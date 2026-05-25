# Dependency Audit Summary

This document summarizes the current dependency audit findings for this project.

## Composer

The project currently uses:
- `laravel/framework` ^12.0
- `inertiajs/inertia-laravel` ^2.0
- `laravel/tinker` ^2.10.1
- `spatie/laravel-permission` ^6.25

### Audit results
- `composer audit` is not available in this Composer version.
- `composer outdated --direct` shows the following major upgrade opportunities:
  - `inertiajs/inertia-laravel` current: v2.0.24, latest: v3.1.0
  - `laravel/tinker` current: v2.11.1, latest: v3.0.2

### Recommendation
- Keep current versions if you want stable compatibility with the existing Laravel 12 codebase.
- Plan major upgrades only after reviewing breaking changes and testing under a dedicated branch.

## NPM

The project currently uses:
- `vite` ^7.0.7
- `@vitejs/plugin-vue` ^6.0.0
- `@inertiajs/vue3` ^2.0.0
- `laravel-vite-plugin` ^2.0.0
- `tailwindcss` ^3.2.1
- `vue` ^3.4.0
- `vuedraggable` ^4.1.0

### Audit results
- `npm outdated --depth=0` indicates the following newer major versions are available:
  - `@inertiajs/vue3` current: 2.3.23, latest: 3.1.1
  - `laravel-vite-plugin` current: 2.1.0, latest: 3.1.0
  - `tailwindcss` current: 3.4.19, latest: 4.3.0
  - `vite` current: 7.3.3, latest: 8.0.11
  - `vuedraggable` current: 4.1.0, latest: 2.24.3

### Recommendation
- Do not update to major versions without compatibility testing.
- If you want the latest security fixes, first confirm the app supports the target major version.
- Update packages incrementally and run `npm run build` after each upgrade.
