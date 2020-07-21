<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BusinessCasesFrontendTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testImportOrdersAutomationTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/businessCases/automatic_email_sending' );
        $response->assertStatus(200);

    }

    public function testAutomaticEmailSendingTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/businessCases/automatic_email_sending' );
        $response->assertStatus(200);

    }

    public function testAutomaticPriceUpdatingTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/businessCases/automatic_price_updating' );
        $response->assertStatus(200);

    }


    public function testAbandonedCartRecoveryTest()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
            ->get( '/businessCases/abandoned_cart_recovery' );
        $response->assertStatus(200);

    }


}
