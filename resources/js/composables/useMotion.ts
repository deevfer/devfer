import { onBeforeUnmount, onMounted, ref, watch, type Ref, type WatchSource } from 'vue';

/** False on the server and on the first client render, then the live value. */
export function useMediaQuery(query: string): Ref<boolean> {
    const matches = ref(false);
    let mq: MediaQueryList | undefined;
    const update = () => (matches.value = mq!.matches);
    onMounted(() => {
        mq = window.matchMedia(query);
        update();
        mq.addEventListener('change', update);
    });
    onBeforeUnmount(() => mq?.removeEventListener('change', update));
    return matches;
}

export const useReducedMotion = () => useMediaQuery('(prefers-reduced-motion: reduce)');
export const useFinePointer = () => useMediaQuery('(hover: hover) and (pointer: fine)');
export const useDesktop = () => useMediaQuery('(min-width: 64rem)');

type Cleanup = void | (() => void);

/**
 * Runs `effect` after mount and again whenever a source changes, calling the
 * previous cleanup first, and cleans up on unmount. Call it after the
 * media-query composables it depends on so it starts with their real value.
 */
export function useClientEffect(effect: () => Cleanup, sources: WatchSource[] = []) {
    let cleanup: Cleanup;
    let stop: (() => void) | undefined;
    const run = () => {
        cleanup?.();
        cleanup = effect();
    };
    onMounted(() => {
        run();
        // After the DOM update, like a React effect
        if (sources.length) stop = watch(sources, run, { flush: 'post' });
    });
    onBeforeUnmount(() => {
        stop?.();
        cleanup?.();
    });
}
