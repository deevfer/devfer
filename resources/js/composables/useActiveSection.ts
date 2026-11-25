import { ref } from 'vue';
import type { SectionKey } from './useSite';
import { useClientEffect } from './useMotion';

/** The section in the middle of the viewport, read from `data-section` attributes. */
export function useActiveSection() {
    const active = ref<SectionKey>('top');
    useClientEffect(() => {
        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => e.isIntersecting && (active.value = e.target.getAttribute('data-section') as SectionKey)),
            { rootMargin: '-45% 0px -50% 0px' },
        );
        document.querySelectorAll('[data-section]').forEach((el) => io.observe(el));
        return () => io.disconnect();
    });
    return active;
}
