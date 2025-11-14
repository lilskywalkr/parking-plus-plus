<?php

use App\Models\User;

test('unauthenticated users cannot enter the record subpage and are redirected thence', function () {
    $response = $this->get(route('record'));

    $response->assertRedirect(route('login'));
});

test('regular users cannot enter the record subpage and are redirected thence', function () {
    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);

    $response = $this->get(route('record'));

    $response->assertRedirect();
});

test('admin can enter the record subpage', function () {
   $admin = User::factory()->create([ 'is_admin' => true ]);

   $this->actingAs($admin);
   $response = $this->get(route('record'));

   $response->assertStatus(200);
});

/* Record search */
test('unauthenticated users cannot enter the filter route', function () {
    $response = $this->get(route('record.filter'));

    $response->assertRedirect(route('login'));
});

test('regular users cannot enter the filter route and are redirected thence', function () {
    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);

    $response = $this->get(route('record.filter'));
    $response->assertRedirect();
});

test('the search query can be empty', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);
    $response = $this->get(route('record.filter', [
        'q' => fake()->randomElement([null, ''])
    ]));

    $response->assertStatus(200);
});

test('the sorting option cannot be other than date, time, user, parking_space_id, registration_plates', function () {
    $admin = User::factory()->create(['is_admin' => true ]);

    $this->actingAs($admin);
    // Hitting the filter route with invalid sorting option
    $response = $this->get(route('record.filter', [
        'option' => 'action',
        'direction' => -1
    ]));

    // Redirects with validation errors
    $response
        ->assertSessionHasErrors(['option'])
        ->assertRedirect();

    // Hitting the filter route with valid sorting option
    $response = $this->get(route('record.filter', [
        'option' => fake()->randomElement(['date', 'time', 'user', 'parking_space_id', 'registration_plates']),
        'direction' => 1
    ]));

    $response->assertStatus(200);
});

test('the sorting direction cannot be empty if the sorting option was provided', function () {
   $admin = User::factory()->create([ 'is_admin' => true ]);

   $this->actingAs($admin);
   $response = $this->get(route('record.filter', [
       'option' => 'date',
       'direction' => null
   ]));

   // Redirects with validation errors
   $response
       ->assertSessionHasErrors(['direction'])
       ->assertRedirect();
});

test('the sorting direction cannot be other than -1 or 1', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);
    // Hitting the filter route with invalid direction
    $response = $this->get(route('record.filter', [
        'option' => 'date',
        'direction' => 0
    ]));

    // Redirects with validation errors
    $response
        ->assertSessionHasErrors(['direction'])
        ->assertRedirect();

    // Hitting the filter route with valid direction
    $response = $this->get(route('record.filter', [
        'option' => 'date',
        'direction' => fake()->randomElement([-1, 1])
    ]));

    $response->assertStatus(200);
});
