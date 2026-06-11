<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
