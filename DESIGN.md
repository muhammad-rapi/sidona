---
name: SIDONA
description: A roadside donation board in sign-painter's enamel, with a thermometer that shows how close the drive is to its target.
colors:
  board: "#ffc61a"
  board-deep: "#f0ab00"
  board-wash: "#fff0b8"
  ink: "#17130a"
  ink-soft: "#4d4430"
  ink-faint: "#756b54"
  paint: "#d42a1f"
  paint-dark: "#a81d14"
  paint-wash: "#fde4e1"
  paid: "#0e7a3b"
  paid-wash: "#daf2e2"
  paper: "#ffffff"
  desk: "#f1f0eb"
  rule: "#d8d4c4"
typography:
  display:
    fontFamily: "Big Shoulders Display, Arial Narrow, ui-sans-serif, sans-serif"
    fontSize: "clamp(3.25rem, 11vw, 7rem)"
    fontWeight: 800
    lineHeight: 0.9
    letterSpacing: "-0.01em"
  headline:
    fontFamily: "Big Shoulders Display, Arial Narrow, ui-sans-serif, sans-serif"
    fontSize: "2.25rem"
    fontWeight: 800
    lineHeight: 1
    letterSpacing: "-0.025em"
  body:
    fontFamily: "Hanken Grotesk, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Hanken Grotesk, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 700
    letterSpacing: "0.05em"
rounded:
  none: "0"
  tube-cap: "1.4rem"
  bulb: "9999px"
spacing:
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "48px"
components:
  button-paint:
    backgroundColor: "{colors.paint}"
    textColor: "{colors.paper}"
    rounded: "{rounded.none}"
    padding: "10px 20px"
    height: "44px"
  button-paint-hover:
    backgroundColor: "{colors.paint-dark}"
  button-ink:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.board}"
    rounded: "{rounded.none}"
    padding: "10px 20px"
  button-line:
    backgroundColor: "transparent"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
  field:
    backgroundColor: "{colors.paper}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    padding: "12px 14px"
  badge-paid:
    backgroundColor: "{colors.paid-wash}"
    textColor: "{colors.paid}"
    rounded: "{rounded.none}"
    padding: "2px 8px"
  badge-fail:
    backgroundColor: "{colors.paint-wash}"
    textColor: "{colors.paint-dark}"
  panel:
    backgroundColor: "{colors.paper}"
    rounded: "{rounded.none}"
---

# Design System: SIDONA

## Overview

**Creative North Star: "The Roadside Donation Board"**

A donation drive is a painted board with a giant thermometer, not a grid of rounded crowdfunding cards. The public site is the board itself: saffron ground, lampblack lettering and frame, paint-red mercury and primary action. Corners are square, rules are heavy, and headlines are condensed painted capitals. The staff panel inherits the same materials in a restrained form: a lampblack sidebar with a saffron active marker, a warm desk-gray workspace, and white ruled ledger tables.

Color is read as signage. Saffron is the surface, black is structure and text, red is the single call to act and the thermometer's mercury, and green appears only to mean paid or succeeded. Density is high-contrast and chunky on the public side (display numerals up to 8rem-ish) and tight and tabular on the staff side.

**Key Characteristics:**
- Square corners everywhere except the thermometer's tube cap and bulb.
- 2px to 4px black rules as the main structural device; no soft shadows.
- Painted capitals (Big Shoulders Display, uppercase, 800) for headlines and numerals; grotesk for everything read at length.
- Tabular numerals globally, so amounts line up.
- One authored motion: the mercury rising.

## Colors

A saffron-and-lampblack sign palette with one red and one green, each with a single job.

### Primary
- **Paint Red** (`{colors.paint}`): the primary action slab, the thermometer mercury and bulb, the raised-amount numeral, focus outlines, input caret and accent. Darkens to Paint Red Deep (`{colors.paint-dark}`) on hover and for error text; Paint Wash (`{colors.paint-wash}`) is the failed/error tint.

