<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'street',
        'number',
        'complement',
        'neighbourhood',
        'city',
        'state',
        'postal_code',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
