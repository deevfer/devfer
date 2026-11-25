# Compiled — Design System

Fernando Vásquez · personal site · v0.1 (Phase 3)

The idea in one line: **a designer who builds.** Everything on the page starts as a
design artifact (outline, wireframe, cell) and compiles into working software in front of
the visitor. One visual system carries the whole site: **200 cells — one per project —
arranged across 11 columns, one per year.**

> **Revision v0.2 (art direction refresh).** After a visual audit against incredibles.dev:
> display type is now **Funnel Display at regular weight** with tight tracking (Anybody removed);
> surfaces are an inset dotted **artboard** (hero, contact) and one **dark** section (industries);
> cells use a **stepped jade ramp** (`--color-j1`…`--color-j6`) instead of flat ink;
> the hero is a design⇄build **scanner** ("Designed by Luisfer. Built by Devfer.");
> the Craft and Journey scenes were replaced by **Capabilities** and **Evolution** ("Still compiling.").
> Evolution content lives in `resources/js/content/evolution.ts`. Tokens in `resources/css/app.css` are the source of truth.

---

## 1. Principles

1. **Show the compile, don't decorate.** Every motion turns a design state into a built
   state, or rearranges the cells to explain something. If an animation explains nothing,
   it is cut.
2. **One loud thing per screen.** Each scene has a single memorable element (the name, the
   field, the index, the divider). Everything around it stays quiet: left-aligned, generous
   space, small type.
3. **The cells are the content.** The 200-cell field is the only graphic language. No
   illustrations, icons, stock imagery, blobs, or gradients.
4. **Mono means code.** Martian Mono only appears on things that are literally technical:
   technology names, versions, years in the timeline, values. Never as decorative eyebrow
   labels.
5. **Numbering only for sequences.** The career journey is numbered because it is a
   sequence. Nothing else is.
6. **Equal in both languages.** Every layout is tested with the longer of EN/ES. Spanish runs
   ~20–25% longer; display type must survive "Sectores en los que he trabajado".

---

## 2. Color

Light-only by design: the grain-on-mineral surface is the identity.

| Token | Hex | Role |
|---|---|---|
| `--mineral` | `#E4E6E3` | Page surface |
| `--paper` | `#F5F6F4` | Raised surface (menu overlay, divider panels) |
| `--ink` | `#15181D` | Text, built cells, focus ring |
| `--graphite` | `#5B6068` | Secondary text, wireframe strokes |
| `--hairline` | `#C9CCC8` | Rules, empty cells, grid lines |
| `--jade` | `#00A27A` | Signal: active cell, live state, selection. **Graphic use only** |
| `--jade-ink` | `#006B51` | Jade when it must carry text or a link underline |

Contrast (WCAG 2.1, on `--mineral`):
- `--ink` 14.9:1 · `--graphite` 5.0:1 · `--jade-ink` 5.2:1 — all pass AA for body text.
- `--jade` is 2.6:1 — never used for text or as the only signal of state; always paired with
  an ink label or shape change.

Rules
- Jade appears on at most one thing per viewport.
- Selection: `::selection { background: var(--ink); color: var(--mineral) }`.
- Grain: a 2–3% monochrome noise texture (static PNG, 128px tile, ~3 KB) over `--mineral`.
  No animated grain.

---

## 3. Typography

All three families are on Google Fonts, self-hosted through `laravel-vite-plugin/fonts` (metric-matched fallbacks,
subset Latin + Latin Extended for á é í ó ú ñ ¿ ¡).

| Role | Family | Why |
|---|---|---|
| Display | **Anybody** (variable: wght 100–900, wdth 50–150) | The width axis lets type *compile*: wireframe letters are narrow and thin, built letters are wide and heavy. The hero and stats animate this axis. |
| Text | **Instrument Sans** (variable: wght 400–700, wdth 75–100) | Calm, slightly condensed grotesk; good at small sizes, distinct from the display face. |
| Code | **Martian Mono** (variable: wght 300–600) | Only for technical values. Has its own width axis, kept fixed. |

### Scale (fluid, 360 → 1440px viewport)

| Token | Size | Line height | Tracking | Family / weight |
|---|---|---|---|---|
| `display-xxl` | `clamp(4.5rem, 17vw, 18rem)` | 0.82 | -0.04em | Anybody 800, wdth 120 |
| `display-xl` | `clamp(3rem, 9vw, 9rem)` | 0.88 | -0.035em | Anybody 700, wdth 110 |
| `display-l` | `clamp(2.25rem, 5vw, 4.5rem)` | 0.95 | -0.025em | Anybody 650, wdth 100 |
| `heading` | `clamp(1.5rem, 2.4vw, 2.25rem)` | 1.1 | -0.01em | Instrument Sans 600 |
| `lead` | `clamp(1.25rem, 1.8vw, 1.625rem)` | 1.35 | -0.005em | Instrument Sans 450 |
| `body` | `1.0625rem` (17px) | 1.55 | 0 | Instrument Sans 400 |
| `small` | `0.875rem` | 1.45 | 0.005em | Instrument Sans 500 |
| `code` | `0.8125rem` | 1.5 | 0 | Martian Mono 400 |

