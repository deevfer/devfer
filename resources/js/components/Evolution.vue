<script setup lang="ts">
import { computed, ref } from 'vue';
import Odometer from './Odometer.vue';
import StageText from './StageText.vue';
import { useI18n } from '@/composables/useI18n';
import { useClientEffect, useReducedMotion } from '@/composables/useMotion';
import { ANCHORS } from '@/lib/scenes';
import { scrollToY } from '@/lib/scroll';
import { FIRST_YEAR, LAST_YEAR, STAGES, stageFor } from '@/content/evolution';

const YEARS = Array.from({ length: LAST_YEAR - FIRST_YEAR + 1 }, (_, i) => FIRST_YEAR + i);
const SPAN = LAST_YEAR - FIRST_YEAR;
const RAMP = ['bg-j2', 'bg-j3', 'bg-j4', 'bg-j4', 'bg-j5', 'bg-j6'];

const { t, locale } = useI18n('evolution');
const reduced = useReducedMotion();
const outer = ref<HTMLDivElement | null>(null);
const year = ref(FIRST_YEAR);

useClientEffect(() => {
    if (reduced.value) return;
    let raf = 0;
    const measure = () => {
        raf = 0;
        const el = outer.value;
        if (!el) return;
        const r = el.getBoundingClientRect();
        const progress = Math.min(1, Math.max(0, -r.top / (r.height - window.innerHeight)));
        // Dwell on each year, then roll quickly to the next one
        const raw = progress * SPAN;
        const whole = Math.floor(raw);
        const f = raw - whole;
        const eased = Math.min(1, Math.max(0, (f - 0.55) / 0.35));
        year.value = FIRST_YEAR + whole + eased * eased * (3 - 2 * eased);
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

const current = computed(() => Math.min(LAST_YEAR, Math.round(year.value)));
const stageIndex = computed(() => Math.max(0, stageFor(current.value)));

function jump(y: number) {
    const el = outer.value;
    if (!el) return;
    const top = el.getBoundingClientRect().top + window.scrollY;
    const progress = (y - FIRST_YEAR) / SPAN;
    scrollToY(top + progress * (el.offsetHeight - window.innerHeight) + 2, !reduced.value);
}

function yearClass(y: number) {
    if (y === current.value) return 'bg-ink';
    if (y <= current.value) return RAMP[Math.min(RAMP.length - 1, stageFor(y))];
    return 'bg-hairline group-hover:bg-j1';
}

const summary = (s: (typeof STAGES)[number]) => {
    const range = s.from === s.to ? `${s.from}` : `${s.from}–${s.to === LAST_YEAR ? t('today') : s.to}`;
    const copy = s[locale];
    return `${range}: ${copy.title}. ${copy.body}${copy.learned ? ` ${copy.learned}` : ''}`;
};
</script>

<template>
    <section v-if="reduced" :id="ANCHORS[locale].evolution" data-scene="evolution" aria-labelledby="evolution-title" class="py-28">
        <div class="frame">
            <div class="grid-12 gap-y-4">
                <h2 id="evolution-title" class="display col-span-4 text-display-xl md:col-span-5 lg:col-span-6">{{ t('title') }}</h2>
                <p class="col-span-4 max-w-[38ch] self-end text-lead text-graphite md:col-span-3 lg:col-span-4 lg:col-start-9">
                    {{ t('lead') }}
                </p>
            </div>
            <ol class="mt-16">
                <li v-for="(s, i) in STAGES" :key="s.from" class="grid-12 gap-y-4 border-t border-hairline py-10">
                    <p class="display col-span-4 text-display-xl text-hairline md:col-span-3 lg:col-span-5">{{ s.from }}</p>
                    <div class="col-span-4 md:col-span-5 lg:col-span-6 lg:col-start-7">
                        <StageText :stage="s" :index="i" active />
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section v-else :id="ANCHORS[locale].evolution" data-scene="evolution" aria-labelledby="evolution-title" class="relative">
        <ol class="sr-only">
            <li v-for="s in STAGES" :key="s.from">{{ summary(s) }}</li>
        </ol>
        <div ref="outer" class="h-[520svh] lg:h-[600svh]">
            <div class="sticky top-0 flex h-svh flex-col overflow-hidden pt-24 pb-6 lg:pt-28 lg:pb-8">
                <div class="frame">
                    <div class="grid-12 gap-y-4">
                        <h2 id="evolution-title" class="display col-span-4 text-display-xl md:col-span-5 lg:col-span-6">{{ t('title') }}</h2>
                        <p class="col-span-4 max-w-[38ch] self-end text-lead text-graphite md:col-span-3 lg:col-span-4 lg:col-start-9">
                            {{ t('lead') }}
                        </p>
                    </div>
                </div>

                <div class="frame grid-12 min-h-0 flex-1 content-center items-center gap-y-6">
                    <p
                        aria-hidden="true"
                        class="display col-span-4 text-[clamp(6rem,30vw,11rem)] leading-none tracking-[-0.06em] md:col-span-8 lg:col-span-7 lg:text-[clamp(10rem,21vw,24rem)]"
                    >
                        <Odometer :value="year" />
                    </p>
                    <div class="col-span-4 grid md:col-span-8 lg:col-span-5" aria-hidden="true">
                        <StageText v-for="(s, i) in STAGES" :key="s.from" :stage="s" :index="i" :active="i === stageIndex" />
                    </div>
                </div>

                <!-- Year rail: eleven columns, one per year, and a way to jump between them -->
                <nav :aria-label="t('title')" class="frame">
                    <ol class="grid grid-cols-11 gap-1 md:gap-2">
                        <li v-for="y in YEARS" :key="y">
                            <button
                                type="button"
                                :aria-label="t('jump', { year: y })"
                                :aria-current="y === current ? 'step' : undefined"
                                class="group flex w-full flex-col gap-2 py-2 text-left"
                                @click="jump(y)"
                            >
                                <span :class="['evo-year block h-3 rounded-[3px] md:h-4', yearClass(y)]" />
                                <span :class="['label hidden md:block', y === current ? 'text-ink' : 'text-graphite']">{{ y }}</span>
                            </button>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>
</template>
