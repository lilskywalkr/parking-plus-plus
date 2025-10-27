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
test('unauthenticated users cannot enter the search route', function () {
    $response = $this->get(route('record.search'));

    $response->assertRedirect(route('login'));
});

test('regular users cannot enter the search route and are redirected thence', function () {
    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);

    $response = $this->get(route('record.search'));
    $response->assertRedirect();
});

test('the search query should not be empty', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);
    $response = $this->get(route('record.search', [
        'q' => fake()->randomElement([null, ''])
    ]));

    $response
        ->assertSessionHasErrors(['q'])
        ->assertRedirect();
});

test('the search query should not be other than a string', function () {
   $admin = User::factory()->create([ 'is_admin' => true ]);

   $this->actingAs($admin);
   $response = $this->get(route('record.search', [
       'q' => array(true, false)
   ]));

   $response
       ->assertSessionHasErrors(['q'])
       ->assertRedirect();
});

test('the search query should not exceed the max number of characters', function () {
   $admin = User::factory()->create([ 'is_admin' => true ]);

   $this->actingAs($admin);

   $response = $this->get(route('record.search', [
       'q' => fake()->realText(300)
   ]));

   $response
       ->assertSessionHasErrors(['q'])
       ->assertRedirect();
});
