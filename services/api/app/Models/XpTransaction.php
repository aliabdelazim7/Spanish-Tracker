<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'source_type',
        'source_id',
        'idempotency_key',
        'description',
    ];

    protected $casts = [
        'amount' => 'integer',
        'source_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
