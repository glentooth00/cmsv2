<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractCommentRead extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}