<script setup lang="ts">
import { ref } from 'vue';
import gsap from 'gsap';
import HeroHeadline from './HeroHeadline.vue';
import CursorCells from './CursorCells.vue';
import { useI18n } from '@/composables/useI18n';
import { useClientEffect, useFinePointer, useReducedMotion } from '@/composables/useMotion';
import { ANCHORS } from '@/lib/scenes';

const REST = 0.56; // where the scanner settles, as a share of the headline width

const { t, locale } = useI18n('hero');
const reduced = useReducedMotion();
const fine = useFinePointer();
const stage = ref<HTMLDivElement | null>(null);

useClientEffect(() => {
    const el = stage.value;
    if (!el) return;
    const still = reduced.value;
    let width = el.offsetWidth;
    const state = { scan: still ? width * REST : 0 };
    let target = width * REST;
    let raf = 0;
    let intro: gsap.core.Tween | null = null;

    const apply = () => el.style.setProperty('--scan', `${state.scan.toFixed(1)}px`);
    const loop = () => {
        raf = 0;
        state.scan += (target - state.scan) * 0.14;
        if (Math.abs(target - state.scan) < 0.3) state.scan = target;
        apply();
        if (state.scan !== target) raf = requestAnimationFrame(loop);
    };
    const moveTo = (x: number) => {
        intro?.kill();
        intro = null;
        target = Math.min(width, Math.max(0, x));
        if (still) {
            state.scan = target;
            apply();
        } else if (!raf) raf = requestAnimationFrame(loop);
    };

    apply();
    // The opening sweep: everything starts as a wireframe and gets built.
    if (!still) {
        intro = gsap.to(state, {
            scan: width * REST,
            duration: 1.8,
            delay: 0.25,
            ease: 'power3.inOut',
            onUpdate: apply,
        });
    }

    const onResize = () => {
        const ratio = width ? state.scan / width : REST;
        width = el.offsetWidth;
        state.scan = target = ratio * width;
        apply();
    };
    const local = (clientX: number) => clientX - el.getBoundingClientRect().left;
    const onMove = (e: PointerEvent) => {
        if (e.pointerType === 'mouse' || e.buttons) moveTo(local(e.clientX));
    };
    const onLeave = (e: PointerEvent) => {
        if (e.pointerType === 'mouse') moveTo(width * REST);
    };

    const area = el.closest('section') ?? el;
    area.addEventListener('pointermove', onMove as EventListener);
    area.addEventListener('pointerleave', onLeave as EventListener);
    el.addEventListener('pointerdown', onMove as EventListener);
    window.addEventListener('resize', onResize);
    return () => {
        intro?.kill();
        cancelAnimationFrame(raf);
        area.removeEventListener('pointermove', onMove as EventListener);
        area.removeEventListener('pointerleave', onLeave as EventListener);
        el.removeEventListener('pointerdown', onMove as EventListener);
        window.removeEventListener('resize', onResize);
    };
}, [reduced]);
</script>

<template>
    <section :id="ANCHORS[locale].intro" data-scene="intro" class="pt-0">
        <div class="artboard flex min-h-[calc(100svh-2*var(--inset))] flex-col">
            <CursorCells v-if="fine && !reduced" />

            <div class="frame relative flex flex-1 flex-col pt-24 pb-8 lg:pt-28 lg:pb-10">
                <h1 class="label flex flex-col text-graphite md:flex-row md:gap-6">
                    <span class="text-ink">{{ t('name') }}</span>
                    <span>{{ t('role') }}</span>
                </h1>

                <div class="flex flex-1 items-center justify-center py-12">
                    <div
                        ref="stage"
                        class="hero-headline display relative grid touch-pan-y text-center select-none"
                        style="--scan: 0px"
                    >
                        <p class="sr-only">{{ t('srHeadline') }}</p>
                        <p class="hero-layer hero-wire" aria-hidden="true"><HeroHeadline wire /></p>
                        <p class="hero-layer hero-built text-ink" aria-hidden="true"><HeroHeadline /></p>

                        <!-- The scanner: design on its left, build on its right -->
                        <div class="hero-scan pointer-events-none absolute inset-y-[-1.5rem] left-0 w-0" aria-hidden="true">
                            <span class="absolute inset-y-0 left-0 w-px bg-jade" />
                            <span
                                class="label absolute -top-3 left-0 flex -translate-x-1/2 -translate-y-full items-center gap-1.5 rounded-full bg-ink py-1 pr-2.5 pl-2.5 whitespace-nowrap text-panel"
                            >
                                <span class="text-fog">{{ t('design') }}</span>
                                <span class="block size-1.5 rounded-[1px] bg-jade" />
                                <span>{{ t('build') }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid-12 items-end gap-y-6">
                    <p class="col-span-2 md:col-span-3 lg:col-span-3">
                        <span class="display block text-display-l">{{ t('years') }}</span>
                        <span class="mt-2 block max-w-[22ch] text-small text-graphite">{{ t('yearsLabel') }}</span>
                    </p>
                    <p class="col-span-2 md:col-span-3 lg:col-span-3">
                        <span class="display block text-display-l">{{ t('projects') }}</span>
                        <span class="mt-2 block max-w-[22ch] text-small text-graphite">{{ t('projectsLabel') }}</span>
                    </p>
                    <p class="label col-span-4 text-graphite md:col-span-2 md:text-right lg:col-span-6">
                        <span class="hint-pointer">{{ t('hintPointer') }}</span>
                        <span class="hint-touch">{{ t('hintTouch') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
