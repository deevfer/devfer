<script setup lang="ts">
import { computed, ref } from 'vue';
import Wordmark from './Wordmark.vue';
import { useI18n, type Locale } from '@/composables/useI18n';
import { useClientEffect } from '@/composables/useMotion';
import { ANCHORS, SCENES, type Scene } from '@/lib/scenes';

const { t, locale } = useI18n();
const LOCALES: Locale[] = ['en', 'es'];

const active = ref<Scene>('intro');
useClientEffect(() => {
    const els = document.querySelectorAll<HTMLElement>('[data-scene]');
    const io = new IntersectionObserver(
        (entries) =>
            entries.forEach((e) => {
                if (e.isIntersecting) active.value = e.target.getAttribute('data-scene') as Scene;
            }),
        { rootMargin: '-45% 0px -55% 0px' },
    );
    els.forEach((el) => io.observe(el));
    return () => io.disconnect();
});

const hidden = ref(false);
useClientEffect(() => {
    let last = window.scrollY;
    const onScroll = () => {
        const y = window.scrollY;
        if (y > 240 && y > last + 6) hidden.value = true;
        else if (y < last - 6) hidden.value = false;
        last = y;
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
});

const dark = computed(() => active.value === 'industries');
const dialog = ref<HTMLDialogElement | null>(null);

function go(scene: Scene) {
    dialog.value?.close();
    document.getElementById(ANCHORS[locale][scene])?.scrollIntoView({ behavior: 'smooth' });
    history.replaceState(history.state, '', `#${ANCHORS[locale][scene]}`);
}
</script>

<template>
    <a href="#main" class="pill pill-ink sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[60]">
        {{ t('a11y.skip') }}
    </a>

    <header
        :class="[
            'fixed inset-x-0 top-0 z-50 transition-[transform,color] duration-500 ease-out',
            dark ? 'on-dark text-panel' : 'text-ink',
        ]"
        :style="{ transform: hidden ? 'translateY(-120%)' : 'none' }"
        @focusin="hidden = false"
    >
        <div
            class="flex items-center justify-between px-[calc(var(--inset)+12px)] pt-[calc(var(--inset)+12px)] md:px-[calc(var(--inset)+20px)] md:pt-[calc(var(--inset)+18px)]"
        >
            <a :href="`#${ANCHORS[locale].intro}`" :aria-label="t('a11y.home')" class="rounded-[10px]">
                <Wordmark />
            </a>

            <div class="flex items-center gap-2">
                <nav :aria-label="t('a11y.language')" :class="['lang-toggle label', dark ? 'bg-white/10' : 'bg-ink/[0.06]']">
                    <template v-for="l in LOCALES" :key="l">
                        <span v-if="l === locale" aria-current="true" :class="dark ? 'bg-panel text-ink' : 'bg-ink text-panel'">
                            {{ l.toUpperCase() }}
                        </span>
                        <a v-else :href="`/${l}#${ANCHORS[l][active]}`" :hreflang="l" :lang="l" class="opacity-70 hover:opacity-100">
                            {{ l.toUpperCase() }}
                        </a>
                    </template>
                </nav>
                <button
                    type="button"
                    aria-haspopup="dialog"
                    :class="['pill pill-line backdrop-blur-sm', dark ? 'bg-white/5' : 'bg-panel/60']"
                    @click="dialog?.showModal()"
                >
                    {{ t('nav.menu') }}
                </button>
                <a :href="`#${ANCHORS[locale].contact}`" :class="['pill hidden md:inline-flex', dark ? 'pill-light' : 'pill-ink']">
                    {{ t('nav.talk') }}
                </a>
            </div>
        </div>
    </header>

    <dialog
        ref="dialog"
        :aria-label="t('nav.menu')"
        class="menu-dialog m-[var(--inset)] h-[calc(100dvh-2*var(--inset))] max-h-none w-[calc(100%-2*var(--inset))] max-w-none rounded-[var(--radius-panel)] bg-ink p-0 text-panel"
    >
        <div class="on-dark flex h-full flex-col px-[12px] py-[12px] md:px-[20px] md:py-[18px]">
            <div class="flex items-center justify-between">
                <span class="grid size-11 place-items-center rounded-[10px] bg-panel/10">
                    <span class="block size-2 rounded-[2px] bg-jade" />
                </span>
                <button type="button" class="pill pill-line" :aria-label="t('a11y.closeMenu')" @click="dialog?.close()">
                    {{ t('nav.close') }}
                </button>
            </div>
            <nav :aria-label="t('nav.menu')" class="frame flex flex-1 items-center">
                <ol class="w-full">
                    <li v-for="scene in SCENES" :key="scene">
                        <a
                            :href="`#${ANCHORS[locale][scene]}`"
                            :aria-current="active === scene ? 'location' : undefined"
                            class="menu-item flex items-center gap-4 py-1.5"
                            @click.prevent="go(scene)"
                        >
                            <span aria-hidden="true" class="menu-cell block size-3 rounded-[3px] bg-jade md:size-4" />
                            <span class="menu-name display text-display-l text-panel">{{ t(`nav.items.${scene}`) }}</span>
                        </a>
                    </li>
                </ol>
            </nav>
            <p class="label text-fog">{{ t('hero.name') }}</p>
        </div>
    </dialog>
</template>
