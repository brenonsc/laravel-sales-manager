<?php

namespace Tests\Unit;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if it lists all sales.
     */
    public function testItListsAllSales()
    {
        $this->actingAsUser();

        $sales = Sale::factory()->count(5)->create();

        $response = $this->getJson('/api/sales');

        $response->assertOk()
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'client_id',
                    'product_id',
                    'quantity',
                    'unit_price',
                    'total_price',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * Test if it creates a sale.
     */
    public function testItCreatesSale()
    {
        $this->actingAsUser();

        $product = Product::factory()->create(['quantity' => 10, 'is_active' => true]);
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ];

        $response = $this->postJson('/api/sales', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'client_id',
                    'product_id',
                    'quantity',
                    'unit_price',
                    'total_price',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('sales', [
            'client_id' => $data['client_id'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'quantity' => 7,
        ]);
    }

    /**
     * Test if it fails to create a sale if the product has not enough stock.
     */
    public function testItFailsToCreateSaleIfNotEnoughStock()
    {
        $this->actingAsUser();

        $product = Product::factory()->create(['quantity' => 2]);
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ];

        $response = $this->postJson('/api/sales', $data);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Not enough stock available']);
    }

    /**
     * Test if it fails to create a sale if the product is not active.
     */
    public function testItFailsToCreateSaleIfProductIsNotActive()
    {
        $this->actingAsUser();

        $product = Product::factory()->create(['is_active' => false]);
        $client = Client::factory()->create();

        $data = [
            'client_id' => $client->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ];

        $response = $this->postJson('/api/sales', $data);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Product is not available']);
    }

    private function actingAsUser()
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'api');
    }
}

