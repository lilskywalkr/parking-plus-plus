<?php

use App\Models\ParkingSpace;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/stall');

    $response->assertRedirect(route('login'));
});

test('admin cannot visit the stall subpage and is redirected thence', function () {
   $admin = User::factory()->create([ 'is_admin' => true ]);

   $this->actingAs($admin);

   $response = $this->get(route('stall'));

   $response->assertRedirect();
});

test('user can visit the stall subpage', function () {
    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);

    $response = $this->get(route('stall'));

    $response->assertStatus(200);
});
