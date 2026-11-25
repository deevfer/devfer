<script setup lang="ts">
import SectionLabel from './SectionLabel.vue';
import { useSite } from '@/composables/useSite';

const { props, t, anchor } = useSite();
</script>

<template>
    <section :id="anchor('profile')" data-section="profile" class="section pt-0!" aria-labelledby="profile-title">
        <div class="container-site">
            <div class="grid gap-3 lg:grid-cols-12">
                <!-- Portrait -->
                <figure class="portrait card lg:col-span-5">
                    <img
                        v-if="props.profile.photo"
                        :src="props.profile.photo"
                        :alt="t('hero.photo_alt')"
                        width="1000"
                        height="1000"
                        loading="lazy"
                        class="portrait-img"
                    />
                    <figcaption class="portrait-caption">
                        <span class="block text-h3 font-semibold">{{ props.profile.name }}</span>
                        <span class="mt-1 block text-sm text-on-ink-muted">{{ t('hero.role') }}</span>
                    </figcaption>
                </figure>

                <!-- Bio -->
                <div class="card flex flex-col p-6 sm:p-10 lg:col-span-7 lg:p-12">
                    <SectionLabel section="profile" :label="t('profile.kicker')" class="self-start" />
                    <h2 id="profile-title" class="section-title mt-6">{{ t('profile.title') }}</h2>
                    <div class="mt-6 grid max-w-[58ch] gap-4">
                        <p class="text-lead">{{ t('profile.body') }}</p>
                        <p class="text-lead text-muted">{{ t('profile.body_b') }}</p>
                        <p class="text-lead text-muted">{{ t('profile.body_c') }}</p>
                    </div>
                </div>
            </div>
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

/* The photo fills the card; a gradient at the foot keeps the caption readable. */
.portrait {
    position: relative;
    min-height: 460px;
    aspect-ratio: 4 / 5;
    overflow: hidden;
    background: var(--color-ink);
    color: var(--color-on-ink);
    isolation: isolate;
}
@media (min-width: 64rem) {
    .section-title { font-size: clamp(2.5rem, 3.5vw, 3.25rem); }
    .portrait { aspect-ratio: auto; }
}
.portrait-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: 50% 30%;
    z-index: -1;
}
.portrait-caption {
    position: absolute;
    inset: auto 0 0 0;
    padding: clamp(20px, 2.4vw, 32px);
    padding-top: 6rem;
    background: linear-gradient(to top, rgb(17 17 19 / 0.88) 25%, transparent);
}
</style>
