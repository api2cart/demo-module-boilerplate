<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FrontendTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHomepageTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/home' );
        $response->assertStatus(200);

    }


    public function testStoresTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/stores' );
        $response->assertStatus(200);

    }

    public function testOrdersTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/orders' );
        $response->assertStatus(200);

    }

    public function testCustomersTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/customers' );
        $response->assertStatus(200);

    }

    public function testProductsTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/products' );
        $response->assertStatus(200);

    }

    public function testCategoriesTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/categories' );
        $response->assertStatus(200);

    }

}
