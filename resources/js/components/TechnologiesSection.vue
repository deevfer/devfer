<script setup lang="ts">
import { ref } from 'vue';
import { Plus } from '@lucide/vue';
import SectionLabel from './SectionLabel.vue';
import { useSite } from '@/composables/useSite';

const { props, t, anchor, locale } = useSite();

/*
 * One category open at a time, as in the reference. The panel animates its
 * height (grid-template-rows 0fr → 1fr) and clips while moving; once open it
 * stops clipping so the logo tooltips can extend past it.
 */
const open = ref<string | null>(props.technologies[0]?.category ?? null);
const settled = ref<string | null>(open.value);

function toggle(category: string) {
    settled.value = null;
    open.value = open.value === category ? null : category;
}
function onPanelTransitionEnd(category: string, e: TransitionEvent) {
    if (e.propertyName === 'grid-template-rows' && open.value === category) settled.value = category;
}

// Tooltips: the first waits a beat, neighbours then open instantly (same rule as the header dock).
const warm = ref(false);
let coolTimer: ReturnType<typeof setTimeout> | undefined;
function onTipShown() {
    clearTimeout(coolTimer);
    warm.value = true;
}
function onTrayLeave() {
    clearTimeout(coolTimer);
    coolTimer = setTimeout(() => (warm.value = false), 300);
}

const newTab = locale === 'es' ? '(se abre en una pestaña nueva)' : '(opens in a new tab)';
</script>

<template>
    <section :id="anchor('technologies')" data-section="technologies" class="section pt-0!" aria-labelledby="tech-title">
        <div class="container-site grid gap-10 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-28">
                    <SectionLabel section="technologies" :label="t('tech.kicker')" />
                    <h2 id="tech-title" class="section-title mt-5">{{ t('tech.title') }}</h2>
                    <p class="mt-5 max-w-[38ch] text-lead text-muted">{{ t('tech.lead') }}</p>
                </div>
            </div>

            <ul class="grid gap-3 lg:col-span-7">
                <li v-for="(group, i) in props.technologies" :key="group.category" class="tech-row" :data-open="open === group.category">
                    <h3>
                        <button
                            :id="`tech-btn-${group.category}`"
                            type="button"
                            class="tech-head"
                            :aria-expanded="open === group.category"
                            :aria-controls="`tech-panel-${group.category}`"
                            @click="toggle(group.category)"
                        >
                            <span class="tech-num" aria-hidden="true">{{ String(i + 1).padStart(2, '0') }}</span>
                            <span class="tech-title">{{ t(`tech.cat.${group.category}`) }}</span>
                            <span class="tech-count" aria-hidden="true">{{ group.items.length }}</span>
                            <span class="tech-toggle" aria-hidden="true"><Plus :size="16" :stroke-width="2.5" /></span>
                        </button>
                    </h3>
                    <div
                        :id="`tech-panel-${group.category}`"
                        role="region"
                        :aria-labelledby="`tech-btn-${group.category}`"
                        class="tech-panel"
                        :data-open="open === group.category"
                        :data-settled="settled === group.category || undefined"
                        @transitionend="onPanelTransitionEnd(group.category, $event)"
                    >
                        <div :inert="open !== group.category || undefined">
                            <ul class="tray" :data-warm="warm || undefined" @pointerleave="onTrayLeave">
                                <li v-for="tech in group.items" :key="tech.name">
                                    <a
                                        v-if="tech.url"
                                        :href="tech.url"
                                        class="logo-tile"
                                        target="_blank"
                                        rel="noopener"
                                        :aria-label="`${tech.name} ${newTab}`"
                                        @pointerenter="onTipShown"
                                    >
                                        <img v-if="tech.logo" :src="tech.logo" alt="" width="30" height="30" loading="lazy" />
                                        <span class="tip" aria-hidden="true">{{ tech.name }}</span>
                                    </a>
                                    <span v-else class="logo-tile" tabindex="0" role="img" :aria-label="tech.name" @pointerenter="onTipShown">
                                        <img v-if="tech.logo" :src="tech.logo" alt="" width="30" height="30" loading="lazy" />
                                        <span class="tip" aria-hidden="true">{{ tech.name }}</span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.section-title {
    font-size: var(--text-h2);
    line-height: var(--text-h2--line-height);
    letter-spacing: var(--text-h2--letter-spacing);
    font-weight: 600;
    text-wrap: balance;
    max-width: 14ch;
}

