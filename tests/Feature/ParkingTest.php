<?php

use App\Models\ParkingSpace;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/parking');
    $response->assertRedirect('/login');
});

test('authenticated users can visit parking', function () {
   $user = User::factory()->create();

   $this->actingAs($user);
   $response = $this->get('/parking');
   $response->assertStatus(200);
});

test('authenticated users can visit the reserve subpage of parking', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get("/parking/$ps->id/reserve");
    $response->assertStatus(200);
});

test('reserve subpage redirects to parking if the stall id is invalid', function () {
    ParkingSpace::factory()->create();

    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get("/parking/0/reserve");
    $response->assertStatus(302);
});

test('user can reserve an available parking stall', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);

    // A user attempts to reserve an available parking stall
    $this->actingAs($user);
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    $response->assertRedirect('/parking');
});

test('user cannot reserve an available parking stall if the registration plates is than 3 characters', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->randomLetter(),
        'stall_id' => $ps->id
    ]);

    $response->assertSessionHasErrors([
        'plate' => 'The plate field must be at least 3 characters.'
    ]);
});

test('user cannot reserve an available parking stall if the stall id is invalid', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);

    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => 0,
    ]);
    $response->assertSessionHasErrors([
        'stall_id' => 'The stall id field must be at least 1.'
    ]);
});

test('user cannot reserve an available parking stall if the stall id is incorrect i.e. non-existent', function () {
    $ps = ParkingSpace::factory()->create();
    $incorrect_stall_id = 2;

    $user = User::factory()->create([ 'is_admin' => false ]);

    $this->actingAs($user);

    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $incorrect_stall_id,
    ]);
    $response->assertSessionHasErrors([
        'stall_id' => 'Stall No. '. $incorrect_stall_id .' is non-existent.'
    ]);
});

test('user cannot reserve a blocked or reserved parking stall', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory(2)->create([ 'is_admin' => false ]); // Creating two regular users

    // A user reserves a stall and logs out
    $this->actingAs($user[0]);
    $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);
    $this->post('/logout')->assertRedirect();

    // Another user tries to reserve that same stall with a unique registration plates
    $this->actingAs($user[1]);
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    $response->assertSessionHasErrors([
        'stall_id' => 'Stall No. '. $ps->id .' is unavailable.'
    ]);
});

test('user cannot reserve an available parking stall with an already existing registration plates', function () {
   $ps = ParkingSpace::factory(2)->create(); // Creating two available parking stalls

   $user = User::factory()->create([ 'is_admin' => false ]);

   $plates = fake()->country();

   // A user reserves the first free stall with a registration plates
   $this->actingAs($user);
   $this->patch('/parking/reserve/save', [
       'plate' => $plates,
       'stall_id' => $ps[0]->id
   ]);

   // The user tries to reserve another free stall with the same registration plates
   $response = $this->patch('/parking/reserve/save', [
       'plate' => $plates,
       'stall_id' => $ps[1]->id
   ]);

   $response->assertSessionHasErrors([
       'plate' => 'This plate is already registered.'
   ]);
});

test('admin can block an available parking stall', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => true ]);
    $this->actingAs($user);

    $response = $this->patch('/parking/block/save', [
        'stall_id' => $ps->id
    ]);

    $response->assertRedirect('/parking');
});


test('admin cannot block a stall if the stall is reserved by a user', function () {
   $ps = ParkingSpace::factory()->create();

   // Creating a regular user
   $user = User::factory()->create(['is_admin' => false]);
   $this->actingAs($user);

   // The user reserves the free parking stall and logs out
   $this->actingAs($user)->patch('/parking/reserve/save', [
       'plate' => fake()->country(),
       'stall_id' => $ps->id
   ]);
   $this->post('/logout')->assertRedirect();

    // Creating an admin
    $admin = User::factory()->create([ 'is_admin' => true ]);
    $this->actingAs($admin);

    // Admin attempts to block a reserved parking stall
    $response = $this->patch('/parking/block/save', [
       'stall_id' => $ps->id
    ]);

    $response->assertSessionHasErrors([
        'reserved' => 'Cannot block this stall, because it is already reserved.'
    ]);
});

test('admin can unblock an unavailable parking stall', function () {
    $ps = ParkingSpace::factory()->create();
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);

    // Blocking the stall
    $response = $this->patch('/parking/block/save', [
        'stall_id' => $ps->id
    ]);

    $response->assertRedirect('/parking');

    // Unblocking the stall
    $response = $this->patch('/parking/block/save', [
        'stall_id' => $ps->id
    ]);
    $response->assertRedirect('/parking');
});

test('admin cannot block a stall if the stall id is invalid', function () {
    $ps = ParkingSpace::factory()->create();

    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);

    $response = $this->patch('/parking/block/save', [
        'stall_id' => 0
    ]);

    $response->assertSessionHasErrors([
        'stall_id' => 'The stall id field must be at least 1.'
    ]);
});

test('admin cannot block a stall if the stall id is incorrect i.e. non-existent', function () {
    $ps = ParkingSpace::factory()->create();
    $incorrect_stall_id = 2;

    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);

    $response = $this->patch('/parking/block/save', [
        'stall_id' => $incorrect_stall_id
    ]);

    $response->assertSessionHasErrors([
        'stall_id' => 'Stall No. '. $incorrect_stall_id .' is non-existent.'
    ]);
});


