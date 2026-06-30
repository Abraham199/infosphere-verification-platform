# IVP UI Design System

## Purpose

Sprint 1 introduces a reusable Blade and Bootstrap-based design system for IVP. The goal is to give all product surfaces a consistent enterprise SaaS experience without moving business logic into views.

## Stack

- Laravel Blade templates
- Blade components under `resources/views/components`
- Bootstrap 5 and Font Awesome through Vite
- Sass design tokens in `resources/css/app.scss`

## Visual Principles

- Clean operational dashboards over marketing-style pages
- Responsive-first layouts for desktop, tablet, and mobile
- 8px-or-smaller card radius through the shared `--ivp-radius` token
- Dark-mode-ready color tokens
- Strong accessible focus states
- No page-specific styling that bypasses the design system

## Palette

- Ink: `#071827`
- Platform blue: `#145da0`
- Signal cyan: `#19b6d2`
- Success green: `#16875f`
- Warning gold: `#b7791f`
- Risk red: `#b42318`
- Background: `#f6f8fb`
- Border: `#d9e2ec`

## Layouts

- `layouts.guest` for authentication and public screens
- `layouts.platform` for Super Admin platform administration
- `layouts.tenant` for tenant and customer workspaces

## Rule

Views may display data that controllers provide. They must not perform business decisions, wallet operations, verification execution, provider calls, or financial posting.
