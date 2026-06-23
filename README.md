# Team Leave Calendar

A team leave management and on-call rotation app, built as a hiring assessment submission for Excite Hungary.

Team members can request leave, see it on a list or calendar, and the app shows who's on call each week — flagging it clearly if the on-call person has approved leave that week.

## Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 13 (REST API only — no Blade/Inertia), SQLite, Pest |
| Frontend | Vue 3 + TypeScript + Vue Router, Tailwind CSS v4, shadcn-vue, Axios |
| CI | GitHub Actions (runs the backend test suite on every push/PR) |

The two are fully decoupled — the frontend is a SPA that talks to the backend purely over JSON, no shared session/auth (the task didn't require user accounts).

## Quick start

1. **Backend** — [`backend/README.md`](./backend/README.md): install, migrate, seed, `php artisan serve`
2. **Frontend** — [`frontend/README.md`](./frontend/README.md): `npm install`, `npm run dev` (needs the backend running)

Start the backend first — the frontend has no offline/mock mode.

## API reference

Full endpoint documentation: [`backend/API.md`](./backend/API.md)
Importable Postman collection (happy paths + edge cases, runnable in order): `backend/postman/TeamLeaveCalendar.postman_collection.json`

## Assumptions

- Only `pending` and `approved` leave requests count as blocking when checking for overlapping dates for the same team member — a `rejected` request never blocks a new one.
- Date ranges are inclusive on both ends; a request ending on day X and another starting on day X+1 are adjacent, not overlapping.
- The on-call rotation is purely calculated (week index modulo team count) from a configurable anchor date — there's no manual override/reassignment table.
- No authentication — not required by the task; the API has no protected routes.

Full details, including backend-specific assumptions: [`backend/README.md`](./backend/README.md#assumptions)

## What wasn't completed

- **Docker** — skipped given the time budget. Environment consistency is handled instead by documenting the exact PHP version requirement and pinning it via `composer.lock` (see `backend/README.md`).
- **Automatic on-call reassignment** — out of scope per the task description; the app flags the conflict but doesn't reassign on-call duty.
- **Comments on leave requests** — considered out of scope for the assessment's time budget; would require a new data model with no direct bearing on the core requirements.

## Optional improvements added

- Pest test suite (15 feature tests) covering overlap validation, status transitions, and on-call rotation/conflict logic
- CI/CD via GitHub Actions
- REST API documentation (`API.md` + Postman collection)
- Calendar month view (with mobile agenda fallback)
- Filtering by team member and status
- Visual on-call conflict highlighting (not just a flag — shows the actual conflicting leave dates)
- Dark mode (not requested, added as UI polish)
