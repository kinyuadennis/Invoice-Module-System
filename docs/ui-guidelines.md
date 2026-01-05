# UI Guidelines & Design System

## Overview
InvoiceHub uses a utility-first approach with Tailwind CSS v4. The design system focuses on professionalism, trust, and clarity, catering to business users.

## Colors

### Primary Palette
Used for main actions, navigation, and brand identity.
- **Deep Royal Blue**: `#1e3a8a` (Trust, Stability)
    - RGB: `30, 58, 138`
    - Variables: `--color-primary`, `--color-primary-50` to `700`

### Accent Palette
Used for success states, call-to-actions, and positive financial indicators.
- **M-PESA Green**: `#39B54A` (Action, Payment)
    - Variables: `--color-accent`, `--color-accent-600`

### Neutral Palette
Used for text, backgrounds, and borders.
- Slate/Gray scale from `#F9FAFB` (50) to `#0F172A` (900).
- Variable: `--color-neutral-*`

## Typography
- **Font Family**: `Inter`, system-ui, sans-serif.
- **Scale**:
    - `xs`: 0.75rem (12px) - Hints, meta data.
    - `sm`: 0.875rem (14px) - Body text, table data.
    - `base`: 1rem (16px) - Primary inputs, standard text.
    - `lg` to `3xl`: Headings.

## Spacing & Layout
- **Container**: Max width `1200px` (`--container-b2b`).
- **Spacing**: 4px baseline.
    - `--spacing-2`: 8px
    - `--spacing-4`: 16px (Standard padding)
    - `--spacing-6`: 24px (Section gap)
- **Shadows**: Soft elevation system (`--shadow-sm` to `--shadow-lg`).

## Transitions
- **Standard**: `150ms cubic-bezier(0.4, 0, 0.2, 1)` for hover effects.
- **Micro-interactions**: Scale transforms on buttons, fade-ins on modals.
- **Reduced Motion**: All animations disabled via `@media (prefers-reduced-motion)`.
