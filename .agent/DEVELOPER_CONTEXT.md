# WESTHUB HEALTHCARE: DEVELOPER AGENT CONTEXT

This document is the "Source of Truth" for any AI agent working on the WestHub Healthcare platform. It ensures consistency across sessions and adherence to the Master Plan.

## 1. PROJECT MISSION & PILLARS
WestHub Healthcare is an Illinois-licensed Home Healthcare Agency (CHAP certified).
- **Core Goal 1:** Build trust for In-Home Care & Nursing.
- **Core Goal 2:** Lead generation (Enquiries, Bookings, Job Applications).
- **Core Goal 3:** Secure Internal Admin Panel.

## 2. STRICT TECHNOLOGY STACK
- **Core:** Laravel 11.x (currently v11.51 on PHP 8.2.30)
- **Frontend:** Livewire 3 (Interactivity), Alpine.js 3 (DOM), Tailwind CSS 3.x (Styling)
- **Icons & Assets:** Inline SVGs (Logo provided in `_project_docs`).
- **Database:** MySQL 8.0+.
- **Key Packages:**
  - `spatie/laravel-permission`: Roles & Permissions.
  - `spatie/laravel-medialibrary`: Image/File uploads.
  - `spatie/laravel-sitemap` & `spatie/laravel-sluggable`: SEO & Routing.
  - `wire-elements/modal`: Dynamic Livewire modals.

## 3. DESIGN SYSTEM (TOKENS)
Strictly use these tailwind classes or hex values:
- **Primary:** `primary-300` (#1C3F78), `primary-200` (#075EA6), `primary-100` (#14ABD5), `primary-50` (#EAFAFF)
- **Neutral:** `neutral-50` (#FFFFFF) to `neutral-600` (#000000).
- **Gradients:**
  - `bg-gradient-brand`: From primary-300 to primary-100.
  - `bg-gradient-teal`, `bg-gradient-sky`, `bg-gradient-blue`.
- **Typography:** `font-display` and `font-sans` (Both mapped to Funnel Display).

## 4. ARCHITECTURAL RULES
1. **Zero Hallucination:** No external image URLs. Use placeholders if not in template.
2. **No Monoliths:** Max 500 lines for Blade. Use anonymous components: `<x-section-header>`, `<x-button-primary>`.
3. **Mobile-First:** Always start with base Tailwind classes, scale up with `md:` and `lg:`.
4. **Security:** Always use Laravel Validation. Honeypot on all public forms.
5. **Accessibility:** WCAG 2.1 AA (Contrast, Aria labels, Focus trapping).

## 5. CURRENT PROJECT STATUS
- [x] Phase 1: Foundation (Laravel 11, Tailwind 3, Alpine 3, Core Packages).
- [ ] Phase 1: Authentication & Basic Layout (Next Step).
- [ ] Phase 2: Public Pages (Home, About, Services).
- [ ] Phase 3: Admin Panel.

**Standing by for Handoff Templates.**
