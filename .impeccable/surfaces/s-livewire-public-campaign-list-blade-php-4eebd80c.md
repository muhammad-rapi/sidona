---
version: 1
slug: "s-livewire-public-campaign-list-blade-php-4eebd80c"
primary_target: "resources/views/livewire/public/campaign-list.blade.php"
related_targets: ["resources/views/components/layouts/public.blade.php","resources/views/components/layouts/app.blade.php"]
---

# Surface brief: SIDONA public + staff panel

Scope and mode: public site (program list, program detail, payment, receipt, status check) is Persuade. Staff panel (sidebar shell, dashboard, tables, forms, audit, reports) is Operate and inherits the same world in restrained form.

Audience: Indonesian public giving on phones; staff on desktop. Action: donate in seconds, no human approval; staff run the ledger and audit.

## Direction contract

THESIS: A donation drive IS the roadside "papan target sumbangan": a painted board with a giant thermometer, not a crowdfunding card grid. Refuses the rounded-card-grid + green donate button category default.
OWN-WORLD: Sign-painter's enamel: saffron-yellow board ground (#FFC61A), lampblack ink (#17130A) frame and lettering, paint-red mercury and primary action (#D42A1F), signal-green only for "paid/success" (#0E7A3B). Heavy black rules, square corners, painted condensed capitals (Big Shoulders Display), grotesk body (Hanken Grotesk), tick-mark thermometer scale. Staff panel: lampblack sidebar with saffron active marker, neutral warm-gray workspace, white ledger tables, same red/green/yellow state colours.
STORY: Visitor sees a real program with the mercury level, understands in one glance how close it is to target, taps a nominal chip, pays by simulated QRIS, and watches the mercury rise on the receipt page. Believes: money is counted, every rupiah is on an auditable ledger.
FIRST VIEWPORT: Yellow board fills the viewport under a slim black navbar. Left ~60%: giant painted headline "SUMBANGAN" over featured program name and amount raised in huge numerals with target line; right: a tall thermometer (tick scale every 10%, red mercury, bulb) at hero scale. Primary action "DONASI SEKARANG" red slab button directly under the amount. Other active programs follow as a ruled list of boards with horizontal thermometers, not equal cards.
FORM: Painted roadside donation board with thermometer; position 1 on my list (IMPECCABLE'S PICK); seed key 41185e32.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance

Signature interaction: on the receipt page the mercury animates from the previous level to the new level including the donor's contribution (one authored motion, respects reduced motion).
Unresolved: real payment gateway (simulator for now); real cover photography supplied by program staff.
