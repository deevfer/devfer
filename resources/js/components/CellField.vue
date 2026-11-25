<script setup lang="ts">
import { ref, watch } from 'vue';
import { COLS, COUNT, ROWS, targets, type LayoutName } from '@/lib/cells';
import { useClientEffect, useReducedMotion } from '@/composables/useMotion';

// Stepped jade ramp, light at the top of the field to deep at the bottom
const RAMP = ['221, 243, 234', '182, 232, 212', '132, 216, 184', '76, 196, 154', '0, 162, 122', '0, 120, 91'];
const INK = '20, 21, 24';
const DOT = { light: '20, 21, 24', dark: '255, 255, 255' };
const DURATION = 900;
const STAGGER = 3;

type Cell = { x: number; y: number; fill: number };
type Tween = { from: Cell; to: Cell };

const props = withDefaults(
    defineProps<{
        layout: LayoutName;
        /** Marks the most recent project in ink. */
        accent?: boolean;
        tone?: 'light' | 'dark';
    }>(),
    { accent: false, tone: 'light' },
);

// cubic-bezier(0.7, 0, 0.2, 1) — the "build" easing
function easeBuild(t: number) {
    const x1 = 0.7, y1 = 0, x2 = 0.2, y2 = 1;
    const bx = (u: number) => 3 * x1 * u * (1 - u) ** 2 + 3 * x2 * u * u * (1 - u) + u ** 3;
    const by = (u: number) => 3 * y1 * u * (1 - u) ** 2 + 3 * y2 * u * u * (1 - u) + u ** 3;
    let lo = 0, hi = 1, u = t;
    for (let i = 0; i < 18; i++) {
        u = (lo + hi) / 2;
        if (bx(u) < t) lo = u;
        else hi = u;
    }
    return by(u);
}

function resolve(name: LayoutName): Cell[] {
    return targets(name).map(({ slot, on }) => ({
        x: slot % COLS,
        y: Math.floor(slot / COLS),
        fill: on ? 1 : 0,
    }));
}

function roundRect(ctx: CanvasRenderingContext2D, x: number, y: number, s: number, r: number) {
    ctx.beginPath();
    ctx.roundRect(x, y, s, s, r);
    ctx.fill();
}

const canvasRef = ref<HTMLCanvasElement | null>(null);
const reduced = useReducedMotion();
let cells = resolve(props.layout);
let frame = 0;

function draw() {
    const canvas = canvasRef.value;
    const ctx = canvas?.getContext('2d');
    if (!canvas || !ctx) return;
    const { accent, tone } = props;

    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    const w = canvas.clientWidth;
    const h = canvas.clientHeight;
    if (canvas.width !== Math.round(w * dpr)) canvas.width = Math.round(w * dpr);
    if (canvas.height !== Math.round(h * dpr)) canvas.height = Math.round(h * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, w, h);

    const pitch = Math.min(w / COLS, h / ROWS);
    const size = pitch * 0.86;
    const ox = (w - pitch * COLS) / 2 + (pitch - size) / 2;
    const oy = (h - pitch * ROWS) / 2 + (pitch - size) / 2;
    const dotAlpha = tone === 'dark' ? 0.16 : 0.14;

    cells.forEach((c, i) => {
        const px = ox + c.x * pitch;
        const py = oy + c.y * pitch;
        if (c.fill < 1) {
            // Unbuilt cells are a quiet dot grid
            ctx.fillStyle = `rgba(${DOT[tone]}, ${dotAlpha * (1 - c.fill)})`;
            const d = Math.max(1.5, size * 0.16);
            roundRect(ctx, px + size / 2 - d / 2, py + size / 2 - d / 2, d, d / 2);
        }
        if (c.fill > 0) {
            const step = Math.min(RAMP.length - 1, Math.max(0, Math.floor((c.y / ROWS) * RAMP.length)));
            const color = accent && i === COUNT - 1 ? INK : RAMP[step];
            ctx.fillStyle = `rgba(${color}, ${c.fill})`;
            const s2 = size * (0.35 + 0.65 * c.fill);
            roundRect(ctx, px + (size - s2) / 2, py + (size - s2) / 2, s2, s2 * 0.16);
        }
    });
}

// Canvas sizing and redraws
useClientEffect(() => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ro = new ResizeObserver(draw);
    ro.observe(canvas);
    draw();
    return () => ro.disconnect();
});
watch(() => [props.accent, props.tone], draw, { flush: 'post' });

// Morph between layouts
useClientEffect(() => {
    const next = resolve(props.layout);
    cancelAnimationFrame(frame);

    if (reduced.value) {
        cells = next;
        draw();
        return;
    }

    const tweens: Tween[] = cells.map((from, i) => ({ from: { ...from }, to: next[i] }));
    const start = performance.now();

    const tick = (now: number) => {
        let done = true;
        cells = tweens.map(({ from, to }, i) => {
            const raw = (now - start - i * STAGGER) / DURATION;
            const t = raw <= 0 ? 0 : raw >= 1 ? 1 : easeBuild(raw);
            if (raw < 1) done = false;
            return {
                x: from.x + (to.x - from.x) * t,
                y: from.y + (to.y - from.y) * t,
                fill: from.fill + (to.fill - from.fill) * t,
            };
        });
        draw();
        if (!done) frame = requestAnimationFrame(tick);
    };
    frame = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(frame);
}, [() => props.layout, reduced]);
</script>

<template>
    <canvas ref="canvasRef" aria-hidden="true" :style="{ aspectRatio: `${COLS} / ${ROWS}` }" />
</template>
