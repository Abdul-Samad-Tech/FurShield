# Design System Master File

> **LOGIC:** When building a page, check `design-system/pages/[page-name].md` first.
> If that file exists, its rules override this file. Otherwise follow this file.

**Project:** FurShield
**Generated:** 2026-11-10
**Category:** Pet-care platform for owners, veterinarians, and shelters

## Decision

UI/UX Pro Max recommended an App Store landing, a vibrant block style, sky blue (`#0369A1`), green CTA (`#22C55E`), and Varela Round + Nunito Sans.

That recommendation is not adopted. It reads as a generic children’s pet app, which the generator itself flags as “generic design.” FurShield is already calm, trustworthy, warm, and premium. The shipped brand stays:

| Role | Token | Value |
|------|--------|--------|
| Primary | `--fs-tide` | `#0E7C74` |
| Primary bright | `--fs-tide-bright` | `#19A89B` |
| Primary deep | `--fs-tide-deep` | `#0A5C56` |
| Accent | `--fs-bloom` | `#E8A87C` |
| Accent soft | `--fs-bloom-soft` | `#F6D7C0` |
| Display | `--fs-font-display` | Syne |
| Body | `--fs-font-sans` | Outfit |

White text on `#0E7C74` is about 5:1. Muted text `#5B737C` on `#F3FAF8` is about 4.7:1. Peach `#E8A87C` is an accent fill only. It is too light for small text on a light surface, so warning text uses a darker brown.

Mood: quiet clinical warmth. One or two motions per view. No bounce, no elastic, no linear UI motion.

## Color

Channels are space-separated RGB so Tailwind opacity modifiers work (`bg-surface/80`).

### Light (`:root`)

| Token | Channels | Hex | Use |
|-------|----------|-----|-----|
| `--fs-surface` | `243 250 248` | `#F3FAF8` | Page background |
| `--fs-surface-raised` | `255 255 255` | `#FFFFFF` | Cards, dialogs |
| `--fs-surface-sunken` | `215 238 232` | `#D7EEE8` | Inset wells |
| `--fs-text` | `16 32 39` | `#102027` | Primary text |
| `--fs-text-muted` | `91 115 124` | `#5B737C` | Secondary text |
| `--fs-text-inverse` | `243 250 248` | `#F3FAF8` | Text on dark fills |
| `--fs-border` | `215 238 232` | `#D7EEE8` | Hairline |
| `--fs-border-strong` | `183 212 206` | `#B7D4CE` | Emphasized edge |
| `--fs-success` | `14 124 116` | `#0E7C74` | Success text and icon |
| `--fs-success-soft` | `215 238 232` | `#D7EEE8` | Success background |
| `--fs-warning` | `154 84 22` | `#9A5416` | Warning text and icon |
| `--fs-warning-soft` | `246 215 192` | `#F6D7C0` | Warning background |
| `--fs-danger` | `180 35 24` | `#B42318` | Danger text and icon |
| `--fs-danger-soft` | `252 232 230` | `#FCE8E6` | Danger background |
| `--fs-focus` | `14 124 116` | `#0E7C74` | Focus ring |

### Dark (`.dark`)

| Token | Channels | Hex |
|-------|----------|-----|
| `--fs-surface` | `7 24 28` | `#07181C` |
| `--fs-surface-raised` | `16 32 39` | `#102027` |
| `--fs-surface-sunken` | `10 40 38` | `#0A2826` |
| `--fs-text` | `243 250 248` | `#F3FAF8` |
| `--fs-text-muted` | `197 213 210` | `#C5D5D2` |
| `--fs-text-inverse` | `16 32 39` | `#102027` |
| `--fs-border` | `46 74 72` | `#2E4A48` |
| `--fs-border-strong` | `125 222 212` | `#7DDED4` |
| `--fs-success` | `60 187 176` | `#3CBBB0` |
| `--fs-success-soft` | `14 58 54` | `#0E3A36` |
| `--fs-warning` | `232 168 124` | `#E8A87C` |
| `--fs-warning-soft` | `58 36 22` | `#3A2416` |
| `--fs-danger` | `244 160 152` | `#F4A098` |
| `--fs-danger-soft` | `62 24 22` | `#3E1816` |
| `--fs-focus` | `125 222 212` | `#7DDED4` |

