<script setup lang="ts">
import { ArrowRight, Download } from '@lucide/vue';
import { useSite } from '@/composables/useSite';

const { props, t, anchor, locale, cvFor } = useSite();
const cv = cvFor(locale);

// Text is split into words so, on phones, words and pills wrap as equal flex items
// (centred on one line, no gaps left after a pill).
// The last two words stay together so a line never ends with a single short word.
const words = (key: string) => {
    const list = t(key).split(/\s+/).filter(Boolean);
    return list.length > 2 ? [...list.slice(0, -2), list.slice(-2).join('\u00a0')] : list;
};
</script>

<template>
    <section :id="anchor('top')" data-section="top" class="hero" aria-labelledby="hero-title">
        <div class="container-site hero-inner">
            <p v-if="props.profile.available" class="available hero-in" style="--d: 0ms">
                <span class="available-dot" aria-hidden="true" />
                {{ t('hero.available') }}
            </p>

            <!--
                Desktop: three rows sized from the reference (64px text, 62px pills).
                Phones: every word and pill becomes an item of one wrapping row,
                centred vertically, with a single forced break after the name.
            -->
            <h1 id="hero-title" class="hero-title">
                <span class="hero-row hero-in" style="--d: 60ms">
                    <span class="h-text"><span v-for="(w, i) in words('hero.greeting')" :key="i" class="w">{{ w }}{{ ' ' }}</span></span>
                    <span v-if="props.profile.memoji" class="h-memoji" aria-hidden="true">
                        <img :src="props.profile.memoji" alt="" width="159" height="159" fetchpriority="high" />
                    </span>
                    <span class="h-pill">{{ props.profile.name }}</span>{{ ' ' }}
                </span>
                <span class="hero-break" aria-hidden="true" />
                <span class="hero-row hero-in" style="--d: 110ms">
                    <span class="h-text"><span v-for="(w, i) in words('hero.expert')" :key="i" class="w">{{ w }}{{ ' ' }}</span></span>
                    <span class="h-pill">{{ t('hero.skill_a') }}</span>{{ ' ' }}
                    <!-- "y / and" travels with the second skill so it never dangles at a line end -->
                    <span class="hero-group">
                        <span class="h-text">{{ t('hero.and') }}</span>{{ ' ' }}
                        <span class="h-pill">{{ t('hero.skill_b') }}</span>
                    </span>{{ ' ' }}
                </span>
                <span class="hero-row hero-tagline hero-in" style="--d: 150ms">
                    <span class="h-text"><span v-for="(w, i) in words('hero.tagline')" :key="i" class="w">{{ w }}{{ ' ' }}</span></span>
                </span>
            </h1>

            <div class="hero-actions hero-in" style="--d: 180ms">
                <a :href="`#${anchor('contact')}`" class="btn btn-ink">
                    {{ t('hero.cta_primary') }}
                    <ArrowRight :size="16" class="btn-arrow" aria-hidden="true" />
                </a>
                <a v-if="cv" :href="cv.url" class="btn btn-outline" download>
                    {{ t('hero.cta_secondary') }}
                    <Download :size="16" class="btn-arrow btn-arrow-down" aria-hidden="true" />
                </a>
                <!-- Supporting figures: plain text, quieter than the buttons -->
                <dl class="stats">
                    <div class="stat">
                        <dt class="stat-label">{{ t('hero.stat_projects_label') }}</dt>
                        <dd class="stat-value">{{ t('hero.stat_projects_value') }}</dd>
                    </div>
                    <div class="stat">
                        <dt class="stat-label">{{ t('hero.stat_countries_label') }}</dt>
                        <dd class="stat-value">{{ t('hero.stat_countries_value') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>

<style scoped>
.hero-inner {
    padding-top: clamp(7rem, 11vw, 10rem);
    padding-bottom: clamp(4.5rem, 8vw, 7.5rem);
    --h-size: clamp(2.5rem, 4.45vw, 4rem); /* 40px → 64px */
    --p-size: clamp(1.25rem, 2.22vw, 2rem); /* 20px → 32px */
}

/* "Available for work" */
.available {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgb(102 187 106 / 0.55);
    background: rgb(102 187 106 / 0.06);
    color: #3f8f44;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.4;
    letter-spacing: -0.01em;
}
.available-dot { width: 8px; height: 8px; border-radius: 5px; background: #66bb6a; box-shadow: 0 0 0 3px rgb(102 187 106 / 0.2); }

.hero-title {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: clamp(1.25rem, 2.2vw, 2rem);
}
.hero-row { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 10px; }
.hero-group { display: inline-flex; align-items: center; gap: 10px; white-space: nowrap; }
.hero-break { display: none; }

.h-text {
    font-size: var(--h-size);
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -0.028em;
    color: var(--color-ink);
}

.h-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.3125em 0.75em; /* 10px 24px at 32px */
    border-radius: 999px;
    background: var(--color-surface);
    font-size: var(--p-size);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -0.005em;
    color: var(--color-ink);
    white-space: nowrap;
}

/* Memoji: a round disc, as tall as the pills beside it */
.h-memoji {
    position: relative;
    display: inline-block;
    width: calc(var(--p-size) * 1.925);
    height: calc(var(--p-size) * 1.925);
    border-radius: 50%;
    background: var(--brand-gradient);
    overflow: hidden;
    flex: none;
}
.h-memoji img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: translateY(6%) scale(1.04);
}

.hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: clamp(1.75rem, 2.6vw, 2.25rem); }

