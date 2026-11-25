<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';
import { ANCHORS } from '@/lib/scenes';

const { t, locale, email } = useI18n();
const copied = ref(false);
const mailto = `mailto:${email}?subject=${encodeURIComponent(t('contact.subject'))}`;
const [user, domain] = email.split('@');
const year = new Date().getFullYear();
let timer: ReturnType<typeof setTimeout> | undefined;

async function copy() {
    try {
        await navigator.clipboard.writeText(email);
        copied.value = true;
        clearTimeout(timer);
        timer = setTimeout(() => (copied.value = false), 2400);
    } catch {
        window.location.href = mailto;
    }
}
onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <section :id="ANCHORS[locale].contact" data-scene="contact" aria-labelledby="contact-title">
        <div class="artboard flex min-h-[calc(100svh-2*var(--inset))] flex-col">
            <div class="frame flex flex-1 flex-col items-center justify-center pt-28 pb-12 text-center">
                <p class="label inline-flex items-center gap-2 text-graphite">
                    <span aria-hidden="true" class="block size-2 rounded-[2px] bg-jade" />
                    {{ t('contact.label') }}
                </p>
                <h2 id="contact-title" class="display mt-8 max-w-[14ch] text-display-xl text-balance">
                    {{ t('contact.title') }}
                </h2>
                <p class="mt-8 max-w-[44ch] text-lead text-graphite">{{ t('contact.lead') }}</p>

                <a :href="mailto" class="contact-email display link-line mt-14 inline-block text-ink">
                    <span class="block md:inline">{{ user }}@</span>
                    <span class="block md:inline">{{ domain }}</span>
                </a>

                <div class="mt-10 flex flex-wrap justify-center gap-2">
                    <button type="button" class="pill pill-ink" @click="copy">
                        <span aria-hidden="true" :class="['block size-2 rounded-[2px]', copied ? 'bg-jade' : 'bg-panel']" />
                        {{ copied ? t('contact.copied') : t('contact.copy') }}
                    </button>
                    <a :href="mailto" class="pill pill-line">{{ t('contact.write') }}</a>
                    <span role="status" aria-live="polite" class="sr-only">{{ copied ? t('contact.copied') : '' }}</span>
                </div>
            </div>

            <footer class="frame flex flex-col gap-3 pb-8 text-small text-graphite md:flex-row md:items-center md:justify-between">
                <p>© {{ year }} {{ t('footer.rights') }}. {{ t('footer.built') }}</p>
                <a :href="`#${ANCHORS[locale].intro}`" class="label link-line self-start text-ink md:self-auto">
                    {{ t('footer.top') }}
                </a>
            </footer>
        </div>
    </section>
</template>
