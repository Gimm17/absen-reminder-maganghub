---
name: Precision Presence
colors:
  surface: '#faf8ff'
  surface-dim: '#d2d9f4'
  surface-bright: '#faf8ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f3ff'
  surface-container: '#eaedff'
  surface-container-high: '#e2e7ff'
  surface-container-highest: '#dae2fd'
  on-surface: '#131b2e'
  on-surface-variant: '#434655'
  inverse-surface: '#283044'
  inverse-on-surface: '#eef0ff'
  outline: '#747686'
  outline-variant: '#c4c5d7'
  surface-tint: '#2151da'
  primary: '#0037b0'
  on-primary: '#ffffff'
  primary-container: '#1d4ed8'
  on-primary-container: '#cad3ff'
  inverse-primary: '#b7c4ff'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#623c00'
  on-tertiary: '#ffffff'
  tertiary-container: '#825100'
  on-tertiary-container: '#ffcb8f'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dce1ff'
  primary-fixed-dim: '#b7c4ff'
  on-primary-fixed: '#001551'
  on-primary-fixed-variant: '#0039b5'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#faf8ff'
  on-background: '#131b2e'
  surface-variant: '#dae2fd'
typography:
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 2rem
    fontWeight: '700'
    lineHeight: 2.5rem
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.5rem
    fontWeight: '700'
    lineHeight: 2rem
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.25rem
    fontWeight: '600'
    lineHeight: 1.75rem
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 1.125rem
    fontWeight: '600'
    lineHeight: 1.5rem
  body-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 1rem
    fontWeight: '400'
    lineHeight: 1.6rem
  body-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.875rem
    fontWeight: '400'
    lineHeight: 1.45rem
  body-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.8125rem
    fontWeight: '400'
    lineHeight: 1.3rem
  label-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.875rem
    fontWeight: '600'
    lineHeight: 1.25rem
  label-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.75rem
    fontWeight: '600'
    lineHeight: 1rem
  label-xs:
    fontFamily: Plus Jakarta Sans
    fontSize: 0.6875rem
    fontWeight: '700'
    lineHeight: 0.875rem
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

The brand personality is dependable, crisp, reassuring, and modern. Targeted at Indonesian interns navigating corporate routines, it replaces anxiety around missing daily presence deadlines with tranquil, automated assurance. The design style moves completely away from default template aesthetics and garish AI gradients, embracing a refined **Modern Utility** framework characterized by:

- Pure structural balance inspired by high-end productivity platforms (Linear, Raycast).
- A soothing yet authoritative visual tone that elevates mundane utility into an effortless everyday habit.
- Polished micro-interactions, distinct state pills, subtle hairline borders, and clear spatial separation between high-priority live indicators and contextual steps.

## Colors

The palette establishes crystalline contrast and purposeful status communication:

- **Primary (`#1D4ED8`)**: Electric Sapphire / Deep Royal Blue. Anchors the primary interactive targets, navigation accents, active timeline indicators, and brand crest.
- **Secondary (`#10B981`)**: Mint / Emerald Success. Communicates completed presence status, active notification checkmarks, and verified state badges. Paired with soft tint `#ECFDF5` for calm, confident feedback.
- **Tertiary (`#F59E0B`)**: Amber / Sunburst. Reserved for alert counters, urgent countdowns approaching midnight cutoff, and tips.
- **Neutrals**: Grounded by `#0F172A` (Slate 900) for headline clarity, `#475569` (Slate 600) for secondary details, `#E2E8F0` for precision hairline borders, and `#F8FAFC` for page canvases beneath crisp `#FFFFFF` cards.

## Typography

The type hierarchy relies entirely on **Plus Jakarta Sans**, chosen for its geometric precision, open counters, and human warmth. 

- Numeric time stamps, WITA time tags, and countdown counters use tabular numbers (`font-variant-numeric: tabular-nums`) to prevent jitter during real-time updates.
- Section titles leverage semi-bold to bold weights with tight negative tracking (`-0.02em`) to produce a deliberate, premium editorial appearance.
- Micro-labels, step badges, and status indicators strictly employ medium or semi-bold weights for high legibility even at tiny dimensions on low-resolution mobile viewports.

## Layout & Spacing

The layout is structured around a single-column, distraction-free container optimized for both quick mobile app-like interactions (as a PWA) and clean desktop centered utility:

