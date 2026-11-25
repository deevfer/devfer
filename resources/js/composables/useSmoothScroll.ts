import Lenis from 'lenis';
import { useClientEffect } from './useMotion';
import '@/lib/scroll';

/** Smooth scrolling for mouse and trackpad only; never for touch or reduced motion. */
export function useSmoothScroll() {
    useClientEffect(() => {
        const fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!fine || reduced) return;

        const lenis = new Lenis({ lerp: 0.12, anchors: { offset: 0 } });
        window.__lenis = lenis;
        let raf = requestAnimationFrame(function loop(time) {
            lenis.raf(time);
            raf = requestAnimationFrame(loop);
        });
        return () => {
            cancelAnimationFrame(raf);
            lenis.destroy();
            window.__lenis = undefined;
        };
    });
}
