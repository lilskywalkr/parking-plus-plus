<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\ParkingSpace;
use App\Models\User;
use App\Services\StripeCheckoutService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

test('summarizeCheckout method throws an exception if the session id is incorrect', function () {
    // Instantiating a stripe checkout service class
    $service = new StripeCheckoutService();

    // Expecting not found exception to be thrown
    $this->expectException(NotFoundHttpException::class);

    // The method should throw an exception if the session id was not found
    $service->summarizeCheckout('aaa', OrderStatusEnum::OPEN);
});

test('summarizeCheckout method throws an exception if the summary was displayed before', function () {
    // Instantiating a stripe checkout service class
    $service = new StripeCheckoutService();

    // Creating a parking space
    $ps = ParkingSpace::factory()->create();

    // Creating a user
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // User enters the checkout with the reserved stall's id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();

    // Simulating the payment by updating the order's fields
    $order = Order::latest()->first();
    $order->update([
        'status' => OrderStatusEnum::COMPLETE,
        'payment_summarized' => true
    ]);
    $order->save();

    // Expecting not found exception to be thrown
    $this->expectException(NotFoundHttpException::class);

    // The method should throw an exception if the summary was displayed
    $service->summarizeCheckout($order->session_id);
});

test('summarizeCheckout summarizes the order when the user pays for the stall', function () {
    // Instantiating a stripe checkout service class
    $service = new StripeCheckoutService();

    // Creating a parking space
    $ps = ParkingSpace::factory()->create();

    // Creating a user
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    // User reserves a stall
    $response = $this->patch('/parking/reserve/save', [
        'plate' => fake()->country(),
        'stall_id' => $ps->id
    ]);

    // If the reservation was successful the user is being redirected back to the parking
    $response->assertRedirect('/parking');

    // User enters the checkout with the reserved stall's id
    $response = $this->post(route('stall.checkout', [
        'id' => $ps->id
    ]));

    // If the checkout was successfully created the user is being redirected to the stripe checkout page
    $response->assertRedirect();

    // Simulating the payment by updating the order's fields
    $order = Order::latest()->first();
    $order->update([
        'status' => OrderStatusEnum::COMPLETE,
    ]);

    // The method returns the summarized order
    $returned_order = $service->summarizeCheckout($order->session_id);

    expect($returned_order->payment_summarized)->toBeTrue();
});