Rules
- Sentence case everywhere. No all-caps labels.
- Body measure: 38–68ch (`max-width: 62ch` default).
- No single-word accenting in headlines (no lone italic or jade word). Emphasis comes from
  scale and position.
- Display type is never justified; it hangs left and is allowed to bleed off the right edge
  on desktop (with `overflow-x: clip` on the section, never on `body`).

---

## 4. Layout grid

| Breakpoint | Columns | Gutter | Margin | Notes |
|---|---|---|---|---|
| `sm` < 640 | 4 | 12px | 16px | Mobile compositions are designed, not scaled |
| `md` 640–1023 | 8 | 16px | 32px | |
| `lg` 1024–1439 | 12 | 20px | 48px | |
| `xl` ≥ 1440 | 12 | 24px | 64px | Content max 1600px; display type may exceed it |

Composition rules
- Left alignment is the default. Body copy starts on column 1 or column 7 (`lg`), never
  centered.
- Asymmetry: each scene places its loud element and its quiet text on different column
  spans (e.g. display 1–12, text 8–12).
- Hairline grid lines (`--hairline`, 1px) are drawn only in the Field and Stack scenes,
  where the grid *is* the information.

---

## 5. Spacing

4px base, named by intent.

| Token | Value | Use |
|---|---|---|
| `space-1` | 4px | Cell gap (sm) |
| `space-2` | 8px | Inline gaps, cell gap (lg) |
| `space-3` | 12px | Label → value |
| `space-4` | 16px | Paragraph spacing |
| `space-6` | 24px | Component padding |
| `space-8` | 32px | Group spacing |
| `space-12` | 48px | Block spacing |
| `space-20` | 80px | Scene padding (sm) |
| `space-32` | 128px | Scene padding (lg) |
| `space-48` | 192px | Breathing room between loud scenes (lg) |

Radius: `0` everywhere except focus rings (`2px`) and the language switch thumb (`999px`).
Shadows: none. Depth comes from `--paper` vs `--mineral` and from overlap.

---

## 6. Information architecture

Eight requested sections combined into seven scenes; no projects section.

```
01 Hero ............ "Compile" — name builds from wireframe
02 Statement ....... who I am: designer + developer, Systems Engineer, the human side
03 The field ....... 11+ / 200+ scroll scene (cells → numerals)
04 Industries ...... sector index + cell pictograms
05 Craft ........... Design ⇄ Code capabilities  +  stack layers (expertise + tech merged)
06 Journey ......... 2016 → now, numbered timeline
07 Contact ......... "Easier than making coffee"
   Footer .......... email, profiles, language, CV
```

### Scene wireframes (desktop · mobile)

**01 Hero**
```
┌──────────────────────────────────────────────────────────────┐
│ FV▪                                   Menu      EN ● ES      │
│                                                              │
│ ┊F┊e┊r┊n┊a┊n┊d┊o┊                                            │
│ VÁSQUEZ███████████████████████  (bleeds right)               │
│                                                              │
│                          Senior fullstack developer and      │
│                          UX/UI designer. 11+ years designing │
│                          and building the web.        ▪▪▪▪▪▪ │
└──────────────────────────────────────────────────────────────┘
mobile: name stacks on 3 lines (Fer-nando / Vás-quez forced by
width axis, not hyphenation); intro below; no hover wireframe —
a single tap on the name replays the compile.
```

**03 The field** (pinned, ~250vh scroll)
```
┌───────────────────────────────┬──────────────────────────────┐
│ ▪▪▪▪▪▪▪▪▪▪▪  11 columns       │                              │
│ ▪▪▪▪▪▪▪▪▪▪▪  × rows           │  step 1: "Every square is a  │
│ ▪▪▪▪▪▪▪▪▪▪▪  = 200 cells      │   project."                  │
│ ...                           │  step 2: cells regroup → 200 │
│                               │  step 3: regroup → 11+       │
│        ███ ███ ███            │  "Years designing and        │
│          █ █ █ █ █            │   building for the web."     │
└───────────────────────────────┴──────────────────────────────┘
mobile: field full-width on top, captions below; same 3 steps,
shorter pin (~180vh). Caption states the field is illustrative.
* figures: 11+ years, 200+ projects (illustrative layout)
```

