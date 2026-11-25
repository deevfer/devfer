<script setup lang="ts">
import SectionLabel from './SectionLabel.vue';
import { Download, FileText } from '@lucide/vue';
import { useSite, type CvFile } from '@/composables/useSite';

const { props, t, anchor, locale } = useSite();
const intl = locale === 'es' ? 'es-GT' : 'en-US';

const size = (bytes: number) => `${new Intl.NumberFormat(intl, { maximumFractionDigits: 1 }).format(bytes / 1048576)} MB`;
// UTC on both server and browser, so SSR and hydration print the same date.
const date = (iso: string) => new Intl.DateTimeFormat(intl, { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' }).format(new Date(`${iso}T00:00:00Z`));
const label = (cv: CvFile) => t(cv.locale === 'es' ? 'cv.es' : 'cv.en');
</script>

<template>
    <section :id="anchor('cv')" data-section="cv" class="section pt-0!" aria-labelledby="cv-title">
        <div class="container-site">
            <div class="card on-ink cv-card grid gap-10 p-6 sm:p-10 lg:grid-cols-12 lg:items-end lg:p-14">
                <div class="lg:col-span-6">
                    <SectionLabel section="cv" :label="t('cv.kicker')" tone="ink" />
                    <h2 id="cv-title" class="section-title mt-6">{{ t('cv.title') }}</h2>
                    <p class="mt-5 max-w-[44ch] text-lead text-on-ink-muted">{{ t('cv.lead') }}</p>
                </div>
                <ul class="grid gap-2 sm:grid-cols-2 lg:col-span-6">
                    <li v-for="cv in props.cv" :key="cv.locale" class="cv-file">
                        <FileText :size="22" aria-hidden="true" class="text-on-ink-muted" />
                        <p class="mt-8 text-lg font-semibold">{{ label(cv) }}</p>
                        <p class="mt-1 font-mono text-[0.75rem] text-on-ink-muted">
                            PDF · {{ size(cv.size) }}<template v-if="cv.updated"> · {{ t('cv.updated') }} {{ date(cv.updated) }}</template>
                        </p>
                        <a :href="cv.url" class="btn btn-light mt-6 w-full" download :hreflang="cv.locale">
                            {{ t('cv.download') }}<span class="sr-only">: {{ label(cv) }}</span>
                            <Download :size="16" class="btn-arrow btn-arrow-down" aria-hidden="true" />
                        </a>
                    </li>
                </ul>
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
.cv-card { background: var(--color-ink); color: var(--color-on-ink); }
.cv-file { padding: 1.25rem; border-radius: 22px; background: rgb(255 255 255 / 0.06); }
</style>
