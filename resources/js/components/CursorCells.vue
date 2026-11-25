<script setup lang="ts">
import { ref } from 'vue';
import { useClientEffect } from '@/composables/useMotion';

const CELL = 28;
const RAMP = ['221, 243, 234', '182, 232, 212', '132, 216, 184', '76, 196, 154', '0, 162, 122'];

/**
 * Cells the cursor builds as it crosses the artboard: they light up in jade
 * steps and fade back to the dot grid.
 */
const canvasRef = ref<HTMLCanvasElement | null>(null);

useClientEffect(() => {
    const canvas = canvasRef.value;
    const host = canvas?.parentElement;
    const ctx = canvas?.getContext('2d');
    if (!canvas || !host || !ctx) return;

    const heat = new Map<string, number>();
    let raf = 0;
    let last: { x: number; y: number } | null = null;

    const size = () => {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = host.clientWidth * dpr;
        canvas.height = host.clientHeight * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    };

    const draw = () => {
        raf = 0;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        heat.forEach((h, key) => {
            const [cx, cy] = key.split(':').map(Number);
            const step = RAMP[Math.min(RAMP.length - 1, Math.floor(h * RAMP.length))];
            ctx.fillStyle = `rgba(${step}, ${Math.min(1, h * 1.4)})`;
            const s = (CELL - 4) * (0.55 + 0.45 * h);
            ctx.beginPath();
            ctx.roundRect(cx * CELL + (CELL - s) / 2, cy * CELL + (CELL - s) / 2, s, s, 3);
            ctx.fill();
            const next = h - 0.022;
            if (next <= 0) heat.delete(key);
            else heat.set(key, next);
        });
        if (heat.size) raf = requestAnimationFrame(draw);
    };

    const touch = (x: number, y: number) => {
        const key = `${Math.floor(x / CELL)}:${Math.floor(y / CELL)}`;
        heat.set(key, Math.min(1, (heat.get(key) ?? 0) + 0.6));
    };

    const onMove = (e: PointerEvent) => {
        const r = host.getBoundingClientRect();
        const p = { x: e.clientX - r.left, y: e.clientY - r.top };
        // Fill the gap between pointer events so fast moves leave a continuous trail
        if (last) {
            const steps = Math.ceil(Math.hypot(p.x - last.x, p.y - last.y) / (CELL / 2));
            for (let i = 1; i <= steps; i++) {
                touch(last.x + ((p.x - last.x) * i) / steps, last.y + ((p.y - last.y) * i) / steps);
            }
        } else touch(p.x, p.y);
        last = p;
        if (!raf) raf = requestAnimationFrame(draw);
    };
    const onLeave = () => (last = null);

    size();
    const ro = new ResizeObserver(size);
    ro.observe(host);
    host.addEventListener('pointermove', onMove);
    host.addEventListener('pointerleave', onLeave);
    return () => {
        ro.disconnect();
        cancelAnimationFrame(raf);
        host.removeEventListener('pointermove', onMove);
        host.removeEventListener('pointerleave', onLeave);
    };
});
</script>

<template>
    <canvas ref="canvasRef" aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 h-full w-full" />
</template>
