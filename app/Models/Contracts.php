<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Livewire\WithFileUploads;

class Contracts extends Model
{
    protected $guarded = [];

    public function departmentInfo()
    {
        return $this->belongsTo(Departments::class, 'department_assigned');
    }
}
