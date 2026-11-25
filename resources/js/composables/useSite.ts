import { usePage } from '@inertiajs/vue3';

export type Locale = 'es' | 'en';
export type SectionKey = 'top' | 'profile' | 'services' | 'technologies' | 'cv' | 'contact';

export type Service = { id: number; slug: string; icon: string; title: string; summary: string; items: string[] };
export type Tech = { name: string; logo: string | null; color: string | null; url: string | null };
export type TechGroup = { category: string; items: Tech[] };
export type CvFile = { locale: Locale; url: string; size: number; updated: string | null };

export type SiteProps = {
    locale: Locale;
    anchors: Record<Locale, Record<SectionKey, string>>;
    texts: Record<string, string>;
    profile: { name: string; photo: string | null; memoji: string | null; available: boolean; email: string };
    nav: { label: string; target: SectionKey | string }[];
    services: Service[];
    technologies: TechGroup[];
    links: { label: string; url: string; icon: string }[];
    cv: CvFile[];
    recaptcha: { enabled: boolean; version?: 'v3' | 'v2'; siteKey?: string; action?: string };
    flash: { contact: { name: string } | null };
    errors: Record<string, string>;
};

/** Page props from Laravel plus helpers: `t` for copy, `anchor` for localized section ids. */
export function useSite() {
    const page = usePage<SiteProps>();
    const props = page.props;
    const locale = props.locale;

    const t = (key: string, vars?: Record<string, string | number>): string => {
        const value = props.texts[key] ?? key;
        return vars ? value.replace(/\{(\w+)\}/g, (m, name: string) => (name in vars ? String(vars[name]) : m)) : value;
    };

    const anchor = (section: SectionKey, lang: Locale = locale) => props.anchors[lang][section];
    const href = (target: string) => (target.startsWith('http') || target.startsWith('mailto:') ? target : `#${anchor(target as SectionKey)}`);
    const cvFor = (lang: Locale) => props.cv.find((c) => c.locale === lang);

    return { page, props, locale, t, anchor, href, cvFor };
}
