<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ninth extends Model
{
    use HasFactory;

    // Especificamos los campos que pueden ser llenados masivamente
    protected $fillable = ['title', 'prayer_every_day'];

    // Relación uno a muchos (Un Ninth puede tener muchos Days)
    public function days()
    {
        return $this->hasMany(Day::class);
    }
}