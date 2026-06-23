<?php

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\TeamMember;
use Database\Seeders\TeamMemberSeeder;

use function Pest\Laravel\patchJson;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed(TeamMemberSeeder::class);
});

it('allows valid status transitions via PATCH', function (string $status) {
    $leaveRequest = LeaveRequest::factory()->create([
        'team_member_id' => TeamMember::query()->where('name', 'Alice')->first()->id,
    ]);

    $response = patchJson("/api/leave-requests/{$leaveRequest->id}", [
        'status' => $status,
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.status'))->toBe($status);
})->with([
    LeaveStatus::Approved->value,
    LeaveStatus::Rejected->value,
]);

it('rejects invalid status values with 422', function () {
    $leaveRequest = LeaveRequest::factory()->create([
        'team_member_id' => TeamMember::query()->where('name', 'Alice')->first()->id,
    ]);

    $response = patchJson("/api/leave-requests/{$leaveRequest->id}", [
        'status' => 'invalid',
    ]);

    expect($response->status())->toBe(422)
        ->and($response->json('errors.status'))->not->toBeEmpty();
});
