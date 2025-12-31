<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SellerProductFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_seller()
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('api/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token']);

        $token = $response->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('api/admin/sellers', [
                'name' => 'Seller One',
                'email' => 'seller@example.com',
                'mobile_no' => '1234567890',
                'country' => 'test_country',
                'state' => 'test_state',
                'skills' => ['php', 'files'],
                'password' => 'password',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sellers', ['email' => 'seller@example.com']);
    }

    public function test_seller_can_create_product()
    {
        $seller = Seller::create([
            'name' => 'Seller One',
            'email' => 'seller@example.com',
            'mobile_no' => '1234567890',
            'country' => 'test_country',
            'state' => 'test_state',
            'skills' => ['php'],
            'password' => Hash::make('password'),
            'role' => 'seller',
        ]);

        $params = [
            'name' => 'Test Product',
            'description' => 'Description',
            'brands' => [
                [
                    'name' => 'Brand A',
                    'detail' => 'Details A',
                    'image' => 'path/to/image.jpg',
                    'price' => 100,
                ],
                [
                    'name' => 'Brand B',
                    'detail' => 'Details B',
                    'image' => 'path/to/image.jpg',
                    'price' => 200,
                ]
            ]
        ];

        Sanctum::actingAs($seller, ['*'], 'web'); // Or however auth is set up, likely api guard
        // The routes use auth:sanctum

        $token = $seller->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
             ->postJson('api/seller/products', $params);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
        $this->assertDatabaseCount('brands', 2);
    }

    public function test_seller_can_listing_and_delete_product()
    {
        $seller = Seller::create([
            'name' => 'Seller One',
            'email' => 'seller@example.com',
            'mobile_no' => '1234567890',
            'country' => 'test_country',
            'state' => 'test_state',
            'skills' => ['php'],
            'password' => Hash::make('password'),
            'role' => 'seller',
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'name' => 'My Product',
            'description' => 'Desc'
        ]);

        Brand::create([
            'product_id' => $product->id,
            'name' => 'Brand X',
            'price' => 500
        ]);

        $token = $seller->createToken('test')->plainTextToken;

        // Listing
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('api/seller/products');
        
        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'My Product']);

        // PDF (Checking status, usually 200 but might fail if view issues, check simple response)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/seller/products/'.$product->id.'/pdf');
        
        $response->assertStatus(200);

        // Delete
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('api/seller/products/'.$product->id);
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_seller_cannot_delete_others_product()
    {
        $seller1 = Seller::create([
            'name' => 'Seller One',
            'email' => 's1@example.com',
            'mobile_no' => '1234567890',
            'country' => 'c',
            'state' => 's',
            'skills' => [],
            'password' => Hash::make('password'),
            'role' => 'seller',
        ]);

        $seller2 = Seller::create([
            'name' => 'Seller Two',
            'email' => 's2@example.com',
            'mobile_no' => '1234567890',
            'country' => 'c',
            'state' => 's',
            'skills' => [],
            'password' => Hash::make('password'),
            'role' => 'seller',
        ]);

        $product = Product::create([
            'seller_id' => $seller1->id,
            'name' => 'S1 Product',
            'description' => 'Desc'
        ]);

        $token2 = $seller2->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->deleteJson('api/seller/products/'.$product->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
}
