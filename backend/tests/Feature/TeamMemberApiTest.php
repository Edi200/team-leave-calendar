<?php

use Database\Seeders\TeamMemberSeeder;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

it('returns team members ordered by id with the correct shape', function () {
    seed(TeamMemberSeeder::class);

    $response = getJson('/api/team-members');

    expect($response->status())->toBe(200)
        ->and($response->json('data'))->toHaveCount(4)
        ->and($response->json('data.0'))->toMatchArray(['id' => 1, 'name' => 'Alice'])
        ->and($response->json('data.1'))->toMatchArray(['id' => 2, 'name' => 'Bob'])
        ->and($response->json('data.2'))->toMatchArray(['id' => 3, 'name' => 'Charlie'])
        ->and($response->json('data.3'))->toMatchArray(['id' => 4, 'name' => 'Diana']);
});
