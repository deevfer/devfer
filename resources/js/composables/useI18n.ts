import { usePage } from '@inertiajs/vue3';

export type Locale = 'en' | 'es';

type Messages = { [key: string]: string | string[] | Messages | Messages[] };

export type SiteProps = {
    locale: Locale;
    messages: Messages;
    email: string;
};

/**
 * Reads the copy that Laravel sends from resources/messages/{locale}.json.
 * `t` returns strings with `{name}` placeholders filled; `raw` returns
 * arrays and objects as they are.
 */
export function useI18n(namespace?: string) {
    const page = usePage<SiteProps>();
    const { locale, email } = page.props;

    const raw = <T = unknown>(key: string): T => {
        const path = (namespace ? `${namespace}.${key}` : key).split('.');
        let value: unknown = page.props.messages;
        for (const part of path) value = (value as Record<string, unknown> | undefined)?.[part];
        return value as T;
    };

    const t = (key: string, vars?: Record<string, string | number>): string => {
        const value = raw(key);
        if (typeof value !== 'string') return namespace ? `${namespace}.${key}` : key;
        if (!vars) return value;
        return value.replace(/\{(\w+)\}/g, (match, name: string) => (name in vars ? String(vars[name]) : match));
    };

    return { t, raw, locale, email };
}
