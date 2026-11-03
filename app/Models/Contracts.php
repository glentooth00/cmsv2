<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\WithFileUploads;


class Contracts extends Model
{
    protected $guarded = [];

    public function departmentInfo()
    {
        return $this->belongsTo(Departments::class, 'department_assigned');
    }

    public function procInfo()
    {
        return $this->belongsTo(Procurement::class, 'proc_mode');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ContractComment::class, 'contract_id')
            ->latest();
    }
}
