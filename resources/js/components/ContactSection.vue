<script setup lang="ts">
import SectionLabel from './SectionLabel.vue';
import { computed, nextTick, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ArrowRight, CircleAlert, CircleCheck, LoaderCircle, Mail, MapPin } from '@lucide/vue';
import AppIcon from './AppIcon.vue';
import { useSite } from '@/composables/useSite';
import { useContactIntent } from '@/composables/useContactIntent';
import { useRecaptcha } from '@/composables/useRecaptcha';
import { useClientEffect } from '@/composables/useMotion';

const { props, t, anchor, locale } = useSite();
const intent = useContactIntent();
const recaptcha = useRecaptcha();

const form = useForm({
    name: '',
    email: '',
    company: '',
    phone: '',
    service: '',
    message: '',
    website: '', // honeypot
    recaptcha_token: '',
});

const FIELDS = ['name', 'email', 'phone', 'company', 'service', 'message'] as const;
type FieldName = (typeof FIELDS)[number];

const sentTo = ref<string | null>(null);
const summary = ref<HTMLElement | null>(null);
const success = ref<HTMLElement | null>(null);
const serviceSelect = ref<HTMLSelectElement | null>(null);
const recaptchaBox = ref<HTMLElement | null>(null);
const highlightService = ref(false);

const fieldErrors = computed(() => FIELDS.filter((f) => form.errors[f]));
const formError = computed(() => (form.errors as Record<string, string | undefined>).form);
const hasErrors = computed(() => fieldErrors.value.length > 0 || !!formError.value);
const err = (f: FieldName) => form.errors[f];
const describedBy = (f: FieldName, hint?: string) => [hint, err(f) ? `${f}-error` : null].filter(Boolean).join(' ') || undefined;

// Load reCAPTCHA once the form is near the viewport (v2 also renders its checkbox).
useClientEffect(() => {
    if (!recaptcha.config.enabled) return;
    const el = document.getElementById('contact-form');
    if (!el) return;
    const io = new IntersectionObserver(
        (entries) => {
            if (entries.some((e) => e.isIntersecting)) {
                io.disconnect();
                recaptcha.load().then(() => recaptcha.mount(recaptchaBox.value)).catch(() => {});
            }
        },
        { rootMargin: '400px 0px' },
    );
    io.observe(el);
    return () => io.disconnect();
});

// A service card's button preselects that service and points the visitor at it.
watch(intent.requests, async () => {
    if (!intent.service.value) return;
    form.service = intent.service.value;
    sentTo.value = null;
    await nextTick();
    serviceSelect.value?.focus({ preventScroll: true });
    highlightService.value = false;
    requestAnimationFrame(() => (highlightService.value = true));
    setTimeout(() => (highlightService.value = false), 800);
});

async function submit() {
    if (form.processing) return;
    form.clearErrors();
    try {
        form.recaptcha_token = await recaptcha.token();
    } catch {
        form.setError('form' as FieldName, t('errors.recaptcha'));
        await nextTick();
        summary.value?.focus();
        return;
    }
    form.post(`/${locale}/contacto`, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: async () => {
            if (hasErrors.value) return;
            sentTo.value = form.name;
            form.reset();
            recaptcha.reset();
        },
        onError: async () => {
            recaptcha.reset();
            await nextTick();
            summary.value?.focus();
        },
    });
}

function sendAnother() {
    sentTo.value = null;
}

function updatePhone(event: Event) {
    const input = event.target as HTMLInputElement;
    input.value = input.value.replace(/[^0-9]/g, '');
    form.phone = input.value;
}

// Focus moves once the new state has entered (the swap waits for the old one to leave).
function onSwapped() {
    if (sentTo.value) success.value?.focus();
    else document.getElementById('field-name')?.focus();
}
</script>

