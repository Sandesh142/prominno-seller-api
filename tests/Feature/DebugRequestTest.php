<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Seller;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DebugRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_product_without_json_header()
    {
        $seller = Seller::create([
            'name' => 'Test Seller',
            'email' => 'test@seller.com',
            'password' => Hash::make('password'),
            'mobile_no' => '123', 'country' => 'c', 'state' => 's', 'skills' => [], 'role' => 'seller'
        ]);
        $token = $seller->createToken('test')->plainTextToken;

        $payload = [
            "name" => "Mouse",
            "description" => "Wireless Mouse",
            "brands" => [
                [
                    "name" => "Lenovo",
                    "detail" => "Dell Mouse",
                    "price" => 1000,
                    "image" => "C:\Users\Admin\Pictures\Annotation 2025-12-16 110130.jpg"
                ]
            ]
        ];

        // Sending as raw POST without trying to encode as JSON or set headers automatically (using post() instead of postJson())
        // BUT post() does send as form-data usually.
        // To mimic "raw body without header", we might need more low level or just assert on `post` behavior with array.
        
        // If user sends raw JSON string but no Content-Type header, but DOES expect JSON response:
        $response = $this->call('POST', 'api/seller/products', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'HTTP_ACCEPT' => 'application/json'
        ], json_encode($payload));
        
        // Expecting 422 because Content-Type is not application/json, so request->all() is empty
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_can_create_product_with_json_header()
    {
        $seller = Seller::create([
            'name' => 'Test Seller 2',
            'email' => 'test2@seller.com',
            'password' => Hash::make('password'),
            'mobile_no' => '123', 'country' => 'c', 'state' => 's', 'skills' => [], 'role' => 'seller'
        ]);
        $token = $seller->createToken('test')->plainTextToken;

        $payload = [
            "name" => "Mouse",
            "description" => "Wireless Mouse",
            "brands" => [
                [
                    "name" => "Lenovo",
                    "detail" => "Dell Mouse",
                    "price" => 1000,
                    "image" => "test.jpg" // string path
                ]
            ]
        ];

        // Using postJson sets Accept: application/json and Content-Type: application/json
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
             ->postJson('api/seller/products', $payload);

        $response->assertStatus(201);
    }
}
