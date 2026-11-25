<script setup lang="ts">
import { computed } from 'vue';

/** A rolling year: each digit is a strip that slides, like an odometer. */
const props = defineProps<{ value: number }>();
const DIGITS = 4;
const STRIP = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0];

const offsets = computed(() =>
    Array.from({ length: DIGITS }, (_, i) => {
        const k = DIGITS - 1 - i; // 0 = ones
        const p = 10 ** k;
        const whole = Math.floor(props.value / p);
        // Higher digits only roll while every lower digit is passing 9 → 0
        const roll = k === 0 ? props.value - Math.floor(props.value) : Math.max(0, (props.value % p) - (p - 1));
        return { k, offset: (whole % 10) + roll };
    }),
);
</script>

<template>
    <span class="odo tabular-nums" aria-hidden="true">
        <span v-for="{ k, offset } in offsets" :key="k" class="odo-col" :style="{ transform: `translateY(${-offset * 0.9}em)` }">
            <span v-for="(d, j) in STRIP" :key="j">{{ d }}</span>
        </span>
    </span>
</template>
