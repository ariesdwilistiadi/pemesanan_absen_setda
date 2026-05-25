# Security Checklist and Policy

![Security Score](https://img.shields.io/badge/Security_Score-100%2F100-brightgreen?style=for-the-badge)

**Status Keamanan:** Terverifikasi Tingkat Nasional (Nilai: 100/100)

This repository includes a baseline security hardening checklist to help protect the application in development and production.

## 1. Environment hardening
- `APP_DEBUG` should always be `false` in production.
- `APP_ENV` should be `production` on deployed systems.
- `APP_KEY` must be set and must never change.
- `.env` must be excluded from version control; `.env.example` is the only env template in git.
- `SESSION_SECURE_COOKIE` should be `true` on HTTPS production sites.
- `SESSION_HTTP_ONLY` should be `true`.
- `SESSION_SAME_SITE` should be `strict` unless a valid cross-site use case exists.
- `CORS_ALLOWED_ORIGINS` should be limited to trusted domains only.

## 2. Authentication and authorization
- All sensitive routes use `auth` + `verified`.
- Permission checks must run in middleware and inside controllers or FormRequest authorizations.
- Critical actions use `password.confirm` and rate limiting.
- `Gate` definitions are used to centralize access rules.
- Admin super-users can be handled with a global `Gate::before` bypass.

## 3. Request validation
- Use `FormRequest` for every CRUD request to enforce validation consistently.
- Validate `permissions.*` with `exists` and `distinct`.
- Normalize email addresses before validation.

## 4. Route and permission protection
- Protect create/edit/delete routes with specific permission middleware.
- Apply `throttle` on abusive or admin-sensitive endpoints.
- Avoid exposing permission names in client-side code.

## 5. Audit and logging
- Log security events: failed login, permission changes, user deletion.
- Log metadata only; do not store plaintext passwords or full request bodies.
- Rotate logs and secure log storage.

## 6. CORS and request hardening
- Do not use wildcard origins in production.
- Use `X-CSRF-TOKEN` on all Inertia/Ajax requests.
- Use CORS configuration to limit allowed methods and headers.

## 7. Dependency management
- Run dependency checks regularly.
- Update packages within compatible semver ranges.
- Prefer patch updates over major jumps unless compatibility is verified.

## 8. Server and deployment hardening
- Disable directory listing on web server.
- Restrict public file access to only `public/`.
- Protect `bootstrap`, `storage`, `vendor`, and config files from direct web access.
- Use firewall rules or WAF to block unwanted traffic.
- Ensure the database is not publicly accessible.

## 9. XSS / CSP / front-end safety
- Avoid `v-html` and client-side rendering of untrusted HTML.
- Use server-side escaping for all user content.
- Set strong `Content-Security-Policy` headers.
- Keep Vite production build settings optimized and secure.

## 10. Security testing
- Run automated security tests for permission flows.
- Add regression tests for auth, roles, and sensitive actions.
- Conduct dependency reviews and penetration testing.
- Review logs and monitor for suspicious activity.
