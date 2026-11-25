<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';
import { LAST_YEAR, STAGES, type Stage } from '@/content/evolution';

const props = defineProps<{ stage: Stage; index: number; active: boolean }>();
const { t, locale } = useI18n('evolution');

const copy = computed(() => props.stage[locale]);
const range = computed(() => {
    const { from, to } = props.stage;
    const end = to === LAST_YEAR ? t('today') : to;
    return from === to ? `${from}` : `${from}–${end}`;
});
</script>

<template>
    <div class="evo-stage" :data-active="active">
        <p class="label text-graphite">
            {{ t('stage', { n: index + 1, total: STAGES.length }) }}
            <span class="mx-2 text-hairline">/</span>
            {{ range }}
        </p>
        <h3 class="display mt-4 text-display-l text-balance">{{ copy.title }}</h3>
        <p class="mt-4 max-w-[36ch] text-lead text-graphite">{{ copy.body }}</p>
        <p v-if="copy.learned" class="mt-4 max-w-[36ch] text-lead">{{ copy.learned }}</p>
        <ul v-if="stage.tech.length > 0" class="mt-5 flex flex-wrap gap-2">
            <li v-for="name in stage.tech" :key="name" class="chip">{{ name }}</li>
        </ul>
    </div>
</template>
