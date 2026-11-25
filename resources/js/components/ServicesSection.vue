<script setup lang="ts">
import SectionLabel from './SectionLabel.vue';
import { ArrowRight } from '@lucide/vue';
import AppIcon from './AppIcon.vue';
import { useSite } from '@/composables/useSite';
import { useContactIntent } from '@/composables/useContactIntent';

const { props, t, anchor } = useSite();
const intent = useContactIntent();
</script>

<template>
    <section :id="anchor('services')" data-section="services" class="section pt-0!" aria-labelledby="services-title">
        <div class="container-site">
            <div class="grid gap-6 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-7">
                    <SectionLabel section="services" :label="t('services.kicker')" />
                    <h2 id="services-title" class="section-title mt-5">{{ t('services.title') }}</h2>
                </div>
                <p class="max-w-[42ch] text-lead text-muted lg:col-span-5 lg:justify-self-end">{{ t('services.lead') }}</p>
            </div>

            <ul class="services-grid mt-12 lg:mt-16">
                <li
                    v-for="(service, i) in props.services"
                    :key="service.id"
                    :class="['service card', i === 0 && 'service-featured on-ink']"
                >
                    <article :aria-labelledby="`service-${service.slug}`" class="flex h-full flex-col">
                        <div class="flex items-start justify-between gap-4">
                            <span class="service-icon"><AppIcon :name="service.icon" :size="22" /></span>
                            <span class="font-mono text-[0.75rem] service-index" aria-hidden="true">{{ String(i + 1).padStart(2, '0') }}</span>
                        </div>

                        <h3 :id="`service-${service.slug}`" class="mt-8 text-h3 font-semibold lg:mt-10">{{ service.title }}</h3>
                        <p class="service-summary mt-3">{{ service.summary }}</p>

                        <div v-if="service.items.length" class="mt-6">
                            <p class="sr-only">{{ t('services.includes') }}</p>
                            <ul class="flex flex-wrap gap-1.5">
                                <li v-for="item in service.items" :key="item" class="chip">{{ item }}</li>
                            </ul>
                        </div>

                        <div class="mt-auto pt-8">
                            <a
                                :href="`#${anchor('contact')}`"
                                :class="['btn', i === 0 ? 'btn-ghost-ink' : 'btn-light']"
                                @click="intent.ask(service.slug)"
                            >
                                {{ t('services.ask') }}<span class="sr-only">: {{ service.title }}</span>
                                <ArrowRight :size="16" class="btn-arrow" aria-hidden="true" />
                            </a>
                        </div>
                    </article>
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
    max-width: 16ch;
}

.services-grid { display: grid; gap: 12px; }
@media (min-width: 48rem) {
    .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .service-featured { grid-column: span 2; }
}
@media (min-width: 64rem) {
    .services-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

.service { padding: clamp(20px, 2.4vw, 32px); }
.service-featured { background: var(--color-ink); color: var(--color-on-ink); }

.service-icon {
    display: grid; place-items: center; width: 52px; height: 52px; border-radius: 16px;
    background: var(--color-canvas); color: var(--color-ink);
}
.service-featured .service-icon { background: var(--brand-gradient); color: #fff; }
.service-index { color: var(--color-faint); }
.service-featured .service-index { color: var(--color-on-ink-muted); }

.service-summary { color: var(--color-muted); max-width: 52ch; }
.service-featured .service-summary { color: var(--color-on-ink-muted); }
</style>
