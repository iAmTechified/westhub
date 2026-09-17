# WestHub Healthcare Application

This repository contains the complete source code for the **WestHub** platform, a comprehensive healthcare service web application built with Laravel and Livewire. 

It uses a "monorepo" structure, housing both the public-facing website and the dedicated administrative backend.

## Project Structure

* **Main Application (Root):** The public-facing website where users can view care services, book appointments, submit join requests, and view the gallery.
* **Admin Panel (`/westhub-admin`):** The secure backend portal (typically hosted on a subdomain like `admin.westhub.com`) where administrators can manage appointments, services, users, and SEO configurations.

## Architecture & Tech Stack
* **Framework:** Laravel 11.x
* **Frontend:** TailwindCSS, Alpine.js, Livewire 3
* **Database:** MySQL
* **Deployment:** Automated via cPanel Git Version Control (`.cpanel.yml`)

## cPanel Deployment Guide

This project is configured for automated deployments via cPanel Git Version Control. 
Because the application uses a split directory structure in production (public files in `public_html`, core files outside of it), the `.cpanel.yml` file automates the copying and installation.

When cPanel pulls the latest branch:
1. It copies the main core files into the `westhub` directory.
2. It copies the public assets into `public_html`.
3. It copies the admin public assets into the `admin` subdomain folder.
4. It executes `composer install` for both applications to install PHP dependencies.

*(Note: Frontend assets are pre-compiled and tracked via the `/public/build` directory, so Node.js/NPM is not required on the production server).*

Deployment does **not** run migrations or create cron jobs; those are manual steps. Both apps must
also share one `SETTINGS_ENCRYPTION_KEY` and one `PUBLIC_STORAGE_PATH`, or encrypted settings and
admin-uploaded images will not work on the public site.

## Documentation

* **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)**: environment setup, email, Google integrations, cPanel
  deployment, migrations, cron jobs, roles and handover.
* **[docs/FEATURES.md](docs/FEATURES.md)**: how the promo campaign, booking providers and admin roles
  behave.