Brand utilities that already exist stay as they are: `ink`, `mist`, `tide`, `bloom`, `shadow-soft`, `shadow-glow`. New work uses the semantic names below.

## Spacing

| Token | Value |
|-------|--------|
| `--fs-space-3xs` | `0.25rem` |
| `--fs-space-2xs` | `0.5rem` |
| `--fs-space-xs` | `0.75rem` |
| `--fs-space-sm` | `1rem` |
| `--fs-space-md` | `1.5rem` |
| `--fs-space-lg` | `2rem` |
| `--fs-space-xl` | `3rem` |
| `--fs-space-2xl` | `4rem` |
| `--fs-space-3xl` | `6rem` |

## Radius

| Token | Value | Use |
|-------|--------|-----|
| `--fs-radius-xs` | `0.5rem` | Chips |
| `--fs-radius-sm` | `0.75rem` | Compact controls |
| `--fs-radius-md` | `1rem` | Buttons and inputs |
| `--fs-radius-lg` | `1.75rem` | Cards |
| `--fs-radius-xl` | `999px` | Pills |

## Shadow

| Token | Light |
|-------|--------|
| `--fs-shadow-xs` | `0 1px 2px rgba(16, 32, 39, 0.06)` |
| `--fs-shadow-sm` | `0 4px 12px rgba(16, 32, 39, 0.06)` |
| `--fs-shadow-md` | `0 12px 40px rgba(16, 32, 39, 0.08)` |
| `--fs-shadow-lg` | `0 20px 60px rgba(14, 124, 116, 0.18)` |

Dark mode deepens `--fs-shadow-md` and `--fs-shadow-lg` with black at 0.35 and 0.45 alpha. No colored glow on dark surfaces.

## Motion

Durations and easings only. No bounce, no elastic, no overshoot.

| Token | Value | Use |
|-------|--------|-----|
| `--fs-duration-fast` | `150ms` | Hover color, focus |
| `--fs-duration-base` | `300ms` | Panels, buttons |
| `--fs-duration-slow` | `500ms` | Large reveals, ceiling |
| `--fs-ease-standard` | `cubic-bezier(0.2, 0, 0, 1)` | State changes |
| `--fs-ease-enter` | `cubic-bezier(0.16, 1, 0.3, 1)` | Elements entering |
| `--fs-ease-exit` | `cubic-bezier(0.4, 0, 1, 1)` | Elements leaving |

`prefers-reduced-motion: reduce` sets all three durations to `0ms`.

## Tailwind names

Use these utilities. They read the variables above.

- Surface: `bg-surface`, `bg-surface-raised`, `bg-surface-sunken`
- Text: `text-text`, `text-text-muted`, `text-text-inverse`
- Border: `border-border`, `border-border-strong`
- Status: `text-success`, `bg-success-soft`, `text-warning`, `bg-warning-soft`, `text-danger`, `bg-danger-soft`
- Focus: `ring-focus`, `border-focus`
- Space: `p-fs-3xs` through `p-fs-3xl` (`gap-`, `m-` the same way)
- Radius: `rounded-fs-xs`, `rounded-fs-sm`, `rounded-fs-md`, `rounded-fs-lg`, `rounded-fs-xl`
- Shadow: `shadow-fs-xs`, `shadow-fs-sm`, `shadow-fs-md`, `shadow-fs-lg`
- Motion: `duration-fs-fast`, `duration-fs-base`, `duration-fs-slow`, `ease-fs-standard`, `ease-fs-enter`, `ease-fs-exit`

Do not replace `rounded-md`, `shadow-sm`, or `duration-300` in existing screens. Those names already belong to Tailwind defaults.
