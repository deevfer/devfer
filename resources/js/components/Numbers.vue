<script setup lang="ts">
import { ref } from 'vue';
import CellField from './CellField.vue';
import { useI18n } from '@/composables/useI18n';
import { useClientEffect, useReducedMotion } from '@/composables/useMotion';
import { ANCHORS } from '@/lib/scenes';
import type { LayoutName } from '@/lib/cells';

const STEPS: { layout: LayoutName; title: string; sub: string }[] = [
    { layout: 'all', title: 'step1', sub: 'step1sub' },
    { layout: '200', title: 'step2', sub: 'step2sub' },
    { layout: '11+', title: 'step3', sub: 'step3sub' },
];

const { t, locale } = useI18n('numbers');
const reduced = useReducedMotion();
const outer = ref<HTMLDivElement | null>(null);
const step = ref(0);

useClientEffect(() => {
    if (reduced.value) return;
    let raf = 0;
    const measure = () => {
        raf = 0;
        const el = outer.value;
        if (!el) return;
        const r = el.getBoundingClientRect();
        const progress = Math.min(1, Math.max(0, -r.top / (r.height - window.innerHeight)));
        step.value = progress < 0.3 ? 0 : progress < 0.64 ? 1 : 2;
    };
    const onScroll = () => {
        if (!raf) raf = requestAnimationFrame(measure);
    };
    measure();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    return () => {
        window.removeEventListener('scroll', onScroll);
        window.removeEventListener('resize', onScroll);
        cancelAnimationFrame(raf);
    };
}, [reduced]);
</script>

<template>
    <section v-if="reduced" :id="ANCHORS[locale].numbers" data-scene="numbers" aria-labelledby="numbers-title" class="py-24">
        <h2 id="numbers-title" class="sr-only">{{ t('title') }}</h2>
        <div class="frame grid gap-20 md:grid-cols-3">
            <div v-for="s in STEPS" :key="s.layout" class="flex flex-col items-center text-center">
                <CellField :layout="s.layout" class="w-40" />
                <p class="display mt-8 text-display-l">{{ t(s.title) }}</p>
                <p class="mt-3 max-w-[30ch] text-graphite">{{ t(s.sub) }}</p>
            </div>
        </div>
        <p class="label frame mt-16 text-center text-graphite">{{ t('caption') }}</p>
    </section>

    <section v-else :id="ANCHORS[locale].numbers" data-scene="numbers" aria-labelledby="numbers-title" class="relative">
        <h2 id="numbers-title" class="sr-only">{{ t('title') }}</h2>
        <ul class="sr-only">
            <li v-for="s in STEPS" :key="s.layout">{{ t(s.title) }} {{ t(s.sub) }}</li>
        </ul>
        <div ref="outer" aria-hidden="true" class="h-[320svh]">
            <div class="sticky top-0 h-svh">
                <div
                    class="frame relative grid h-full grid-rows-[auto_minmax(0,1fr)_auto] items-center justify-items-center gap-6 pt-24 pb-8 text-center lg:grid-cols-[1fr_auto_1fr] lg:grid-rows-1 lg:gap-12 lg:pt-20"
                >
                    <!-- Title: left on desktop, above on phones -->
                    <div class="grid w-full lg:justify-items-end lg:text-right">
                        <p v-for="(s, i) in STEPS" :key="s.layout" class="numbers-step display text-display-l text-balance" :data-active="i === step">
                            {{ t(s.title) }}
                        </p>
                    </div>

                    <CellField :layout="STEPS[step].layout" :accent="step === 0" class="h-full max-h-[62svh] w-auto lg:max-h-[74svh]" />

                    <div class="grid w-full max-w-[30ch] lg:justify-items-start lg:text-left">
                        <p v-for="(s, i) in STEPS" :key="s.layout" class="numbers-step text-lead text-graphite" :data-active="i === step">
                            {{ t(s.sub) }}
                        </p>
                    </div>
                </div>
                <div class="absolute inset-x-0 bottom-4 flex items-center justify-center gap-2">
                    <span
                        v-for="(s, i) in STEPS"
                        :key="s.layout"
                        :class="['block h-1 rounded-full transition-all duration-500', i === step ? 'w-8 bg-ink' : 'w-2 bg-hairline']"
                    />
                </div>
                <p class="label absolute right-[var(--margin)] bottom-4 hidden text-graphite lg:block">{{ t('caption') }}</p>
            </div>
        </div>
    </section>
</template>
