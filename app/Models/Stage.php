<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'position'];

    public function deals()
    {
        return $this->hasMany(Deal::class)->orderBy('position', 'asc')->orderBy('created_at', 'desc');
    }
}
