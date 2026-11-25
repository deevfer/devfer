import { ref } from 'vue';
import { useSite } from './useSite';

type Grecaptcha = {
    ready(cb: () => void): void;
    execute(siteKey: string, opts: { action: string }): Promise<string>;
    render(el: HTMLElement, opts: { sitekey: string; hl?: string; callback?: (token: string) => void; 'expired-callback'?: () => void }): number;
    getResponse(id?: number): string;
    reset(id?: number): void;
};
declare global {
    interface Window {
        grecaptcha?: Grecaptcha;
        __onRecaptchaLoad?: () => void;
    }
}

let loading: Promise<void> | null = null;

/**
 * Google reCAPTCHA, loaded only when the visitor reaches the form.
 * v3 returns a token per submit; v2 renders the checkbox into `container`.
 */
export function useRecaptcha() {
    const { props, locale } = useSite();
    const config = props.recaptcha;
    const widget = ref<number | null>(null);
    const v2Token = ref('');

    function load(): Promise<void> {
        if (!config.enabled) return Promise.resolve();
        if (loading) return loading;
        loading = new Promise((resolve, reject) => {
            window.__onRecaptchaLoad = () => resolve();
            const s = document.createElement('script');
            const render = config.version === 'v3' ? config.siteKey! : 'explicit';
            s.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(render)}&hl=${locale}&onload=__onRecaptchaLoad`;
            s.async = true;
            s.defer = true;
            s.onerror = () => {
                loading = null;
                reject(new Error('recaptcha-load'));
            };
            document.head.appendChild(s);
        });
        return loading;
    }

    async function mount(container: HTMLElement | null) {
        if (!config.enabled || config.version !== 'v2' || !container || widget.value !== null) return;
        await load();
        widget.value = window.grecaptcha!.render(container, {
            sitekey: config.siteKey!,
            hl: locale,
            callback: (token) => (v2Token.value = token),
            'expired-callback': () => (v2Token.value = ''),
        });
    }

    async function token(): Promise<string> {
        if (!config.enabled) return '';
        await load();
        if (config.version === 'v2') return v2Token.value;
        const g = window.grecaptcha!;
        await new Promise<void>((r) => g.ready(r));
        return g.execute(config.siteKey!, { action: config.action ?? 'contact' });
    }

    function reset() {
        if (config.version === 'v2' && widget.value !== null) {
            window.grecaptcha?.reset(widget.value);
            v2Token.value = '';
        }
    }

    return { config, load, mount, token, reset };
}
