<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'cpf' => $this->faker->unique()->numerify('###########'),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
        ];
    }

    public function withAddress()
    {
        return $this->has(Address::factory(), 'address');
    }
}

