<script setup lang="ts">
import { computed, ref, type Component } from 'vue';
import { ArrowRight, BriefcaseBusiness, CodeXml, FileText, House, MessageCircle, UserRound } from '@lucide/vue';
import { useSite, type Locale, type SectionKey } from '@/composables/useSite';
import { useActiveSection } from '@/composables/useActiveSection';

/*
 * The navigation is a single floating dock, as in the reference: icon buttons
 * with a label that appears on hover or keyboard focus, the language switch,
 * and the primary call to action. Once the visitor leaves the hero, the
 * portrait slides into the start of the dock.
 */
const { props, t, href, anchor, locale } = useSite();
const active = useActiveSection();

const ICONS: Record<string, Component> = {
    profile: UserRound,
    services: BriefcaseBusiness,
    technologies: CodeXml,
    cv: FileText,
    contact: MessageCircle,
};

// Contact is the call-to-action button, so it is not repeated as an icon.
const items = computed(() => props.nav.filter((item) => item.target !== 'contact'));
const other: Locale = locale === 'es' ? 'en' : 'es';
const otherHref = computed(() => (active.value === 'top' ? `/${other}` : `/${other}#${anchor(active.value as SectionKey, other)}`));
const otherName = t(other === 'es' ? 'nav.lang_es' : 'nav.lang_en');
const avatar = props.profile.memoji ?? props.profile.photo;
const showAvatar = computed(() => active.value !== 'top' && !!avatar);

/*
 * Tooltips (Emil Kowalski's rule): the first one waits a beat so a pointer
 * passing over the dock doesn't flash labels; once one has opened, moving to
 * a neighbour shows its label instantly. Leaving the dock resets the delay.
 */
const warm = ref(false);
let coolTimer: ReturnType<typeof setTimeout> | undefined;
function onTipShown() {
    clearTimeout(coolTimer);
    warm.value = true;
}
function onDockLeave() {
    clearTimeout(coolTimer);
    coolTimer = setTimeout(() => (warm.value = false), 300);
}
</script>

<template>
    <a href="#main" class="btn btn-ink sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[70]">
        {{ t('a11y.skip') }}
    </a>

    <!-- Shared stroke gradient for the dock icons (24×24 icon space, so straight lines still paint). -->
    <svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="brand-stroke" gradientUnits="userSpaceOnUse" x1="2" y1="12" x2="22" y2="12">
                <stop offset="0" stop-color="#c262bf" />
                <stop offset="0.167" stop-color="#d656ad" />
                <stop offset="0.333" stop-color="#ea4e96" />
                <stop offset="0.5" stop-color="#f95083" />
                <stop offset="0.667" stop-color="#fe5971" />
                <stop offset="0.833" stop-color="#ff6961" />
                <stop offset="1" stop-color="#fe7b58" />
            </linearGradient>
        </defs>
    </svg>

    <header class="dock-wrap">
        <nav :aria-label="t('nav.label')" class="dock" :data-warm="warm || undefined" @pointerleave="onDockLeave">
            <a
                :href="`#${anchor('top')}`"
                class="dock-avatar"
                :data-visible="showAvatar || undefined"
                :aria-hidden="!showAvatar || undefined"
                :tabindex="showAvatar ? undefined : -1"
                :aria-label="t('a11y.home')"
            >
                <span class="dock-avatar-inner">
                    <img v-if="avatar" :src="avatar" alt="" width="40" height="40" />
                </span>
                <!-- Outside the clipped disc, so the status dot is never cut off -->
                <i v-if="props.profile.available" aria-hidden="true" />
            </a>

            <ul class="dock-list">
                <li class="dock-home">
                    <a :href="`#${anchor('top')}`" class="dock-item" :aria-current="active === 'top' ? 'true' : undefined" @pointerenter="onTipShown">
                        <House :size="20" :stroke-width="1.75" aria-hidden="true" />
                        <span class="sr-only">{{ t('a11y.home') }}</span>
                        <span class="tip" aria-hidden="true">{{ props.profile.name }}</span>
                    </a>
                </li>
                <li v-for="item in items" :key="item.target">
                    <a
                        :href="href(item.target)"
                        class="dock-item"
                        :aria-current="active === item.target ? 'true' : undefined"
                        @pointerenter="onTipShown"
                    >
                        <component :is="ICONS[item.target] ?? FileText" :size="20" :stroke-width="1.75" aria-hidden="true" />
                        <span class="sr-only">{{ item.label }}</span>
                        <span class="tip" aria-hidden="true">{{ item.label }}</span>
                    </a>
                </li>
                <li>
                    <a :href="otherHref" class="dock-item dock-lang" :hreflang="other" :lang="other" @pointerenter="onTipShown">
                        <span aria-hidden="true">{{ other.toUpperCase() }}</span>
                        <span class="sr-only">{{ otherName }}</span>
                        <span class="tip" aria-hidden="true">{{ otherName }}</span>
                    </a>
                </li>
            </ul>

            <a :href="`#${anchor('contact')}`" class="dock-cta" :aria-current="active === 'contact' ? 'true' : undefined">
                <span class="dock-cta-icon" aria-hidden="true"><MessageCircle :size="14" :stroke-width="2.25" /></span>
                <span class="dock-cta-label">{{ t('hero.cta_primary') }}</span>
                <ArrowRight :size="15" class="dock-cta-arrow" aria-hidden="true" />
            </a>
        </nav>
    </header>
</template>

<style scoped>
.dock-wrap {
    position: fixed;
    top: max(14px, env(safe-area-inset-top));
    left: 0;
    right: 0;
    z-index: 50;
    display: flex;
    justify-content: center;
    padding-inline: 12px;
    pointer-events: none;
}

