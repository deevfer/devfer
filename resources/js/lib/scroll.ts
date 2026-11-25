import type Lenis from "lenis";

declare global {
  interface Window {
    __lenis?: Lenis;
  }
}

/** Scrolls through Lenis when it is running, natively otherwise. */
export function scrollToY(y: number, smooth = true) {
  if (window.__lenis) window.__lenis.scrollTo(y, { immediate: !smooth, duration: 1.4 });
  else window.scrollTo({ top: y, behavior: smooth ? "smooth" : "auto" });
}