**04 Industries**
```
┌──────────────────────────────────────────────────────────────┐
│ Industries I've worked in                                    │
│                                                              │
│ Food service ─────────────────────────       ▪▪  ▪▪          │
│ Telecommunications                           ▪▪▪▪▪▪  ← cells │
│ Trade & export                               ▪▪▪▪▪▪   form a │
│ Employment platforms                          ▪▪▪▪   pictogram│
│ Agencies                                                     │
│                         What I built here, in one or two     │
│                         sentences. Role · years              │
└──────────────────────────────────────────────────────────────┘
mobile: one sector per screen in a vertical snap sequence;
pictogram above, name, description. Index dots on the edge.
```

**05 Craft**
```
Design ⇄ Code
┌─────────────────────────┃────────────────────────────────────┐
│ DESIGN  wireframe,      ┃ CODE   Laravel · Vue.js · MySQL     │
│ experience map          ┃ architecture, security, speed       │
└─────────────────────────┃────────────────────────────────────┘
                (drag / arrow keys)

Stack                     [ Website ] [ Web app ] [ Mobile app ] [ Infra ]
interface   HTML  CSS  Tailwind  Bootstrap  JavaScript  Vue.js  React
application PHP  Laravel  CodeIgniter  Node
data        MySQL  Oracle
infra       AWS  Linux  Git  …        ← selected solution lights its path
mobile: divider becomes a two-state toggle; stack layers become
an accordion, solution filter as a horizontal chip row.
```

**06 Journey** — horizontal scroll on `lg` (pinned), vertical list on `sm`/`md`.
```
01 2016–18 Lumation Services   02 2018–19 Grupo DIT   03 2019 Agexport …
   Jr. Developer                  Jr. Developer          Webmaster
```

**07 Contact**
```
Contacting me is easier
than making coffee.

hola@fernandovasquez.tech   [ Copy ]   [ Write an email ]
LinkedIn  GitHub  …          Download CV (EN/ES)
```

---

## 7. Components

| Component | Purpose | Key states | Accessibility |
|---|---|---|---|
| `SiteHeader` | Wordmark, menu trigger, language switch. Hides on scroll down, returns on scroll up. | default, condensed, menu-open | Skip link first in tab order; `nav` landmark |
| `MenuOverlay` | Full-screen scene index in display type on `--paper` | closed, opening, open | `dialog` with focus trap, Esc closes, returns focus |
| `LanguageSwitch` | EN ● ES toggle, links to the same page in the other locale | en, es, focus | Real `<a hreflang lang>` links, `aria-current` |
| `CompileText` | Display text that animates wdth/wght from wireframe → built | wireframe, compiling, built | Text is real DOM text; animation is CSS variable–driven; `aria-label` not needed |
| `CellField` | Canvas renderer for the 200 cells; morphs between layouts (grid, numeral, pictogram) | idle, morphing, static | `aria-hidden`; every state has an adjacent text equivalent |
| `IndustryIndex` | List of sectors driving `CellField` pictograms | rest, hover/focus, active | `button` list with `aria-controls` / `aria-expanded`; arrow keys move |
| `DesignCodeSplit` | Draggable divider between design and code views of a capability | 0–100% position | `role="slider"` with `aria-valuenow`, ← → keys, Home/End |
| `StackLayers` | Four layers of technologies with solution filter | none selected, filtered | Filter is a radio group; dimmed items keep 4.5:1 contrast |
| `JourneyTrack` | Ordered timeline of roles | — | Semantic `<ol>`; horizontal scroll is optional enhancement |
| `CopyEmail` | Copies email, confirms inline | idle, copied | Live region announces "Email copied" / "Correo copiado" |
| `Link` / `Button` | Text links with an animated underline that "compiles" from dashed to solid | default, hover, focus, visited | 2px ink focus ring, 3px offset |

Buttons: text + underline only (no pills, no rounded cards). Primary action = ink fill,
mineral text, 0 radius, 48px min height.

---

## 8. Motion language

Engine: **GSAP 3 + ScrollTrigger** for scroll scenes; CSS for micro-states; **Lenis** for
smooth scroll on pointer devices only (disabled for touch and reduced motion).

| Token | Value | Use |
|---|---|---|
| `dur-micro` | 160ms | Hover, focus, underline |
| `dur-ui` | 320ms | Menu, toggles, copy confirm |
| `dur-compile` | 1100ms | Hero and display-text compile |
| `dur-morph` | 900ms | Cell layout changes (non-scrubbed) |
| `ease-build` | `cubic-bezier(0.7, 0, 0.2, 1)` | Compile and morph (snappy, mechanical) |
| `ease-out` | `cubic-bezier(0.2, 0.8, 0.2, 1)` | UI responses |
| `stagger-cell` | 2–4ms per cell | Cell morphs (200 cells ≈ 0.6s spread) |

