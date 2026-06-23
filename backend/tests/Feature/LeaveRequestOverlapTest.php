<?php

use App\Models\LeaveRequest;
use App\Models\TeamMember;
use Database\Seeders\TeamMemberSeeder;

use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(TeamMemberSeeder::class);
});

it('rejects overlapping leave requests for the same team member with 422', function () {
    $member = TeamMember::query()->where('name', 'Alice')->first();

    LeaveRequest::factory()->create([
        'team_member_id' => $member->id,
        'start_date' => '2025-03-01',
        'end_date' => '2025-03-10',
    ]);

    $response = postJson('/api/leave-requests', [
        'team_member_id' => $member->id,
        'start_date' => '2025-03-05',
        'end_date' => '2025-03-15',
        'reason' => 'Overlapping trip',
    ]);

    expect($response->status())->toBe(422)
        ->and($response->json('errors.start_date.0'))->toBe('This team member already has a leave request that overlaps these dates.');
});

it('allows overlapping leave requests for different team members', function () {
    $alice = TeamMember::query()->where('name', 'Alice')->first();
    $bob = TeamMember::query()->where('name', 'Bob')->first();

    LeaveRequest::factory()->create([
        'team_member_id' => $alice->id,
        'start_date' => '2025-04-01',
        'end_date' => '2025-04-10',
    ]);

    $response = postJson('/api/leave-requests', [
        'team_member_id' => $bob->id,
        'start_date' => '2025-04-05',
        'end_date' => '2025-04-15',
        'reason' => 'Same dates, different person',
    ]);

    expect($response->status())->toBe(201);
});

it('allows adjacent non-overlapping ranges where one ends the day before the other starts', function () {
    $member = TeamMember::query()->where('name', 'Alice')->first();

    LeaveRequest::factory()->create([
        'team_member_id' => $member->id,
        'start_date' => '2025-05-01',
        'end_date' => '2025-05-05',
    ]);

    $response = postJson('/api/leave-requests', [
        'team_member_id' => $member->id,
        'start_date' => '2025-05-06',
        'end_date' => '2025-05-10',
        'reason' => 'Starts the day after the previous request ends',
    ]);

    expect($response->status())->toBe(201);
});

it('does not block a new overlapping request when an existing request is rejected', function () {
    $member = TeamMember::query()->where('name', 'Alice')->first();

    LeaveRequest::factory()->rejected()->create([
        'team_member_id' => $member->id,
        'start_date' => '2025-06-01',
        'end_date' => '2025-06-10',
    ]);

    $response = postJson('/api/leave-requests', [
        'team_member_id' => $member->id,
        'start_date' => '2025-06-05',
        'end_date' => '2025-06-15',
        'reason' => 'Overlaps a rejected request only',
    ]);

    expect($response->status())->toBe(201);
});
