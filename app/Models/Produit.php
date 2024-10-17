<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = ['libelle', 'description', 'image', 'prix', 'statut'];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}

