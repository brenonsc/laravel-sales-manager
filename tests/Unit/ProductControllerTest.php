<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if it can list active products.
     */
    public function testItCanListActiveProducts()
    {
        $this->actingAsUser();

        $products = Product::factory()->count(3)->create(['is_active' => true]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => ['id', 'sku', 'name', 'price', 'quantity', 'is_active', 'created_at', 'updated_at']
            ]);
    }

    /**
     * Test if it can create product.
     */
    public function testItCanCreateProduct()
    {
        $this->actingAsUser();

        $data = Product::factory()->make()->toArray();

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonFragment(['message' => 'Product created successfully.']);

        $this->assertDatabaseHas('products', ['sku' => $data['sku']]);
    }

    /**
     * Test if it cannot create a product with invalid data.
     */
    public function testItCannotCreateAProductWithInvalidData()
    {
        $this->actingAsUser();

        $data = Product::factory()->make(['name' => ''])->toArray();

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonFragment(['message' => 'Validation failed.']);
    }

    /**
     * Test if it can retrieve a product.
     */
    public function testItCanRetrieveAProduct()
    {
        $this->actingAsUser();

        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonFragment(['message' => 'Product retrieved successfully.']);
    }

    /**
     * Test if it returns 404 if product not found.
     */
    public function testItReturns404IfProductNotFound()
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonFragment(['message' => 'Product not found.']);
    }

    /**
     * Test if it can update a product.
     */
    public function testItCanUpdateAProduct()
    {
        $this->actingAsUser();

        $product = Product::factory()->create();

        $data = Product::factory()->make()->toArray();

        $response = $this->putJson("/api/products/{$product->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'success'])
            ->assertJsonFragment(['message' => 'Product updated successfully.']);

        $this->assertDatabaseHas('products', ['sku' => $data['sku']]);
    }

    /**
     * Test if it returns 404 if updating a non-existent product.
     */
    public function testItReturns404IfUpdatingANonExistentProduct()
    {
        $this->actingAsUser();  // Autentica o usuário antes de fazer a requisição

        $data = Product::factory()->make()->toArray();

        $response = $this->putJson('/api/products/999', $data);

        $response->assertStatus(404)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonFragment(['message' => 'Product not found.']);
    }

    /**
     * Test if it can delete a product.
     */
    public function testItCanDeleteAProduct()
    {
        $this->actingAsUser();

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
    }

    /**
     * Test if it returns 404 if deleting a non-existent product.
     */
    public function testItReturns404IfDeletingANonExistentProduct()
    {
        $this->actingAsUser();

        $response = $this->deleteJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJsonFragment(['status' => 'error'])
            ->assertJsonFragment(['message' => 'Product not found.']);
    }

    private function actingAsUser()
    {
        $user = User::factory()->create();
        return $this->actingAs($user, 'api');
    }
}
