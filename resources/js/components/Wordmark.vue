<script setup lang="ts">
// "FV" drawn in cells on a dark tile
const F = ['###', '#..', '##.', '#..', '#..'];
const V = ['#.#', '#.#', '#.#', '#.#', '.#.'];
const rects: [number, number][] = [];
F.forEach((r, y) => [...r].forEach((c, x) => c === '#' && rects.push([x, y])));
V.forEach((r, y) => [...r].forEach((c, x) => c === '#' && rects.push([x + 4, y])));
</script>

<template>
    <span class="grid size-11 place-items-center rounded-[10px] bg-ink">
        <svg viewBox="0 0 7 5" width="22" height="16" aria-hidden="true">
            <rect
                v-for="[x, y] in rects"
                :key="`${x}-${y}`"
                :x="x + 0.1"
                :y="y + 0.1"
                width="0.8"
                height="0.8"
                rx="0.12"
                :fill="y === 4 && x === 5 ? '#00a27a' : '#f7f7f5'"
            />
        </svg>
    </span>
</template>
