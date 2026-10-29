<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractComment extends Model
{
    protected $fillable = [
        'contract_id',
        'user_id',
        'comment',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contracts::class, 'contract_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
