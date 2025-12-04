<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'company',
        'value',
        'stage_id',
        'contact_name',
        'contact_email',
        'notes',
        'position'
    ];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}
