# Team Leave Calendar — Backend

REST API for the Team Leave Calendar with On-Call Rotation assessment project.

Built with **Laravel 13**, a pure JSON API — no Blade, no Inertia, no authentication.

## Requirements

- **PHP 8.4+** (the committed `composer.lock` pins Symfony packages that require PHP 8.4.1 or newer — PHP 8.3 will fail `composer install`)
- Composer 2.x
- SQLite support (`pdo_sqlite` extension — bundled with most PHP installs by default)

## Setup

```bash
git clone <repo-url>
cd team-leave-calendar/backend

composer install
cp .env.example .env
php artisan key:generate
```

The `.env.example` already sets `DB_CONNECTION=sqlite`. Create the database file and run migrations with seed data:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

This seeds four team members in a fixed order — **Alice, Bob, Charlie, Diana** — which also defines the on-call rotation sequence.

### On-call rotation anchor

The rotation is calculated relative to `ONCALL_START_DATE` in `.env` (must be a **Monday**). This is set to a recent Monday by default. Change it if you want week 0 of the rotation to start on a different date:

```env
ONCALL_START_DATE=2026-06-22
```

## Running the app

```bash
php artisan serve
```

API is now available at `http://localhost:8000/api`. See [API.md](./API.md) for the full endpoint reference.

## Running tests

```bash
php artisan test
```

15 Pest feature tests cover overlap validation, status transitions, and on-call rotation/conflict logic, using an in-memory SQLite database (configured in `phpunit.xml`, independent of your local `.env`).

CI runs the same test suite automatically on every push/PR via GitHub Actions (`.github/workflows/ci.yml`).

## Manual API testing (Postman)

A ready-to-import collection is included at `postman/TeamLeaveCalendar.postman_collection.json`. Import it into Postman and run the requests **in order, 01 through 20** — later requests depend on records created by earlier ones (ids are captured automatically into collection variables). Make sure:
- `php artisan serve` is running on port 8000
- No Postman **Environment** is selected (top-right dropdown → "No Environment") — an active Environment with its own `baseUrl`/other variables from a different project can silently override the request URLs

## Assumptions

- **Overlap blocking rule:** only leave requests with status `pending` or `approved` count when checking for overlapping date ranges for the same team member. A `rejected` request never blocks a new request for the same period.
- **Date ranges are inclusive on both ends** — two ranges overlap if `start_date <= other.end_date AND end_date >= other.start_date`. A request ending on day X and another starting on day X+1 are **not** considered overlapping (adjacent, not overlapping).
- **On-call rotation** is purely derived (week index modulo team member count) — there is no database table for it, and no support for manual overrides or reassignment.
- **On-call conflict** is flagged only when the on-call person has an **approved** leave request overlapping that week. A pending or rejected leave does not flag a conflict.
- **No authentication** — the task didn't require it. Laravel Sanctum is installed (via `php artisan install:api`) but entirely unused; no routes are protected.
- **No pagination** — endpoints return full collections, given the small expected data volume for this assessment.

## What wasn't completed

- **Docker** — skipped given the time budget. Environment consistency is handled instead by documenting the exact PHP version requirement (8.4+) above, and pinning it via `composer.lock`.
- **Automatic on-call reassignment** when the on-call person is on leave — out of scope per the task description; the API only flags the conflict, it doesn't reassign.

## Optional improvements added

- **Pest test suite** (15 feature tests, 58 assertions) covering all core business rules
- **CI/CD** via GitHub Actions — runs the full test suite on every push/PR
- **REST API documentation** — see [API.md](./API.md)
