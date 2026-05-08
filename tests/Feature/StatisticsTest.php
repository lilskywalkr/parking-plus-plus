<?php

use App\Models\User;

test('unauthenticated users cannot enter the statistics subpage and are redirected thence', function () {
   $response = $this->get(route('statistics'));

   $response->assertRedirect(route('login'));
});

test('regular users cannot enter the statistics subpage and are redirected thence', function () {
   $user = User::factory()->create([ 'is_admin' => false ]);

   $this->actingAs($user);

   $response = $this->get(route('statistics'));
   $response->assertRedirect();
});

test('admin can visit the statistics page', function () {
    $user = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($user);

    $response = $this->get(route('statistics'));
    $response->assertStatus(200);
});

test('regular users cannot visit the show subpage of statistics', function () {
   $user = User::factory()->create([ 'is_admin' => false ]);

   $this->actingAs($user);

   $response = $this->get(route('statistics.show'));
   $response->assertRedirect();
});

test('the statistics date should not be empty', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);

    $response = $this->get(route('statistics.show', [
        'date' => fake()->randomElements([null, ''])
    ]));

    $response
        ->assertSessionHasErrors('date')
        ->assertRedirect();
});

test('the statistics date cannot be of a format other than Y-m-d', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);

    $response = $this->get(route('statistics.show', [
        'date' => '12-16-2021'
    ]));
    $response->assertSessionHasErrors('date');

    $response = $this->get(route('statistics.show', [
        'date' => '31-01-2021'
    ]));
    $response->assertSessionHasErrors('date');

    $response = $this->get(route('statistics.show', [
        'date' => '2000-27-07'
    ]));
    $response->assertSessionHasErrors('date');
});

test('the statistics date is of the format Y-m-d', function () {
   $admin = User::factory()->create([ 'is_admin' => true ]);

   $this->actingAs($admin);

   $response = $this->get(route('statistics.show', [
       'date' => '2000-07-07'
   ]));
   $response->assertStatus(200);

   $response = $this->get(route('statistics.show', [
       'date' => '2007-09-30'
   ]));
   $response->assertStatus(200);
});
