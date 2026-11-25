<script setup lang="ts">
import { ref } from 'vue';
import CellField from './CellField.vue';
import { useI18n } from '@/composables/useI18n';
import { useClientEffect, useFinePointer, useReducedMotion } from '@/composables/useMotion';
import { ANCHORS } from '@/lib/scenes';
import type { LayoutName } from '@/lib/cells';

const KEYS = ['food', 'telecom', 'trade', 'employment', 'agencies'] as const satisfies readonly LayoutName[];
type Key = (typeof KEYS)[number];

// Each name sits on its own indent, so the list reads as a composition, not a table.
const INDENT = ['0%', '14%', '5%', '20%', '9%'];

const { t, locale } = useI18n('industries');
const fine = useFinePointer();
const reduced = useReducedMotion();
const active = ref<Key>('food');
const hovering = ref(false);
const buttons: (HTMLButtonElement | null)[] = [];
const list = ref<HTMLDivElement | null>(null);
const preview = ref<HTMLDivElement | null>(null);

// The preview tile trails the cursor on pointer devices.
useClientEffect(() => {
    if (!fine.value) return;
    const area = list.value;
    const tile = preview.value;
    if (!area || !tile) return;
    const still = reduced.value;
    const pos = { x: 0, y: 0 };
    const target = { x: 0, y: 0 };
    let raf = 0;
    const loop = () => {
        const k = still ? 1 : 0.16;
        const dx = target.x - pos.x;
        pos.x += dx * k;
        pos.y += (target.y - pos.y) * k;
        const tilt = still ? 0 : Math.max(-7, Math.min(7, dx * 0.04));
        tile.style.transform = `translate3d(${pos.x}px, ${pos.y}px, 0) rotate(${tilt}deg)`;
        raf = Math.abs(dx) > 0.2 || Math.abs(target.y - pos.y) > 0.2 ? requestAnimationFrame(loop) : 0;
    };
    const onMove = (e: PointerEvent) => {
        const r = area.getBoundingClientRect();
        target.x = Math.min(r.width - tile.offsetWidth, e.clientX - r.left + 32);
        target.y = e.clientY - r.top - tile.offsetHeight / 2;
        if (!raf) raf = requestAnimationFrame(loop);
    };
    area.addEventListener('pointermove', onMove);
    return () => {
        area.removeEventListener('pointermove', onMove);
        cancelAnimationFrame(raf);
    };
}, [fine, reduced]);

// Touch screens: the name in the middle of the viewport is the active one.
useClientEffect(() => {
    if (fine.value) return;
    const io = new IntersectionObserver(
        (entries) =>
            entries.forEach((e) => {
                if (e.isIntersecting) active.value = e.target.getAttribute('data-key') as Key;
            }),
        { rootMargin: '-40% 0px -50% 0px' },
    );
    buttons.forEach((el) => el && io.observe(el));
    return () => io.disconnect();
}, [fine]);

function onKey(e: KeyboardEvent, i: number) {
    const move = ({ ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 } as Record<string, number>)[e.key];
    let next: number | undefined;
    if (move) next = (i + move + KEYS.length) % KEYS.length;
    if (e.key === 'Home') next = 0;
    if (e.key === 'End') next = KEYS.length - 1;
    if (next === undefined) return;
    e.preventDefault();
    active.value = KEYS[next];
    buttons[next]?.focus();
}
</script>

<template>
    <section
        :id="ANCHORS[locale].industries"
        data-scene="industries"
        aria-labelledby="industries-title"
        class="artboard on-dark bg-night text-panel"
        style="background-image: var(--dots-dark)"
    >
        <div class="frame pt-28 pb-16 lg:pt-36 lg:pb-24">
            <div class="grid-12 gap-y-6">
                <h2 id="industries-title" class="display col-span-4 text-display-l md:col-span-5 lg:col-span-6">
                    {{ t('title') }}
                </h2>
                <p class="col-span-4 max-w-[40ch] text-lead text-fog md:col-span-3 lg:col-span-4 lg:col-start-9">
                    {{ t('lead') }}
                </p>
            </div>

            <div ref="list" class="relative mt-16 lg:mt-24" @pointerenter="hovering = true" @pointerleave="hovering = false">
                <div role="tablist" aria-orientation="vertical" :aria-label="t('title')" class="flex flex-col">
                    <div v-for="(key, i) in KEYS" :key="key" class="border-t border-rule-dark last:border-b">
                        <button
                            :ref="(el) => (buttons[i] = el as HTMLButtonElement | null)"
                            :data-key="key"
                            role="tab"
                            :id="`industry-tab-${key}`"
                            :aria-selected="active === key"
                            aria-controls="industry-panel"
                            :tabindex="active === key ? 0 : -1"
                            :data-active="active === key"
                            class="industry-name display flex w-full items-center gap-4 py-3 text-left lg:py-4 lg:pl-[var(--indent)]"
                            :style="{ '--indent': INDENT[i] }"
                            @click="active = key"
                            @pointerenter="fine && (active = key)"
                            @focus="active = key"
                            @keydown="onKey($event, i)"
                        >
                            <span aria-hidden="true" class="industry-cell block size-3 shrink-0 rounded-[3px] bg-jade lg:size-4" />
                            <span>{{ t(`items.${key}.name`) }}</span>
                        </button>

                        <!-- Touch screens: the active sector opens in place -->
                        <div aria-hidden="true" :class="['pointer-coarse-only gap-4 pb-8', active === key ? 'flex' : 'hidden']">
                            <div class="w-[4.5rem] shrink-0 rounded-[10px] bg-panel p-2">
                                <CellField :layout="key" class="w-full" />
                            </div>
                            <div>
                                <p class="text-body">{{ t(`items.${key}.body`) }}</p>
                                <p class="label mt-3 text-fog">{{ t(`items.${key}.meta`) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pointer devices: a preview tile trails the cursor -->
                <div
                    ref="preview"
                    aria-hidden="true"
                    :data-visible="hovering"
                    class="industry-preview pointer-fine-only pointer-events-none absolute top-0 left-0 z-10 w-[18rem] rounded-[var(--radius-tile)] bg-panel p-3 text-ink shadow-[0_30px_60px_-20px_rgb(0_0_0/0.55)]"
                >
                    <div class="rounded-[8px] bg-surface p-5" style="background-image: var(--dots); background-size: 7px 7px">
                        <CellField :layout="active" class="mx-auto h-40 w-auto" />
                    </div>
                    <p class="mt-4 px-1 text-heading">{{ t(`items.${active}.name`) }}</p>
                    <p class="label mt-1.5 mb-1 px-1 text-graphite">{{ t(`items.${active}.meta`) }}</p>
                </div>
            </div>

            <!-- The active sector, in words -->
            <div id="industry-panel" role="tabpanel" :aria-labelledby="`industry-tab-${active}`" class="grid-12 mt-10 gap-y-4">
                <p class="label pointer-fine-only col-span-4 text-fog lg:col-span-4">{{ t('hint') }}</p>
                <div class="sr-only lg:not-sr-only lg:col-span-5 lg:col-start-8">
                    <p class="text-lead text-panel">{{ t(`items.${active}.body`) }}</p>
                    <p class="label mt-3 text-fog">{{ t(`items.${active}.meta`) }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
