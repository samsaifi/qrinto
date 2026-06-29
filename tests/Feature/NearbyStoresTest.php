<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NearbyStoresTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_store_page_renders_without_geolocation()
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/find-store');
        $response->assertStatus(200);
        $response->assertViewHas('stores');
        $response->assertViewHas('nearbyStores');
    }

    public function test_find_store_ajax_with_coordinates_returns_matched_stores()
    {
        $this->withoutExceptionHandling();
        // 1. Create dummy stores
        $store1 = Store::forceCreate([
            'name' => 'Times Square Store',
            'store_name' => 'Times Square Store',
            'store_code' => 'STORE-NY-01',
            'owner_name' => 'John Doe',
            'address' => '1515 Broadway',
            'phone' => '212-555-0199',
            'zip' => '10036',
            'zip_code' => '10036',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'United States',
            'is_active' => true,
        ]);

        $store2 = Store::forceCreate([
            'name' => 'Los Angeles Store',
            'store_name' => 'Los Angeles Store',
            'store_code' => 'STORE-LA-02',
            'owner_name' => 'Jane Smith',
            'address' => '6000 Sunset Blvd',
            'phone' => '323-555-0144',
            'zip' => '90001',
            'zip_code' => '90001',
            'city' => 'Toronto',
            'state' => 'ON',
            'country' => 'Canada',
            'is_active' => true,
        ]);

        // Mock Nominatim response for Times Square coordinates
        Http::fake([
            'nominatim.openstreetmap.org/*' => Http::response([
                'address' => [
                    'city' => 'New York',
                    'postcode' => '10036',
                    'state' => 'New York',
                    'country' => 'United States',
                ]
            ], 200)
        ]);

        // AJAX request with coordinates
        $response = $this->getJson('/find-store?lat=40.7580&lon=-73.9855');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'stores');
        $response->assertJsonFragment([
            'store_name' => 'Times Square Store',
        ]);
    }
}