<template>
    <section :id="anchor('contact')" data-section="contact" class="section pt-0!" aria-labelledby="contact-title">
        <div class="container-site">
            <div class="grid gap-3 lg:grid-cols-12">
                <div class="flex flex-col py-2 lg:col-span-5 lg:pr-10">
                    <SectionLabel section="contact" :label="t('contact.kicker')" class="self-start" />
                    <h2 id="contact-title" class="section-title mt-6">{{ t('contact.title') }}</h2>
                    <p class="mt-5 max-w-[42ch] text-lead text-muted">{{ t('contact.lead') }}</p>

                    <dl class="mt-10 grid gap-2">
                        <div class="info">
                            <dt class="font-mono text-label text-muted">{{ t('contact.email_label') }}</dt>
                            <dd><a :href="`mailto:${props.profile.email}`" class="info-link"><Mail :size="18" aria-hidden="true" />{{ props.profile.email }}</a></dd>
                        </div>
                        <div class="info">
                            <dt class="font-mono text-label text-muted">{{ t('contact.location_label') }}</dt>
                            <dd class="info-link"><MapPin :size="18" aria-hidden="true" />{{ t('hero.location') }}</dd>
                        </div>
                    </dl>
                    <ul v-if="props.links.some((l) => !l.url.startsWith('mailto:'))" class="mt-4 flex flex-wrap gap-2">
                        <li v-for="link in props.links.filter((l) => !l.url.startsWith('mailto:'))" :key="link.url">
                            <a :href="link.url" class="btn btn-light" target="_blank" rel="noopener">
                                <AppIcon :name="link.icon" :size="16" /> {{ link.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card p-5 sm:p-8 lg:col-span-7 lg:p-10">
                    <Transition name="swap" mode="out-in" @after-enter="onSwapped">
                        <!-- Success -->
                        <div v-if="sentTo" key="done" class="flex min-h-[420px] flex-col items-start justify-center">
                            <span class="done-icon grid size-14 place-items-center rounded-full text-white" style="background: var(--brand-gradient)">
                                <CircleCheck :size="28" aria-hidden="true" />
                            </span>
                            <h3 ref="success" tabindex="-1" class="mt-6 text-h3 font-semibold outline-none">{{ t('form.success_title') }}</h3>
                            <p class="mt-3 max-w-[44ch] text-lead text-muted" role="status">{{ t('form.success_body', { name: sentTo }) }}</p>
                            <button type="button" class="btn btn-light mt-8" @click="sendAnother">{{ t('form.send_another') }}</button>
                        </div>

                        <!-- Form -->
                        <form v-else key="form" id="contact-form" novalidate class="grid gap-5" :aria-busy="form.processing" @submit.prevent="submit">
                            <div v-if="hasErrors" ref="summary" tabindex="-1" class="summary" role="alert">
                                <CircleAlert :size="20" class="mt-0.5 shrink-0" aria-hidden="true" />
                                <div>
                                    <p class="font-medium">{{ formError ?? t('form.errors_summary') }}</p>
                                    <ul v-if="fieldErrors.length" class="mt-1.5 grid gap-0.5 text-sm">
                                        <li v-for="f in fieldErrors" :key="f">
                                            <a :href="`#field-${f}`" class="underline underline-offset-2">{{ t(`form.${f}`) }}</a>: {{ err(f) }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label for="field-name" class="field-label">{{ t('form.name') }}</label>
                                    <input id="field-name" v-model="form.name" class="field mt-2" type="text" name="name" autocomplete="name" maxlength="120" required :aria-invalid="!!err('name')" :aria-describedby="describedBy('name')" />
                                    <p v-if="err('name')" id="name-error" class="field-error"><CircleAlert :size="15" class="mt-px shrink-0" aria-hidden="true" />{{ err('name') }}</p>
                                </div>
                                <div>
                                    <label for="field-email" class="field-label">{{ t('form.email') }}</label>
                                    <input id="field-email" v-model="form.email" class="field mt-2" type="email" name="email" autocomplete="email" inputmode="email" maxlength="190" required :aria-invalid="!!err('email')" :aria-describedby="describedBy('email')" />
                                    <p v-if="err('email')" id="email-error" class="field-error"><CircleAlert :size="15" class="mt-px shrink-0" aria-hidden="true" />{{ err('email') }}</p>
                                </div>
                                <div>
                                    <label for="field-phone" class="field-label">{{ t('form.phone') }}</label>
                                    <input id="field-phone" :value="form.phone" class="field mt-2" type="tel" name="phone" autocomplete="tel" inputmode="numeric" pattern="[0-9]*" maxlength="40" required :aria-invalid="!!err('phone')" :aria-describedby="describedBy('phone')" @input="updatePhone" />
                                    <p v-if="err('phone')" id="phone-error" class="field-error"><CircleAlert :size="15" class="mt-px shrink-0" aria-hidden="true" />{{ err('phone') }}</p>
                                </div>
                                <div>
                                    <label for="field-company" class="field-label">{{ t('form.company') }} <small>{{ t('form.optional') }}</small></label>
                                    <input id="field-company" v-model="form.company" class="field mt-2" type="text" name="company" autocomplete="organization" maxlength="120" :aria-invalid="!!err('company')" :aria-describedby="describedBy('company')" />
                                    <p v-if="err('company')" id="company-error" class="field-error"><CircleAlert :size="15" class="mt-px shrink-0" aria-hidden="true" />{{ err('company') }}</p>
                                </div>
                            </div>

                            <div>
                                <label for="field-service" class="field-label">{{ t('form.service') }}</label>
                                <select
                                    id="field-service"
                                    ref="serviceSelect"
                                    v-model="form.service"
                                    :class="['field mt-2', highlightService && 'is-picked']"
                                    name="service"
                                    required
                                    :aria-invalid="!!err('service')"
                                    :aria-describedby="describedBy('service')"
                                >
                                    <option value="" disabled>{{ t('form.service_placeholder') }}</option>
                                    <option v-for="s in props.services" :key="s.slug" :value="s.slug">{{ s.title }}</option>
                                    <option value="other">{{ t('form.service_other') }}</option>
                                </select>
                                <p v-if="err('service')" id="service-error" class="field-error"><CircleAlert :size="15" class="mt-px shrink-0" aria-hidden="true" />{{ err('service') }}</p>
                            </div>

                            <div>
                                <label for="field-message" class="field-label">{{ t('form.message') }}</label>
                                <p id="message-hint" class="mt-1 text-sm text-muted">{{ t('form.message_hint') }}</p>
                                <textarea id="field-message" v-model="form.message" class="field mt-2" name="message" rows="5" maxlength="5000" required :aria-invalid="!!err('message')" :aria-describedby="describedBy('message', 'message-hint')" />
                                <p v-if="err('message')" id="message-error" class="field-error"><CircleAlert :size="15" class="mt-px shrink-0" aria-hidden="true" />{{ err('message') }}</p>
                            </div>

                            <!-- Honeypot: hidden from people and assistive tech -->
                            <div class="hp" aria-hidden="true">
                                <label for="field-website">Website</label>
                                <input id="field-website" v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off" />
                            </div>

                            <div v-if="recaptcha.config.enabled && recaptcha.config.version === 'v2'" ref="recaptchaBox" />

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <p v-if="recaptcha.config.enabled" class="max-w-[46ch] text-[0.75rem] leading-snug text-faint">
                                    {{ t('form.recaptcha_notice') }}
                                </p>
                                <span v-else />
                                <button type="submit" class="btn btn-ink submit" :disabled="form.processing">
                                    <span class="submit-label" :data-busy="form.processing">
                                        <LoaderCircle v-if="form.processing" :size="16" class="spin" aria-hidden="true" />
                                        {{ form.processing ? t('form.sending') : t('form.submit') }}
                                        <ArrowRight v-if="!form.processing" :size="16" class="btn-arrow" aria-hidden="true" />
                                    </span>
                                </button>
                            </div>
                        </form>
                    </Transition>
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
    max-width: 12ch;
}
.info { padding: 1rem 1.1rem; border-radius: var(--radius-tile); background: var(--color-surface); }
.info-link { display: inline-flex; align-items: center; gap: 0.55rem; margin-top: 0.35rem; font-size: 1.0625rem; font-weight: 500; overflow-wrap: anywhere; }

.summary {
    display: flex; gap: 0.75rem; padding: 1rem 1.1rem; border-radius: 16px;
    background: #fff1ef; color: var(--color-danger); outline: none;
}
.summary:focus-visible { outline: 2px solid var(--color-danger); outline-offset: 2px; }

.hp { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }

.submit { min-width: 11.5rem; }
.submit-label { display: inline-flex; align-items: center; gap: 0.6rem; }
.spin { animation: spin 700ms linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Field errors appear in place: a short fade and 2px drop, so the eye catches them. */
.field-error {
    transition: opacity 180ms var(--ease-out), transform 180ms var(--ease-out);
    @starting-style { opacity: 0; transform: translateY(-2px); }
}
.summary {
    transition: opacity 200ms var(--ease-out), transform 200ms var(--ease-out);
    @starting-style { opacity: 0; transform: translateY(-4px); }
}

/* Preselected service: one ring pulse confirms which field changed. */
.is-picked { animation: picked 600ms var(--ease-out); }
@keyframes picked {
    0% { box-shadow: 0 0 0 0 rgb(249 80 131 / 0.45); }
    100% { box-shadow: 0 0 0 10px rgb(249 80 131 / 0); }
}

/* Sending a message is rare: the check gets a small overshoot as it lands. */
.done-icon { animation: done-pop 420ms cubic-bezier(0.34, 1.4, 0.64, 1) 120ms both; }
@keyframes done-pop {
    from { opacity: 0; transform: scale(0.85); }
}

/* Form ⇄ success: crossfade with a light blur so the two states read as one change. */
.swap-enter-active { transition: opacity 260ms var(--ease-out), transform 260ms var(--ease-out), filter 260ms var(--ease-out); }
.swap-leave-active { transition: opacity 160ms ease, filter 160ms ease; }
.swap-enter-from { opacity: 0; transform: translateY(6px); filter: blur(2px); }
.swap-leave-to { opacity: 0; filter: blur(2px); }

@media (prefers-reduced-motion: reduce) {
    .spin { animation: none; }
    .done-icon { animation: none; }
    .is-picked { animation: none; box-shadow: 0 0 0 3px rgb(249 80 131 / 0.35); }
    .swap-enter-from { transform: none; filter: none; }
    .swap-leave-to { filter: none; }
}
</style>
