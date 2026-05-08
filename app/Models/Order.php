<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\RecordFactory> */
    use HasFactory;

    protected $casts = [
        'payment_summarized' => 'boolean',
    ];

    protected $fillable = [
        'total_price',
        'status',
        'user_id',
        'registration_plates',
        'payment_summarized',
        'session_id'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
