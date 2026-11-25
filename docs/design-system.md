# Fernando Vásquez — Design system

v2 · Laravel 13 + Inertia + Vue 3. Tokens live in `resources/css/app.css` and are the
source of truth; this document explains the decisions behind them.

## Idea

A professional profile page that answers three questions in the first screen: who
Fernando is, what he does, and how to reach him. Large type on a light canvas, ink for
contrast, wide corners, and photography used sparingly inside the composition.

## Principles

1. **The headline does the work.** One statement in the largest type on the page, the name
   above it in a smaller size. Inline capsules (portrait, design → code) live inside the
   sentence instead of beside it.
2. **One dark surface per screen.** Ink cards (featured service, portrait, CV) carry the
   contrast; everything else sits on white cards over the canvas.
3. **Cards explain, they don't decorate.** Each card answers a question: what a service
   includes, where Fernando worked, which tools he uses.
4. **Facts only.** Copy comes from the CVs and the previous site. No year counts that the
   CV cannot confirm ("since 2016" instead), no client logos, no ratings.
5. **Equal in both languages.** Every component is checked with the longer of ES/EN.
6. **Motion has a job.** See `docs/motion.md`.

## Colour

| Token | Value | Use |
| --- | --- | --- |
| `canvas` | `#ecece9` | Page background |
| `surface` | `#ffffff` | Cards |
| `sunken` | `#e2e2de` | Generic logo symbol, inset tiles |
| `ink` | `#111113` | Text, dark cards, primary buttons |
| `muted` | `#5d5d63` | Secondary text (5.5:1 on canvas) |
| `faint` | `#77777d` | Large secondary text, numbers |
| `accent` | `#3355ff` | Focus ring, status dots, featured icon (from the CV blue) |
| `accent-ink` | `#2440d6` | Accent text on light backgrounds |

The accent appears in small doses: a dot, a ring, one icon tile.

## Type

Geist for everything, Geist Mono for small labels (kickers, numbers, file sizes). Both
self-hosted by `laravel-vite-plugin/fonts` with metric-matched fallbacks.

| Role | Size | Tracking |
| --- | --- | --- |
| Hero statement | `clamp(2.75rem, 6.3vw, 6.5rem)`, 600 | -0.045em |
| Hero intro (name) | `clamp(1.75rem, 3.4vw, 3.25rem)`, 600 | -0.035em |
| Section title | `clamp(2.1rem, 4.6vw, 4.25rem)`, 600 | -0.04em |
| Card title | `clamp(1.375rem, 1.9vw, 1.75rem)`, 600 | -0.02em |
| Lead | `clamp(1.0625rem, 1.3vw, 1.25rem)` | — |
| Body | 1rem / 1.6 | — |
| Label | 0.75rem mono | 0.02em |

## Shape and space

- Cards: `--radius-card` `clamp(24px, 2.4vw, 36px)`; tiles 14–22px; buttons and chips are pills.
- Page gutter `clamp(16px, 4vw, 48px)`, max width 88rem, sections `clamp(4.5rem, 9vw, 8.5rem)`.
- Touch targets ≥ 44px; buttons 48px.

## Sections (anchors ES / EN)

1. Hero — `#inicio` / `#top`
2. Services — `#servicios` / `#services`: bento grid, first card featured in ink
3. Profile and experience — `#perfil` / `#profile`: portrait card, bio, education, timeline
4. Technologies — `#tecnologias` / `#technologies`: grouped tiles with official marks in ink
5. CV — `#cv`: both PDFs on both language versions
6. Contact — `#contacto` / `#contact`: details and form

## Technology logos

Official marks from Simple Icons (CC0 collection of brand SVGs; individual marks keep
their owners' trademarks and licences, e.g. Vue.js CC BY-NC-SA). Drawn as a mask in ink
so the set reads as one system; the brand colour appears on hover. Brands that do not
allow redistribution (Adobe, AWS, Oracle, OpenAI/ChatGPT, AtomChat.io) use a generic
symbol with the name. Logos can be replaced from the panel.

## Accessibility

- Visible focus ring (`2px` accent, `3px` offset) on every interactive element.
- Skip link, landmark navigation, `aria-current` on the active section.
- Mobile menu is a modal `<dialog>`: focus moves in, is trapped, Escape closes, focus returns.
- Form: labels, hints and errors linked with `aria-describedby`, `aria-invalid`, an
  error summary that receives focus, and a status message on success.
- `prefers-reduced-motion` keeps fades and colour changes and removes movement.