/* Figures beside the buttons: number above, label below, separated by a hairline. */
.stats { display: flex; align-items: center; margin-left: 12px; }
.stat {
    display: flex;
    flex-direction: column-reverse; /* value reads first visually; label stays the <dt> */
    padding: 0 18px;
    line-height: 1.15;
}
.stat + .stat { border-left: 1px solid var(--color-line); }
.stat-value { font-size: 1.125rem; font-weight: 700; letter-spacing: -0.02em; color: var(--color-ink); }
.stat-label { margin-top: 2px; font-size: 0.8125rem; color: var(--color-muted); }
@media (max-width: 480px) {
    .stats { width: 100%; margin: 8px 0 0; }
    .stat:first-child { padding-left: 4px; }
}

/*
 * Phones: one wrapping row. Rows, text runs and groups dissolve (display:
 * contents) so each word and pill is an item of the title's flex row: items
 * centre on each line, a long pill moves whole, and the tagline continues
 * right after the last pill instead of leaving the rest of that line empty.
 * Text scales with the screen (~24px at 320, ~28px at 390, 40px from 550);
 * pills stay at 0.58 of it, the reference's ratio.
 */
@media (max-width: 640px) {
    .hero-inner { --h-size: clamp(1.5rem, 7.3vw, 2.5rem); --p-size: calc(var(--h-size) * 0.58); }
    .hero-title {
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        gap: calc(var(--h-size) * 0.16) calc(var(--h-size) * 0.26);
    }
    .hero-row, .h-text { display: contents; }
    .w { font-size: var(--h-size); font-weight: 700; line-height: 1.2; letter-spacing: -0.028em; }
    .hero-group { gap: calc(var(--h-size) * 0.26); }
    .hero-break { display: block; flex-basis: 100%; height: 0; }
}

/*
 * First-visit entrance: the hero is seen once per visit, so it can carry a
 * short, staggered rise. It runs on the compositor (opacity + transform), the
 * content is in the HTML from SSR, and nothing waits on it to be usable.
 */
@media (prefers-reduced-motion: no-preference) {
    /* One :global() around the whole selector: Vue drops anything after a :global(…) part. */
    :global(html.js .hero-in) {
        animation: hero-rise 520ms var(--ease-out) both;
        animation-delay: var(--d, 0ms);
    }
}
@keyframes hero-rise {
    from { opacity: 0; transform: translateY(12px); }
}
</style>
