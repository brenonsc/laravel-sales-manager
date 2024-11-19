<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
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
}
