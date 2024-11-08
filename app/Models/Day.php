<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    // Especificamos los campos que pueden ser llenados masivamente
    protected $fillable = ['title', 'ninth_id'];

    // Relación inversa: Un Day pertenece a un Ninth
    public function ninth()
    {
        return $this->belongsTo(Ninth::class);
    }
}