### Secondary
- **Board Saffron** (`{colors.board}`): the public hero ground, the navbar and footer rules, the active sidebar marker, and text on lampblack. Board Deep (`{colors.board-deep}`) is the darker saffron; Board Wash (`{colors.board-wash}`) tints waiting badges and table row hover.
- **Signal Green** (`{colors.paid}`): paid and success states only (badge, flash). Paid Wash (`{colors.paid-wash}`) is its tint.

### Neutral
- **Lampblack** (`{colors.ink}`): text, borders, the sidebar and navbar, the thermometer frame. Ink Soft (`{colors.ink-soft}`) for secondary text and table headers; Ink Faint (`{colors.ink-faint}`) for placeholders and the scrollbar.
- **Paper** (`{colors.paper}`): body ground, panels, tables, fields.
- **Desk Gray** (`{colors.desk}`): staff workspace background.
- **Rule** (`{colors.rule}`): hairline dividers inside staff panels and tables.

### Named Rules
**The Single Red Rule.** Red marks the one thing to act on or the progress itself (donate button, mercury). It is not used as decoration.
**The Green Means Paid Rule.** Signal green appears only for paid or success. It is never a brand accent or a donate button.
**The Ink-On-Board Rule.** On saffron, text and focus rings are lampblack; on lampblack, text is saffron or white and focus rings are saffron.

## Typography

**Display Font:** Big Shoulders Display (with Arial Narrow fallback), loaded from Google Fonts at 700/800/900
**Body Font:** Hanken Grotesk (with system sans fallback), loaded at 400 to 700

**Character:** A sign-painter's condensed capitals set against a plain, legible grotesk. The display face carries headlines and money; the grotesk carries labels, forms, and prose.

### Hierarchy
- **Display** (800, clamp(3.25rem, 11vw, 7rem), 0.9): the hero headline; featured program name scales from 2.6rem to 4.5rem, the raised amount up to 6rem.
- **Headline** (800, 2.25rem, 1): staff page titles and stat values, uppercase, tight tracking.
- **Title** (800 display, 1.875rem to 2.25rem): public section and program titles, painted capitals.
- **Body** (400, 1rem, relaxed): prose; `max-w-prose` for staff sub-copy.
- **Label** (700, 0.75rem, 0.05em tracking, uppercase): table headers, stat labels, badges, buttons (0.875rem); nav headings in the sidebar go to 0.7rem and 0.18em.

### Named Rules
**The Painted Capitals Rule.** Uppercase condensed display type is for headlines, big numerals, and the logotype. Body copy and form text stay in the grotesk, sentence case.
**The Tabular Rule.** Numerals are tabular everywhere; money columns are right-aligned.

## Layout

Public pages sit in a `max-w-6xl` container with 20px side padding. The first viewport is a full-width saffron band: headline and amount on the left, tall thermometer on the right in a two-column grid that stays two-column on phones with a smaller thermometer. Other programs follow as a ruled list of boards with horizontal thermometers rather than equal cards. Sticky 64px navbar with a 4px saffron bottom rule; footer mirrors it as a lampblack block.

The staff panel is a fixed 16rem (`lg:pl-64`) lampblack sidebar with a sticky 56px top bar and drawer on small screens; the workspace is `max-w-6xl`, padded 16px to 32px, on desk gray. Spacing follows Tailwind's 4px scale; section rhythm is 24px between page head and content, 40px to 64px vertical padding on public bands.

## Elevation & Depth

Flat. Depth comes from heavy black borders, tonal bands (saffron against white, lampblack against desk gray), and the 4px saffron rules on the shell. No box shadows are used.

### Named Rules
**The Flat Enamel Rule.** Surfaces are flat paint. State is shown by color inversion, border, and outline, never by shadow or blur.

## Shapes

