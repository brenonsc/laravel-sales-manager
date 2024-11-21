<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cpf',
        'email',
        'phone',
    ];

    public function address()
    {
        return $this->hasOne(Address::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
