<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Record extends Model
{
    /** @use HasFactory<\Database\Factories\RecordFactory> */
    use HasFactory;

    protected $guarded = [];


    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /*public function parking_space(): BelongsTo {
        return $this->belongsTo(ParkingSpace::class);
    }*/
}
