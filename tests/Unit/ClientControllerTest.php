<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Client;
use App\Models\User;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if all clients are listed.
     */
    public function testItListsAllClients()
    {
        Client::factory()->count(5)->create();

        $response = $this->actingAsUser()->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'cpf', 'email', 'phone', 'created_at', 'updated_at']
            ]);
    }

    /**
     * Test if a client is retrieved with the sales associated.
     */
    public function testItRetrievesClientWithSales()
    {
        $client = Client::factory()->hasSales(3)->create();

        $response = $this->actingAsUser()->getJson("/api/clients/{$client->id}/sales");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Sales retrieved successfully.')
            ->assertJsonStructure([
                'status', 'message', 'data' => [
                    'id', 'name', 'cpf', 'email', 'phone', 'created_at', 'updated_at', 'sales' => [
                        '*' => ['id', 'created_at', 'updated_at']
                    ]
                ]
            ]);
    }

    /**
     * Test if a client is not found.
     */
    public function testItHandlesClientNotFound()
    {
        $response = $this->actingAsUser()->getJson('/api/clients/999/sales');

        $response->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Client not found.');
    }

    /**
     * Test if a client is created successfully.
     */
    public function testItCreatesAClientWithAddress()
    {
        $data = [
            'name' => 'Jane Doe',
            'cpf' => '12345678901',
            'email' => 'janedoe@example.com',
            'phone' => '12991234567',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighbourhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'postal_code' => '01000000'
            ]
        ];

        $response = $this->actingAsUser()->postJson('/api/clients', $data);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Client created successfully.')
            ->assertJsonStructure(['status', 'message', 'data' => ['id', 'name', 'address']]);
    }

    /**
     * Test if a client is not created with invalid data.
     */
    public function testItHandlesInvalidClientData()
    {
        $response = $this->actingAsUser()->postJson('/api/clients', []);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test if a client is updated successfully.
     */
    public function testItUpdatesAClient()
    {
        $client = Client::factory()->create();
        $data = ['name' => 'Updated Name'];

        $response = $this->actingAsUser()->putJson("/api/clients/{$client->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Client updated successfully.');
        $this->assertDatabaseHas('clients', ['id' => $client->id, 'name' => 'Updated Name']);
    }

    /**
     * Test if it returns the sales of a client by month and year.
     */
    public function testItReturnsSalesByMonthAndYear()
    {
        $client = Client::factory()->hasSales(5)->create();
        $sale = $client->sales()->first();
        $sale->created_at = '2024-11-01';
        $sale->save();

        $response = $this->actingAsUser()->getJson("/api/clients/{$client->id}/sales/2024/11");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['status', 'message', 'data' => ['sales']]);
    }

    /**
     * Test if it handles invalid month or year.
     */
    public function testItHandlesInvalidMonthOrYear()
    {
        $response = $this->actingAsUser()->getJson('/api/clients/1/sales/abcd/99');

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Invalid month or year provided.');
    }

    private function actingAsUser()
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'api');
    }
}
