import type { Locale } from '@/composables/useI18n';

export const SCENES = ['intro', 'about', 'numbers', 'industries', 'capabilities', 'evolution', 'contact'] as const;
export type Scene = (typeof SCENES)[number];

/** Localized, human-readable anchors for each scene. */
export const ANCHORS: Record<Locale, Record<Scene, string>> = {
    en: {
        intro: 'intro',
        about: 'about',
        numbers: 'numbers',
        industries: 'industries',
        capabilities: 'capabilities',
        evolution: 'evolution',
        contact: 'contact',
    },
    es: {
        intro: 'inicio',
        about: 'sobre-mi',
        numbers: 'cifras',
        industries: 'sectores',
        capabilities: 'capacidades',
        evolution: 'evolucion',
        contact: 'contacto',
    },
};
