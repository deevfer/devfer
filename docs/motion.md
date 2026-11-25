# Motion inventory

Every animation on the public site, with its purpose and values, ready for a
`review-animations` pass. Curves come from `resources/css/app.css` (`--ease-out:
cubic-bezier(0.23, 1, 0.32, 1)`, `--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1)`).
Everything animates `transform` and `opacity` except where noted.

| Where | What | Purpose | Values | Reduced motion |
| --- | --- | --- | --- | --- |
| `resources/css/app.css` `.btn` | Press | Feedback | `:active` scale 0.97, 160ms `--ease-out` | No scale |
| `resources/css/app.css` `.btn-arrow` | Arrow nudge on hover | Feedback (leads somewhere) | translateX/Y 2px, 200ms `--ease-out`, hover devices only | Removed |
| `SiteHeader.vue` `.tip` | Label under a dock icon | Feedback / identification of icon-only links | opacity + translateY(-4px) scale(0.97), 125ms `--ease-out`; first label waits 350ms, neighbours open instantly with no motion while `data-warm`; keyboard focus shows it at once | No movement |
| `SiteHeader.vue` `.dock-item:active`, `.dock-cta:active` | Press | Feedback | scale 0.94 / 0.97, 160ms `--ease-out` | No scale |
| `SiteHeader.vue` `.dock-avatar` | Portrait slides into the dock after the hero | Spatial: the dock grows to make room | `width` 0→44px, 260ms `--ease-out` + opacity 200ms. **Deliberate exception** to transform-only: a single 44px element that changes once per scroll past the hero | Fade only |
| `SiteHeader.vue` `.dock-cta` | Hover colour (ink → accent) and arrow nudge | Feedback | background-color 200ms, arrow translateX(2px) 200ms, hover devices only | Same colour change |
| `HeroSection.vue` `.hero-in` | First-visit entrance | Rare, first paint | translateY(12px) + opacity, 520ms `--ease-out`, delays 0/60/110/150/180ms; only with `html.js` | Removed |
| `ContactSection.vue` `.field-error`, `.summary` | Errors appear in place | Preventing a jarring change | `@starting-style` translateY(-2px / -4px) + opacity, 180ms / 200ms | Fade only |
| `ContactSection.vue` `.is-picked` | Service preselected from a card | State indication | box-shadow ring 0→10px, 600ms `--ease-out` (box-shadow, one shot) | Static ring for 800ms |
| `ContactSection.vue` `.swap-*` | Form ⇄ success | Preventing a jarring change | enter 260ms opacity + translateY(6px) + blur(2px); leave 160ms opacity + blur | Fade only |
| `ContactSection.vue` `.done-icon` | Success check lands | Delight (rare: once per message) | scale 0.85→1 + opacity, 420ms `cubic-bezier(0.34, 1.4, 0.64, 1)`, 120ms delay | Removed |
| `ContactSection.vue` `.spin` | Sending spinner | State | rotate, 700ms linear, infinite while sending | Static icon |
| `TechnologiesSection.vue` `.tech-panel` | Category opens / closes (one at a time) | State indication | `grid-template-rows` 0fr→1fr 300ms `--ease-out` + opacity 200ms; clips while moving, then `overflow: visible` so tooltips can extend. **Deliberate exception** to transform-only: accordion height | Opacity only |
| `TechnologiesSection.vue` `.tech-toggle` | + turns into × | State indication | rotate(45deg) 240ms `--ease-out`; press scale 0.94 | No rotation |
| `TechnologiesSection.vue` `.tip` | Tool name above each logo | Identification (logos have no visible label) | Same rules as the dock labels: 125ms, first one waits 350ms, neighbours instant; shown on keyboard focus | No movement |
| `TechnologiesSection.vue` `.logo-tile:active` | Press | Feedback | scale 0.95, 160ms | No scale |
| `LangSwitch.vue`, nav, footer links | Colour on hover | Feedback | color / background-color 160ms | Same |

Global guard: `@media (prefers-reduced-motion: reduce)` in `app.css` limits
transitions to opacity and colour and shortens animations; smooth anchor
scrolling only runs with `prefers-reduced-motion: no-preference`.

Deliberately not animated (from the `find-animation-opportunities` pass):
section reveals on scroll, a sliding nav indicator, a hide-on-scroll dock,
card or logo hover lifts, and a crossfade on the submit label (it would delay
the "Sending…" signal).
