<script setup lang="ts">
import { useI18n } from '@/composables/useI18n';

/** Each line is two words that sit together on wide screens and stack on phones. */
defineProps<{ wire?: boolean }>();
const { t } = useI18n('hero');
const LINES = [
    ['a1', 'a2'],
    ['b1', 'b2'],
];
</script>

<template>
    <span v-for="line in LINES" :key="line[0]" class="block">
        <template v-for="(key, i) in line" :key="key">
            <span v-if="i > 0" class="hidden md:inline">{{ ' ' }}</span>
            <span class="block md:inline">
                <span class="word inline-block"
                    >{{ t(key) }}<span v-if="wire" class="handles" aria-hidden="true"><i /><i /><i /><i /></span
                ></span>
            </span>
        </template>
    </span>
</template>
