<?php


use App\Enums\ParkingRecordActionEnum;
use App\Events\ParkingActionRecorded;
use App\Listeners\CreateRecordListener;
use Illuminate\Support\Facades\Event;

test('The event listener can be dispatched', function () {
    // Faking an event
    Event::fake();

    ParkingActionRecorded::dispatch(
        fake()->randomNumber(),
        fake()->randomNumber(1),
        ParkingRecordActionEnum::DRIVE_OUT,
        fake()->country()
    );

    // Asserting the job was pushed to the queue
    Event::assertDispatched(ParkingActionRecorded::class);
});

test('The listener is attached to the event', function () {
    // Faking an event
    Event::fake();

    Event::assertListening(
        ParkingActionRecorded::class,
        CreateRecordListener::class
    );
});

test('The event creates a record of the action upon a parking stall', function () {
    $user = \App\Models\User::factory()->create();
    $ps = \App\Models\ParkingSpace::factory()->create();

    event(new ParkingActionRecorded(
        $user->id,
        $ps->id,
        ParkingRecordActionEnum::DRIVE_OUT,
        fake()->country()
    ));

    $this->assertEquals(1, \App\Models\Record::all()->count());
});