- **Max Layout Boundary**: Fixed centered shell capped at `540px` for mobile web focus, expandable to a two-column responsive split (`max-w-4xl` / `896px`) on larger viewports where the primary status card sits left and the procedural guide sits right.
- **Rhythm**: Compact vertical cadence governed by an 8pt baseline scale. Gaps between related list items use `space-sm` (`0.5rem`), card interior padding relies on `space-lg` (`1.5rem`), and modular cards separate cleanly by `space-md` to `space-lg`.
- **Safe Area**: Mobile margins lock at `1rem` edge distance with safe-bottom padding to account for mobile web browser navigational chrome and PWA dock overlays.

## Elevation & Depth

This system avoids heavy shadows, dark drop bevels, and artificial skeuomorphism. Depth is produced purely through a layered surface hierarchy paired with crisp hairline borders:

- **Base Layer (`Level 0`)**: App canvas tinted at `#F8FAFC`.
- **Card Surface (`Level 1`)**: Pure white `#FFFFFF` wrapped with a `1px` stroke of `#E2E8F0` and an ultra-subtle ambient shadow: `0 1px 3px 0 rgba(15, 23, 42, 0.04), 0 1px 2px -1px rgba(15, 23, 42, 0.04)`.
- **Floating Status Callouts & Overlays (`Level 2`)**: Used for confirmation badges, tooltips, and interactive time chips: `0 4px 6px -1px rgba(15, 23, 42, 0.06), 0 2px 4px -2px rgba(15, 23, 42, 0.04)` over soft tinted backgrounds (`#F1F5F9` or `#ECFDF5`).
- **Interactive States**: Hovering on primary cards applies a faint elevated translation (`translate-y-[-1px]`) and transitions border color from `#E2E8F0` to `#CBD5E1`.

## Shapes

The design uses a balanced `roundedness: 2` (base `0.5rem` / `8px` corner radius) across standard components, with larger structural containers taking `rounded-2xl` (`1rem` / `16px`).

- **Pill Badges**: Status indicators (e.g., "Sudah Absen", "Aktif", "WITA") use full pill radius (`rounded-full`) for quick visual scanning.
- **Card Containers**: Large cards utilize `rounded-2xl` (`16px`) with subtle hairline clipping to yield an approachable, soft software finish.
- **Interactive Inputs and Step Badges**: Step numbers and toggle icons adopt `rounded-lg` (`8px`) or `rounded-full` proportional circle bounds.

## Components

### 1. Header & Navigation Banner
- Replaces the plain blue bar with a clean, branded frosted header.
- Displays the app logo/icon alongside an ambient live status pill ("3x Pengingat Aktif").
- Includes a subtle timezone tracker indicator ("Zona Waktu: WITA").

### 2. Live Status Banner & Countdown
- **Card State (Completed)**: `#ECFDF5` emerald tint, fine `#A7F3D0` border, featuring a pulsing emerald indicator dot, bold "Kamu sudah absen hari ini" copy, accompanied by a dynamic time pill ("Tercatat 08:14 WITA") and an inline subtle ghost refresh button.
- **Card State (Pending)**: Warm `#FFFBEB` amber tint with a live countdown chip ("Tersisa 3 jam 15 mnt sebelum 00.00 WITA").

### 3. Interactive Reminder Schedule (Timeline Grid)
- Replaces simple bullet points with structured mini-cards or a vertical micro-timeline.
- Each schedule slot features:
  - Time badge (`16.30 WITA`, `20.30 WITA`, `23.00 WITA`) in monospaced or tabular bold styling.
  - Contextual label (Sore / Malam / Peringatan Terakhir).
  - Status indicator (Sent, Next, Upcoming).

### 4. Primary & Secondary CTA Buttons
- **Primary Action ("Buka Dashboard MagangHub")**:
  - Full-width or auto-stretched, styled in `#1D4ED8` with crisp white bold text, subtle shadow, and an external link arrow icon (`↗`).
  - Active state: `#1E40AF` with slight scale down (`scale-[0.99]`).
- **Secondary Action ("Daftarkan / Ganti Email")**:
  - Bordered ghost pill button: `#FFFFFF` fill, `#E2E8F0` border, `#475569` typography, hovering to `#F8FAFC`.

### 5. Step Tracker / "Cara Kerja" Micro-Cards
- Replaces raw numbered paragraphs with a 3-step vertical or horizontal grid of micro-cards.
- Each step features a numeric badge (e.g., `01`, `02`, `03` in light sapphire circle badges), concise bold micro-titles, and muted descriptive subtext.
- Includes a dedicated "PWA Install / Add to Home screen" callout box styled with a warm lightbulb accent badge.

### 6. Minimal Footer
- Center-aligned or flex-spaced, containing subtle muted links, copyright, and critical deadline alert: *"Batas absen: tengah malam (00.00 WITA)"* formatted with an amber clock glyph.