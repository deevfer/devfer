<script setup lang="ts">
import { computed, type Component } from 'vue';
import { BriefcaseBusiness, CodeXml, FileText, MessageCircle, UserRound } from '@lucide/vue';

/*
 * Section label: the section's dock icon in a blue disc plus its name, on a
 * raised white pill. Using the same icon as the navigation ties each section
 * back to the button that leads to it.
 */
const props = withDefaults(defineProps<{ section: string; label: string; tone?: 'light' | 'ink' }>(), { tone: 'light' });
const ICONS: Record<string, Component> = {
    profile: UserRound,
    services: BriefcaseBusiness,
    technologies: CodeXml,
    cv: FileText,
    contact: MessageCircle,
};
const icon = computed(() => ICONS[props.section] ?? FileText);
</script>

<template>
    <p :class="['section-label', tone === 'ink' && 'is-ink']">
        <span class="section-label-icon" aria-hidden="true"><component :is="icon" :size="14" :stroke-width="2.25" /></span>
        {{ label }}
    </p>
</template>

<style scoped>
.section-label {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    height: 38px;
    padding: 0 16px 0 5px;
    border-radius: 999px;
    background: var(--color-surface);
    color: var(--color-ink);
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    white-space: nowrap;
    box-shadow:
        0 0 0 1px rgb(17 17 19 / 0.06),
        0 1px 2px rgb(17 17 19 / 0.05),
        0 10px 22px -14px rgb(17 17 19 / 0.35);
}
.section-label-icon {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: var(--brand-gradient);
    color: #fff;
    box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.18), 0 2px 6px -2px rgb(249 80 131 / 0.55);
}
.is-ink {
    background: rgb(255 255 255 / 0.07);
    color: var(--color-on-ink);
    box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.1);
}
</style>