/* Rows */
.tech-row {
    border-radius: 26px;
    background: var(--color-surface);
    box-shadow: 0 0 0 1px rgb(17 17 19 / 0.03), 0 12px 30px -22px rgb(17 17 19 / 0.35);
}
.tech-head {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    min-height: 76px;
    padding: 14px 18px 14px 22px;
    text-align: left;
    border-radius: 26px;
}
.tech-num {
    display: grid;
    place-items: center;
    flex: none;
    width: 30px;
    height: 30px;
    border-radius: 999px;
    background: var(--color-canvas);
    font-family: var(--font-mono);
    font-size: 0.6875rem;
    font-weight: 500;
    color: var(--color-muted);
}
.tech-title { flex: 1; font-size: clamp(1.0625rem, 1.5vw, 1.25rem); font-weight: 700; letter-spacing: -0.015em; }
.tech-count { font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-faint); }
.tech-toggle {
    display: grid;
    place-items: center;
    flex: none;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: var(--color-ink);
    color: var(--color-on-ink);
    box-shadow: 0 6px 14px -6px rgb(17 17 19 / 0.6);
    transition: transform 240ms var(--ease-out), background-color 200ms ease;
}
.tech-head[aria-expanded='true'] .tech-toggle { transform: rotate(45deg); }
.tech-head:active .tech-toggle { transform: scale(0.94); }
.tech-head[aria-expanded='true']:active .tech-toggle { transform: rotate(45deg) scale(0.94); }
@media (hover: hover) and (pointer: fine) {
    .tech-head:hover .tech-toggle { background: var(--color-ink-soft); }
}

/* Height animation: clip while moving, then let tooltips overflow */
.tech-panel {
    display: grid;
    grid-template-rows: 0fr;
    opacity: 0;
    transition: grid-template-rows 300ms var(--ease-out), opacity 200ms ease;
}
.tech-panel[data-open='true'] { grid-template-rows: 1fr; opacity: 1; }
.tech-panel > div { min-height: 0; overflow: hidden; }
.tech-panel[data-settled] > div { overflow: visible; }

/* Tray of logos, like an app dock */
.tray {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0 18px 20px 66px;
    padding: 10px;
    border-radius: 22px;
    background: var(--color-canvas);
}
@media (max-width: 480px) {
    .tray { margin-left: 18px; }
}
.logo-tile {
    position: relative;
    display: grid;
    place-items: center;
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: var(--color-surface);
    box-shadow: 0 0 0 1px rgb(17 17 19 / 0.05), 0 6px 14px -8px rgb(17 17 19 / 0.35);
    transition: transform 160ms var(--ease-out);
}
.logo-tile:active { transform: scale(0.95); }
/* Every logo sits in the same 30 × 30 box */
.logo-tile img { width: 30px; height: 30px; object-fit: contain; }

/* Name tooltip above each logo (same look as the header's) */
.tip {
    position: absolute;
    bottom: calc(100% + 10px);
    left: 50%;
    z-index: 5;
    padding: 5px 10px;
    border-radius: 999px;
    background: var(--brand-gradient);
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 600;
    white-space: nowrap;
    pointer-events: none;
    opacity: 0;
    transform: translate(-50%, 4px) scale(0.97);
    transform-origin: bottom center;
    transition: opacity 125ms var(--ease-out), transform 125ms var(--ease-out);
}
.tip::after {
    content: '';
    position: absolute;
    bottom: -4px;
    left: 50%;
    width: 9px;
    height: 9px;
    border-radius: 2px;
    background: #f95083;
    transform: translateX(-50%) rotate(45deg);
}
@media (hover: hover) and (pointer: fine) {
    .logo-tile:hover .tip { opacity: 1; transform: translate(-50%, 0) scale(1); transition-delay: 350ms; }
    .tray[data-warm] .logo-tile:hover .tip { transition-delay: 0ms; transition-duration: 0ms; }
}
.logo-tile:focus-visible .tip { opacity: 1; transform: translate(-50%, 0) scale(1); }

@media (prefers-reduced-motion: reduce) {
    .tech-panel { transition: opacity 200ms ease; }
    .tip { transform: translate(-50%, 0); }
}
</style>
