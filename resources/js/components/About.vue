<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';
import { useClientEffect, useReducedMotion } from '@/composables/useMotion';
import { ANCHORS } from '@/lib/scenes';

/** A centred statement that is "built" word by word as it scrolls through the viewport. */
const { t, locale } = useI18n('about');
const reduced = useReducedMotion();
const heading = ref<HTMLHeadingElement | null>(null);
const words = computed(() => t('title').split(' '));
const built = ref(words.value.length);

useClientEffect(() => {
    const total = words.value.length;
    if (reduced.value) {
        built.value = total;
        return;
    }
    let raf = 0;
    const measure = () => {
        raf = 0;
        const el = heading.value;
        if (!el) return;
        const r = el.getBoundingClientRect();
        const vh = window.innerHeight;
        const progress = Math.min(1, Math.max(0, (vh * 0.85 - r.top) / (vh * 0.55)));
        built.value = Math.round(progress * total);
    };
    const onScroll = () => {
        if (!raf) raf = requestAnimationFrame(measure);
    };
    measure();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => {
        window.removeEventListener('scroll', onScroll);
        cancelAnimationFrame(raf);
    };
}, [reduced]);
</script>

<template>
    <section :id="ANCHORS[locale].about" data-scene="about" aria-labelledby="about-title" class="py-[18svh] lg:py-[24svh]">
        <div class="frame flex flex-col items-center text-center">
            <h2 id="about-title" ref="heading" class="display max-w-[17ch] text-display-xl text-balance">
                <span v-for="(w, i) in words" :key="i" :class="['statement-word', i < built ? 'text-ink' : 'text-hairline']"
                    >{{ w }}{{ i < words.length - 1 ? ' ' : '' }}</span
                >
            </h2>
            <div class="mt-12 max-w-[54ch] space-y-5 text-left md:text-center lg:mt-16">
                <p class="text-lead text-graphite">{{ t('p1') }}</p>
                <p class="text-lead">{{ t('p2') }}</p>
            </div>
            <p class="label mt-10 inline-flex items-center gap-2.5 rounded-full border border-hairline px-4 py-2 text-graphite">
                <span aria-hidden="true" class="block size-2 rounded-[2px] bg-jade" />
                {{ t('aside') }}
            </p>
        </div>
    </section>
</template>