Motion inventory (complete list — nothing else animates)
1. Hero compile on load (once per session).
2. Hero letters revert to wireframe near the cursor (pointer devices only).
3. Field scene: cells → "200" → "11+" (scroll-scrubbed).
4. Industry pictogram morph on hover/focus/selection.
5. Design ⇄ Code divider (user-driven).
6. Stack path highlight on filter.
7. Journey horizontal scroll (scroll-scrubbed, `lg` only).
8. Link underline compile; menu open; copy confirmation.
9. Page language switch: cells sweep across the screen (≤ 600ms).

Reduced motion (`prefers-reduced-motion: reduce`)
- Everything renders in its final built state. Cell layouts switch instantly; the field
  scene becomes three static figures stacked vertically. No pinning, no Lenis, no cursor
  effects. Users can still drag the divider and filter the stack.

Performance budgets
- Canvas: 200 rects, single draw call per frame, paused when off-screen
  (`IntersectionObserver`) and when the tab is hidden.
- JS for motion loaded after first paint; hero text is server-rendered.
- Targets: LCP < 1.8s, CLS < 0.02, INP < 150ms on mid-range mobile.

---

## 9. Navigation

- Routes: `/en` (default; `/` redirects by `Accept-Language`, then cookie), `/es`.
  Single-page scenes with anchored sections; localized anchors (`#industries` /
  `#sectores`).
- Language choice persists in a `NEXT_LOCALE` cookie. Switching keeps the current scene.
- Header: wordmark (cell-built "FV") · Menu · EN/ES. The menu lists the seven scenes in
  display type; the current scene is marked with a jade cell.
- `hreflang` alternates for `en`, `es`, and `x-default` (→ `/en`) on every page; localized
  `title`, `description`, Open Graph, and JSON-LD `Person`.

---

## 10. Responsive behavior

| Concern | Desktop (`lg+`) | Tablet (`md`) | Mobile (`sm`) |
|---|---|---|---|
| Hero name | 2 lines, bleeds right, cursor wireframe | 2 lines, no bleed | 3–4 lines, tap to replay |
| Field | Pinned split: field left, captions right | Pinned, stacked | Short pin, captions below |
| Industries | Index + pictogram side by side | Same, smaller | Snap sequence, one sector per screen |
| Craft | Draggable divider | Draggable divider | Two-state toggle |
| Stack | Layer rows + filter | Layer rows | Accordion + chip row |
| Journey | Horizontal pinned track | Vertical list | Vertical list |
| Motion | Full inventory | No cursor effects | No cursor effects, simpler morphs, no Lenis |
| Touch targets | — | 44px min | 48px min |

---

## 11. Technical foundation

- **Laravel 13 + Inertia v3 + Vue 3 (TypeScript)**, server-side rendered for both locales.
  Routes in `routes/web.php` (`/en`, `/es`; `/` redirects by saved cookie or browser
  language). Meta tags, hreflang and JSON-LD are rendered in `resources/views/app.blade.php`.
- **Tailwind CSS v4** with the tokens above defined in `@theme` (`resources/css/app.css`).
- Messages in `resources/messages/en.json` and `resources/messages/es.json`, sent to Vue
  as a page prop and read with `useI18n()`; evolution copy in
  `resources/js/content/evolution.ts`.
- **GSAP**, **Lenis**; canvas 2D for `CellField` (no Three.js needed).
- Fonts self-hosted from Google Fonts by `laravel-vite-plugin/fonts`; OG images rendered per
  locale with PHP GD (`app/Support/OgImage.php`), sitemap and robots from `SeoController`.

---

## 12. Content decisions (confirmed)

1. **Industries:** only the five verified sectors: food service / quick-service restaurants,
   telecommunications, trade & export, employment platforms, digital agencies
   (multi-client). Industries is a main visual scene.
2. **No client showcase.** Work is described by sector. The Journey scene lists employers and
   roles as career facts; the McDonald's Mesoamérica work is described as "a quick-service
   restaurant group across Mesoamérica" without the brand name.
3. **Figures:** 11+ years of professional experience, 200+ projects. The Field caption
   reads "11+ years" without a start year.
4. **Profiles & location:** none on the current site, so none on the new one. No social
   links and no location are shown.
5. **5+ countries:** kept. It is stated on the current site ("5+ países alcanzados").
6. **Mobile:** "Mobile apps" appears as a solution type (verified service) built with the
   verified stack only. No mobile-specific framework is named.
7. **Contact:** hola@fernandovasquez.tech, with copy-to-clipboard and "Write an email"
   (mailto). No form backend. The existing CV PDF is **not** linked: it says 9+ years and
   includes a personal phone, address, and references.
