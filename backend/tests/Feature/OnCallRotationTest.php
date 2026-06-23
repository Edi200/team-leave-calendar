<?php

use App\Models\LeaveRequest;
use App\Models\TeamMember;
use Database\Seeders\TeamMemberSeeder;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(TeamMemberSeeder::class);
});

it('returns Alice as on-call for week 0', function () {
    $response = getJson('/api/on-call?week=2025-01-06');

    expect($response->status())->toBe(200)
        ->and($response->json('week_start'))->toBe('2025-01-06')
        ->and($response->json('week_end'))->toBe('2025-01-12')
        ->and($response->json('on_call_member'))->toMatchArray(['id' => 1, 'name' => 'Alice']);
});

it('returns Bob as on-call for week 1', function () {
    $response = getJson('/api/on-call?week=2025-01-13');

    expect($response->status())->toBe(200)
        ->and($response->json('on_call_member'))->toMatchArray(['id' => 2, 'name' => 'Bob']);
});

it('returns Diana as on-call for week 7 when the rotation wraps around', function () {
    $response = getJson('/api/on-call?week=2025-02-24');

    expect($response->status())->toBe(200)
        ->and($response->json('on_call_member'))->toMatchArray(['id' => 4, 'name' => 'Diana']);
});

it('resolves a mid-week date to the same on-call member as that week Monday', function () {
    $response = getJson('/api/on-call?week=2025-01-15');

    expect($response->status())->toBe(200)
        ->and($response->json('week_start'))->toBe('2025-01-13')
        ->and($response->json('on_call_member'))->toMatchArray(['id' => 2, 'name' => 'Bob']);
});

it('flags a conflict when the on-call member has approved leave overlapping the week', function () {
    $bob = TeamMember::query()->where('name', 'Bob')->first();

    LeaveRequest::factory()->approved()->create([
        'team_member_id' => $bob->id,
        'start_date' => '2025-01-13',
        'end_date' => '2025-01-17',
    ]);

    $response = getJson('/api/on-call?week=2025-01-15');

    expect($response->status())->toBe(200)
        ->and($response->json('conflict'))->toBeTrue();
});

it('does not flag a conflict when the on-call member has only pending leave overlapping the week', function () {
    $bob = TeamMember::query()->where('name', 'Bob')->first();

    LeaveRequest::factory()->create([
        'team_member_id' => $bob->id,
        'start_date' => '2025-01-13',
        'end_date' => '2025-01-17',
    ]);

    $response = getJson('/api/on-call?week=2025-01-15');

    expect($response->status())->toBe(200)
        ->and($response->json('conflict'))->toBeFalse();
});

it('does not flag a conflict when the on-call member has only rejected leave overlapping the week', function () {
    $bob = TeamMember::query()->where('name', 'Bob')->first();

    LeaveRequest::factory()->rejected()->create([
        'team_member_id' => $bob->id,
        'start_date' => '2025-01-13',
        'end_date' => '2025-01-17',
    ]);

    $response = getJson('/api/on-call?week=2025-01-15');

    expect($response->status())->toBe(200)
        ->and($response->json('conflict'))->toBeFalse();
});
