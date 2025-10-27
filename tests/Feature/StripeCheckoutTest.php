<?php

use App\Models\Order;
use App\Models\ParkingSpace;
use App\Models\User;

test('admin cannot hit the checkout session route and is redirected thence', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);

    $response = $this->post(route('stall.checkout', [
        'id' => fake()->randomElement([null, fake()->randomNumber(1)])
    ]));

    $response->assertRedirect();
});

test('admin cannot enter the checkout success page and is redirected thence', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);
    $response = $this->post(route('stall.checkout.success'));

    $response->assertRedirect();
});

test('admin cannot enter the checkout cancel page and is redirected thence', function () {
    $admin = User::factory()->create([ 'is_admin' => true ]);

    $this->actingAs($admin);
    $response = $this->post(route('stall.checkout.cancel'));

    $response->assertRedirect();
});

test('user cannot enter a checkout session with an invalid stall id', function () {
    $ps = ParkingSpace::factory()->create();
    $incorrect_stall_id = 0;

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // Attempting to enter the checkout session with an incorrect stall id
    $response = $this->post(route('stall.checkout', [
        'id' => $incorrect_stall_id
    ]));

    // The route throws not found http exception - might be modified in the future when refactoring the code
    $response->assertNotFound();

    // Attempting to enter the checkout session with an incorrect stall id once more
    $incorrect_stall_id = -1;
    $response = $this->post(route('stall.checkout', [
        'id' => $incorrect_stall_id
    ]));
    // The route throws not found http exception - might be modified in the future when refactoring the code
    $response->assertNotFound();
});

test('user cannot enter a checkout session with the id of an unreserved stall', function () {
    $ps = ParkingSpace::factory(2)->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps[0]->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // Attempting to enter the checkout session with the id of an unreserved stall
    $response = $this->post(route('stall.checkout', [
        'id' => $ps[1]->id
    ]));

    // The route throws not found http exception - might be modified in the future when refactoring the code
    $response->assertNotFound();
});

test('user can enter a checkout session for their reserved parking stall by the stall id', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // Attempting to enter the checkout session with the stall id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();
});

test('user can enter a checkout session for all their reserved parking stalls by not providing any id', function () {
    $ps = ParkingSpace::factory(2)->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    foreach ($ps as $p) {
        // User reserves a stall
        $response = $this->patch(route('parking.reserve.save'), [
            'plate' => fake()->country(),
            'stall_id' => $p->id
        ]);

        // If the reservation was successful the user is being redirected back to the parking
        $response->assertRedirect('/parking');
    }

    // Attempting to enter the checkout session for the all the reserved stalls by not providing any id
    $response = $this->post(route('stall.checkout', [
        'id' => null
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();
});

test('user sees not found error on the success page if the session id parameter is not present in the uri', function () {
    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    $response = $this->post(route('stall.checkout.success', [
        'session_id' => null
    ]));

    $response->assertNotFound();
});

test('user sees not found error on the success page if session id is incorrect', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // User enters the checkout session with the correct stall id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();

    // Attempting to visit the checkout success page with an incorrect session id
    $response = $this->get(route('stall.checkout.success', [
        'session_id' => str_repeat(chr(65 + rand(0, 25)), 65) // 65 char long string of a random character
    ]));

    // If the session id was not found in the db then not found error is thrown
    $response->assertNotFound();
});

test('user sees not found error on the success page if the session id is correct but the order is unpaid', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // User enters the checkout session with the correct stall id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();

    // Getting the session id from the order that was created in the checkout
    $session_id = Order::latest()->first()->session_id;

    // Attempting to visit the checkout success page with the correct session id
    $response = $this->get(route('stall.checkout.success', [
        'session_id' => $session_id
    ]));

    // If the session id was found but the order was not paid then not found error is thrown
    $response->assertNotFound();
});

test('user sees not found error on the success page if they revisit the page with the correct session id of a paid order', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // User enters the checkout session with the correct stall id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();

    // Simulating the payment by updating the order's fields
    Order::latest()->update([
        'status' => \App\Enums\OrderStatusEnum::PAID,
        'payment_summarized' => true, // If the success page was once visited this field is updated to true

    ]);

    // Getting the session id from the order that was created in the checkout
    $session_id = Order::latest()->first()->session_id;

    // Attempting to revisit the checkout success page with the correct session id
    $response = $this->get(route('stall.checkout.success', [
        'session_id' => $session_id
    ]));

    // If the session id was found and the order was paid and the payment was summarized (success page was once visited) then not found error is thrown
    $response->assertNotFound();
});

test('user sees the summary of their payment on the success page if the order was paid', function () {
    $ps = ParkingSpace::factory()->create();

    $user = User::factory()->create([ 'is_admin' => false ]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // User enters the checkout session with the correct stall id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();

    // Simulating the payment by updating the order's fields
    Order::latest()->update([
        'status' => \App\Enums\OrderStatusEnum::PAID,

    ]);

    // Getting the session id from the order that was created in the checkout
    $session_id = Order::latest()->first()->session_id;

    // Attempting to visit the checkout success page for the first time with the correct session id
    $response = $this->get(route('stall.checkout.success', [
        'session_id' => $session_id
    ]));

    $response->assertStatus(200);
});