Square by default (0 radius) on buttons, fields, panels, badges, flash banners, and tables. The only curves are the thermometer's tube cap (1.4rem) and its bulb (full circle), because they depict a physical object. Borders are 2px on interactive elements (buttons, fields, flashes), 1px rule-colored on staff panels, 3 to 4px on thermometers and shell rules. A hazard stripe (lampblack and saffron diagonal, 12px tall) serves as a board divider.

## Components

### Buttons
- **Shape:** square, 2px lampblack border, 44px minimum height, uppercase bold with wide tracking.
- **Primary (Paint):** red fill, white text; darkens to Paint Red Deep on hover. The only donate action style.
- **Ink:** lampblack fill, saffron text, softens to Ink Soft on hover. **Line:** transparent with ink text; inverts to ink fill and saffron text on hover.
- **Sizes:** small (36px, 0.75rem) and large (56px, Big Shoulders at 1.125rem, 0.06em tracking) for the hero action.
- **States:** 1px press-down on active; 150ms color transition; 50% opacity when disabled; 3px red focus ring with 2px offset (ink on saffron, saffron on lampblack).

### Inputs / Fields
- **Style:** white, 2px lampblack border, square, 14px/12px padding, ink-faint placeholder.
- **Focus:** 3px red outline at 0 offset. **Error:** border turns paint red with semibold Paint Red Deep message below.

### Badges and Flashes
- Small bordered square tags: paid (green on green wash), waiting (ink on saffron wash, faint-ink border), failed (red on red wash), ink (saffron on lampblack). Flash banners use the same green and red pairs with a 2px border.

### Navigation
- **Public:** lampblack navbar, saffron logotype with a miniature thermometer mark, uppercase bold links that turn saffron on hover and when current.
- **Staff sidebar:** lampblack, links in 75% white that brighten on hover; the current page gets a 4px saffron left bar, a 10% white wash, and saffron text. Group headings are small, tracked, uppercase at 45% white. Icons are inline SVG line icons.

### Ledger Tables and Panels
- White panels with a 1px rule border. Table header has a 2px lampblack bottom rule and small uppercase ink-soft labels; rows have hairline rules and a faint saffron-wash hover; numeric columns are right-aligned.

### Thermometer (signature)
A vertical tube (2.75rem wide, 4px lampblack border, rounded cap, tick marks every 10% in faded ink) filled with red mercury, a round red bulb at the base, and a labeled scale at 0, 25, 50, 75, 100% in display type. A horizontal variant (1.5rem tall, 3px border) is used in program lists. Mercury is a scaled fill driven by a `--level` variable; on the receipt page it animates from the previous level to the new one (1.6s vertical, 1.1s horizontal, exponential ease-out), the system's one authored motion. It exposes an accessible percent label and respects reduced motion. Page content also fades up 14px over 0.7s on entry (`rise-in`).

## Do's and Don'ts

### Do:
- **Do** use red only for the primary action and the mercury, green only for paid or success, and saffron as the surface.
- **Do** keep corners square and borders heavy (2px to 4px lampblack); use the thermometer to show progress rather than a plain bar or ring.
- **Do** set headlines and large numerals in uppercase Big Shoulders Display and everything else in Hanken Grotesk.
- **Do** keep staff screens on desk gray with white ledger panels and the lampblack sidebar, in the same red, green, and saffron state colors.
- **Do** wrap saffron and lampblack areas in the ink or board focus-ring overrides so the focus outline stays visible.

### Don't:
- **Don't** use rounded-card grids or a green donate button; this world refuses the crowdfunding default.
- **Don't** use shadows, blur, or gradients for depth; the only gradients are thermometer tick marks and the hazard stripe.
- **Don't** use green for anything but paid or success states.
- **Don't** use saffron text on white, or white text on saffron; contrast fails.

<!-- Not canonized: the small uppercase tracked label set above headlines in the public hero (program owner line) and the repeated "Terkumpul" tag read as kickers. The build carries them; new surfaces should not extend them as a pattern. -->
