<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/parking');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the parking', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/parking');
    $response->assertStatus(200);
});
