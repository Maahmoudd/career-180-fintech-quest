<?php

use App\Models\User;

test('authenticated users can render the balances page', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('balances'));

    $response->assertOk()
        ->assertSee('Instructor balances');
});
