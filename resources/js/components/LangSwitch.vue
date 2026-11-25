<script setup lang="ts">
import { useSite, type Locale, type SectionKey } from '@/composables/useSite';

/** ES/EN switch. Each link goes to the same section on the other language's page. */
const props = withDefaults(defineProps<{ section: SectionKey; tone?: 'light' | 'ink' }>(), { tone: 'light' });
const { locale, t, anchor } = useSite();
const LOCALES: Locale[] = ['es', 'en'];
const name = (l: Locale) => t(l === 'es' ? 'nav.lang_es' : 'nav.lang_en');
const hrefFor = (l: Locale) => (props.section === 'top' ? `/${l}` : `/${l}#${anchor(props.section, l)}`);
</script>

<template>
    <nav :aria-label="t('nav.language')" :class="['lang', tone === 'ink' && 'lang-ink']">
        <template v-for="l in LOCALES" :key="l">
            <span v-if="l === locale" class="lang-item is-current" aria-current="true">
                <span aria-hidden="true">{{ l.toUpperCase() }}</span><span class="sr-only">{{ name(l) }}</span>
            </span>
            <a v-else class="lang-item" :href="hrefFor(l)" :hreflang="l" :lang="l">
                <span aria-hidden="true">{{ l.toUpperCase() }}</span><span class="sr-only">{{ name(l) }}</span>
            </a>
        </template>
    </nav>
</template>

<style scoped>
.lang { display: inline-flex; padding: 4px; gap: 2px; border-radius: 999px; background: var(--color-surface); box-shadow: inset 0 0 0 1px var(--color-line); }
.lang-ink { background: rgb(255 255 255 / 0.08); box-shadow: none; }
.lang-item {
    display: grid; place-items: center; min-width: 40px; min-height: 40px; border-radius: 999px;
    font-family: var(--font-mono); font-size: 0.75rem; letter-spacing: 0.04em; color: var(--color-muted);
    transition: color 160ms ease, background-color 160ms ease;
}
.lang-ink .lang-item { color: var(--color-on-ink-muted); }
.is-current { background: var(--color-ink); color: var(--color-on-ink); }
.lang-ink .is-current { background: var(--color-on-ink); color: var(--color-ink); }
@media (hover: hover) and (pointer: fine) {
    a.lang-item:hover { color: var(--color-ink); background: var(--color-canvas); }
    .lang-ink a.lang-item:hover { color: var(--color-on-ink); background: rgb(255 255 255 / 0.08); }
}
</style>
