# API Reference

Base URL: `http://localhost:8000/api`

All responses are JSON. Send `Accept: application/json` on every request (Laravel falls back to an HTML error page otherwise, e.g. on 404s).

---

## GET /team-members

Returns all team members, ordered by id (this order also defines the on-call rotation sequence).

**Response 200**
```json
{
  "data": [
    { "id": 1, "name": "Alice" },
    { "id": 2, "name": "Bob" },
    { "id": 3, "name": "Charlie" },
    { "id": 4, "name": "Diana" }
  ]
}
```

---

## GET /leave-requests

Returns leave requests, ordered by `start_date`. Supports optional filtering.

**Query parameters** (both optional)

| Param | Type | Notes |
|---|---|---|
| `team_member_id` | integer | filter to one team member |
| `status` | string | `pending` \| `approved` \| `rejected` |

**Response 200**
```json
{
  "data": [
    {
      "id": 1,
      "team_member_id": 1,
      "team_member": { "id": 1, "name": "Alice" },
      "start_date": "2026-07-01",
      "end_date": "2026-07-05",
      "reason": "Summer vacation",
      "status": "pending",
      "created_at": "2026-06-23T18:30:44.000000Z",
      "updated_at": "2026-06-23T18:30:44.000000Z"
    }
  ]
}
```

---

## POST /leave-requests

Creates a new leave request. Always created with `status: "pending"`.

**Body**

| Field | Type | Required | Notes |
|---|---|---|---|
| `team_member_id` | integer | yes | must exist in `team_members` |
| `start_date` | date (`YYYY-MM-DD`) | yes | |
| `end_date` | date (`YYYY-MM-DD`) | yes | must be `>=` start_date |
| `reason` | string | yes | max 500 chars |

**Response 201** — same shape as a single item in the `GET /leave-requests` list above.

**Response 422 — validation error**
```json
{
  "message": "This team member already has a leave request that overlaps these dates.",
  "errors": {
    "start_date": [
      "This team member already has a leave request that overlaps these dates."
    ]
  }
}
```
Other 422 causes: `end_date` before `start_date`, non-existent `team_member_id`, missing required field.

---

## PATCH /leave-requests/{id}

Partial update. Any subset of fields below may be sent.

**Body** (all optional)

| Field | Type | Notes |
|---|---|---|
| `team_member_id` | integer | must exist |
| `start_date` | date | |
| `end_date` | date | must be `>=` start_date (merged with existing value if omitted) |
| `reason` | string | max 500 |
| `status` | string | `pending` \| `approved` \| `rejected` |

If `team_member_id`/`start_date`/`end_date` is changed, the overlap check re-runs against the *other* leave requests for that team member (merging any field you didn't send from the existing record), excluding the record being updated itself.

**Response 200** — updated resource, same shape as above.

**Response 422** — invalid `status` value, or the updated date range now overlaps another request.

**Response 404** — `id` doesn't exist.

---

## DELETE /leave-requests/{id}

**Response 204** — no body.

**Response 404** — `id` doesn't exist.

---

## GET /on-call

Returns the on-call team member for a given week, and whether they have a conflicting approved leave.

**Query parameters**

| Param | Type | Notes |
|---|---|---|
| `week` | date (`YYYY-MM-DD`) | optional — any day within the target week. Defaults to the current week if omitted. |

**Response 200**
```json
{
  "week_start": "2026-06-22",
  "week_end": "2026-06-28",
  "on_call_member": { "id": 1, "name": "Alice" },
  "conflict": false
}
```
`conflict` is `true` only if `on_call_member` has an **approved** leave request overlapping `week_start`–`week_end`. Pending or rejected leave never sets this to `true`.
