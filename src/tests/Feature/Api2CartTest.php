<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Api2Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class Api2CartTest extends TestCase
{
    private $api2cart;
    private $store_key = "1316ad9a66ac871ce46a3d59005acc9c";

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->api2cart = new Api2Cart(true);
        parent::__construct($name, $data, $dataName);
    }



    public function testCheckConnection()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $result = $this->api2cart->checkConnection( $user->api2cart_key );
        $this->assertTrue( $result );

    }

    public function testCartsList()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $result = $this->api2cart->getCartList();

        $this->assertIsArray( $result );
        $this->assertArrayHasKey('url', $result[0]);


//        Log::debug( print_r($result,1) );
//        $this->assertTrue(true);

    }


    public function testOrdersList()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $result = $this->api2cart->getOrderList( $this->store_key );

        $this->assertIsArray( $result );
        $this->assertEquals( 0, $result['return_code'] );
        $this->assertArrayHasKey('result', $result);
    }

    public function testProductsList()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $result = $this->api2cart->getProductList( $this->store_key );

        $this->assertIsArray( $result );
        $this->assertEquals( 0, $result['return_code'] );
        $this->assertArrayHasKey('result', $result);
    }

    public function testCustomersList()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $result = $this->api2cart->getCustomerList( $this->store_key );

        $this->assertIsArray( $result );
        $this->assertEquals( 0, $result['return_code'] );
        $this->assertArrayHasKey('result', $result);
    }

    public function testCategoriesList()
    {
        $user = factory(User::class)->make();
        $this->actingAs($user);

        $result = $this->api2cart->getCategoryList( $this->store_key );

        $this->assertIsArray( $result );
        $this->assertEquals( 0, $result['return_code'] );
        $this->assertArrayHasKey('result', $result);
    }
}
