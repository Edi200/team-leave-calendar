# Team Leave Calendar — Frontend

Vue 3 single-page app for the Team Leave Calendar with On-Call Rotation assessment project. Consumes the Laravel REST API in `../backend`.

## Stack

- Vue 3 + TypeScript + Vue Router
- Tailwind CSS v4
- shadcn-vue (Reka UI primitives, Zinc theme, CSS variable theming)
- Axios
- `@lucide/vue` icons

## Requirements

- Node.js 20+ and npm
- The backend API running (see `../backend/README.md`) — this app has no mock data fallback

## Setup

```bash
cd team-leave-calendar/frontend
npm install
cp .env.example .env
```

`.env` sets `VITE_API_BASE_URL` (defaults to `http://localhost:8000/api` even without a `.env` file, so this step is optional but recommended for clarity).

## Running the app

Make sure the backend is running first (`php artisan serve` in `../backend`), then:

```bash
npm run dev
```

Open the printed local URL (typically `http://localhost:5173`).

## Build

```bash
npm run build
```

## Type-check & lint

```bash
npm run type-check
npm run lint
```

## Features

- **Team Members** — read-only grid of the four seeded team members (per the task spec, team members are a fixed list, not user-managed)
- **Leave Requests** — full CRUD, filterable by team member and status, approve/reject actions, inline validation errors (including overlap conflicts) from the API
- **Calendar** — month view with color-coded leave bars by status; falls back to a vertical agenda list on small screens
- **On-Call** — current on-call person by week, with prominent visual highlighting when they have an approved leave conflicting with that week
- **Dark mode** — toggle in the header (desktop) / nav (mobile), persisted across sessions
- **Responsive** — mobile nav drawer, stacked cards instead of tables on small screens, agenda view instead of a calendar grid on small screens

## Notable implementation detail

The `GET /on-call` endpoint only returns a boolean `conflict` flag, not the specific overlapping leave dates. The On-Call page enriches this client-side: when `conflict: true`, it separately fetches the on-call person's approved leave requests and filters to the ones overlapping the queried week, so the conflict alert can show the actual dates — no backend changes were needed for this.

## What wasn't completed

See `../backend/README.md` — Docker setup, automatic on-call reassignment, and comments on leave requests were intentionally skipped given the assessment's time budget; reasoning is documented there.
