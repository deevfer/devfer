<script setup lang="ts">
import { useSite } from '@/composables/useSite';

// The memoji as Fernando's mark (no background), with the initials as a fallback.
const { props } = useSite();
</script>

<template>
    <img v-if="props.profile.memoji" :src="props.profile.memoji" alt="" width="44" height="44" class="mark" aria-hidden="true" />
    <span v-else class="mark mark-fallback" aria-hidden="true">FV</span>
</template>

<style scoped>
.mark { display: block; width: 44px; height: 44px; object-fit: contain; }
.mark-fallback { display: grid; place-items: center; border-radius: 14px; background: var(--color-ink); color: var(--color-on-ink); font-weight: 600; letter-spacing: -0.04em; }
</style>