.dock {
    pointer-events: auto;
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 4px;
    border-radius: 999px;
    background: rgb(255 255 255 / 0.92);
    backdrop-filter: saturate(1.4) blur(14px);
    -webkit-backdrop-filter: saturate(1.4) blur(14px);
    box-shadow:
        0 0 0 1px rgb(17 17 19 / 0.04),
        0 12px 32px -12px rgb(17 17 19 / 0.22),
        0 2px 6px -2px rgb(17 17 19 / 0.08);
}

.dock-list { display: flex; align-items: center; gap: 2px; }

.dock-item {
    position: relative;
    display: grid;
    place-items: center;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    color: var(--color-ink);
    transition: color 160ms ease, background-color 160ms ease, transform 160ms var(--ease-out);
}
.dock-item:active { transform: scale(0.94); }
/* Current section and hover: the icon's stroke takes the brand gradient (defined once in the template). */
.dock-item[aria-current] :deep(svg) { stroke: url(#brand-stroke); }
.dock-item[aria-current].dock-lang span:first-child { background: var(--brand-gradient); -webkit-background-clip: text; background-clip: text; color: transparent; }
.dock-lang { font-family: var(--font-mono); font-size: 0.75rem; font-weight: 500; letter-spacing: 0.04em; }
@media (hover: hover) and (pointer: fine) {
    .dock-item:hover { background: var(--color-canvas); }
    .dock-item:hover :deep(svg) { stroke: url(#brand-stroke); }
    .dock-lang:hover span:first-child { background: var(--brand-gradient); -webkit-background-clip: text; background-clip: text; color: transparent; }
}

/* Label under each icon */
.tip {
    position: absolute;
    top: calc(100% + 10px);
    left: 50%;
    padding: 5px 10px;
    border-radius: 999px;
    background: var(--brand-gradient);
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 600;
    font-family: var(--font-sans);
    letter-spacing: 0;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transform: translate(-50%, -4px) scale(0.97);
    transform-origin: top center;
    transition: opacity 125ms var(--ease-out), transform 125ms var(--ease-out);
}
.tip::before {
    content: '';
    position: absolute;
    top: -4px;
    left: 50%;
    width: 9px;
    height: 9px;
    border-radius: 2px;
    background: #f95083; /* the gradient's midpoint, where the caret sits */
    transform: translateX(-50%) rotate(45deg);
}
@media (hover: hover) and (pointer: fine) {
    .dock-item:hover .tip {
        opacity: 1;
        transform: translate(-50%, 0) scale(1);
        transition-delay: 350ms;
    }
    /* Once a label has shown, neighbours open instantly and without motion. */
    .dock[data-warm] .dock-item:hover .tip { transition-delay: 0ms; transition-duration: 0ms; }
}
.dock-item:focus-visible .tip { opacity: 1; transform: translate(-50%, 0) scale(1); transition-delay: 0ms; }

/* Portrait that slides into the dock after the hero */
.dock-avatar {
    position: relative;
    display: block;
    flex: none;
    width: 0;
    height: 44px;
    overflow: hidden;
    opacity: 0;
    transition: width 260ms var(--ease-out), opacity 200ms ease, margin 260ms var(--ease-out);
}
.dock-avatar[data-visible] { width: 44px; opacity: 1; margin-right: 2px; }
.dock-avatar-inner {
    position: relative;
    display: block;
    width: 44px;
    height: 44px;
    border-radius: 999px;
    background: var(--brand-gradient);
    overflow: hidden;
}
/* Same memoji as the hero pill, on the same blue */
.dock-avatar img { position: absolute; left: 50%; bottom: -6%; width: 96%; height: auto; transform: translateX(-50%); }
.dock-avatar i {
    position: absolute;
    right: 1px;
    bottom: 1px;
    width: 8px;
    height: 8px;
    border-radius: 2.5px;
    background: #66bb6a;
    box-shadow: 0 0 0 2px #fff;
}

/* Primary action */
.dock-cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    height: 44px;
    margin-left: 2px;
    padding: 0 1rem 0 0.45rem;
    border-radius: 999px;
    background: var(--color-ink);
    color: var(--color-on-ink);
    font-size: 0.9rem;
    font-weight: 500;
    white-space: nowrap;
    transition: background-color 200ms ease, transform 160ms var(--ease-out);
}
.dock-cta:active { transform: scale(0.97); }
.dock-cta-icon {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    border-radius: 999px;
    background: var(--brand-gradient);
    color: #fff;
}
.dock-cta-arrow { transition: transform 200ms var(--ease-out); }
@media (hover: hover) and (pointer: fine) {
    .dock-cta:hover { background: var(--brand-gradient); }
    .dock-cta:hover .dock-cta-icon { background: rgb(255 255 255 / 0.18); }
    .dock-cta:hover .dock-cta-arrow { transform: translateX(2px); }
}

/*
 * Phones: the dock must keep a visible margin on both sides at every width.
 * ≤520px: 40px icons, no home icon or portrait (the dock is ~345px).
 * ≤374px: 36px icons and the call to action shrinks to its icon (~250px).
 */
@media (max-width: 520px) {
    .dock-item { width: 40px; height: 40px; }
    .dock-cta { height: 40px; padding-right: 0.85rem; font-size: 0.875rem; }
    .dock-cta-arrow, .dock-home, .dock-avatar { display: none; }
}
@media (max-width: 374px) {
    .dock-item { width: 36px; height: 36px; }
    .dock-cta { width: 40px; padding: 0; justify-content: center; }
    .dock-cta-label { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
}

@media (prefers-reduced-motion: reduce) {
    .dock-avatar { transition: opacity 200ms ease; }
    .tip { transform: translate(-50%, 0); }
}
</style>
