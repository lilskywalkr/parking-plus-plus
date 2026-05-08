<?php

use App\Jobs\CreateOrderJob;
use App\Models\Order;
use Illuminate\Support\Facades\Queue;

test('The job can be dispatched', function () {
    // Faking a queue
    Queue::fake();

    // Instantiation a job
    $job = new CreateOrderJob(
        fake()->country(),  // registration plates
        fake()->randomElement([6, 7.2, 8.6]),  // total price
        str_repeat(chr(65 + rand(0, 25)), 65),  // session id
        fake()->numberBetween(1, 1) // user id
    );

    dispatch($job); // Dispatching the job

    // Asserting the job was pushed to the queue
    Queue::assertPushed(CreateOrderJob::class);
});

test('The job creates a record in the orders table', function () {
    // Instantiation a job
    $job = new CreateOrderJob(
        fake()->country(),  // registration plates
        fake()->randomElement([6, 7.2, 8.6]),  // total price
        str_repeat(chr(65 + rand(0, 25)), 65),  // session id
        fake()->numberBetween(1, 1) // user id
    );

    $job->handle(); // Dispatching the job by handle method

    // Asserting a record was created in the db
    $this->assertEquals(1, Order::all()->count());
});

