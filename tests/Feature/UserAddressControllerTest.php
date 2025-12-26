<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UserAddressControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['id' => 'address_test']);
        $this->tenant->domains()->create(['domain' => 'addresstest.localhost']);
    }

    protected function initializeTenant(): void
    {
        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);
        });
    }

    /** @test */
    public function test_addresses_index_requires_authentication()
    {
        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
        });

        $response = $this->get('http://addresstest.localhost/addresses');
        $response->assertRedirect();
    }

    /** @test */
    public function test_addresses_index_shows_user_addresses()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            // Create some addresses
            UserAddress::create([
                'user_id' => $this->user->id,
                'label' => 'Home',
                'recipient_name' => 'John Doe',
                'phone' => '08123456789',
                'address_line_1' => 'Jl. Home',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->get('/addresses');

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) =>
                $page->component('Addresses/Index')
                    ->has('addresses')
            );
        });
    }

    /** @test */
    public function test_can_create_new_address()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->post('/addresses', [
                    'label' => 'Office',
                    'recipient_name' => 'John Doe',
                    'phone' => '08123456789',
                    'address_line_1' => 'Jl. Office No. 1',
                    'address_line_2' => 'Lantai 5',
                    'city' => 'Jakarta Selatan',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '12345',
                    'is_default' => false,
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');

            $this->assertDatabaseHas('user_addresses', [
                'user_id' => $this->user->id,
                'label' => 'Office',
                'recipient_name' => 'John Doe',
            ]);
        });
    }

    /** @test */
    public function test_first_address_becomes_default()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->post('/addresses', [
                    'label' => 'Home',
                    'recipient_name' => 'John Doe',
                    'phone' => '08123456789',
                    'address_line_1' => 'Jl. Test',
                    'city' => 'Jakarta',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '12345',
                    'is_default' => false, // Even if false, first should be default
                ]);

            $address = UserAddress::where('user_id', $this->user->id)->first();
            $this->assertTrue($address->is_default);
        });
    }

    /** @test */
    public function test_can_update_address()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $address = UserAddress::create([
                'user_id' => $this->user->id,
                'label' => 'Home',
                'recipient_name' => 'Old Name',
                'phone' => '08123456789',
                'address_line_1' => 'Old Address',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->put('/addresses/' . $address->id, [
                    'label' => 'Office',
                    'recipient_name' => 'New Name',
                    'phone' => '08987654321',
                    'address_line_1' => 'New Address',
                    'city' => 'Bandung',
                    'province' => 'Jawa Barat',
                    'postal_code' => '54321',
                ]);

            $response->assertRedirect();

            $address->refresh();
            $this->assertEquals('Office', $address->label);
            $this->assertEquals('New Name', $address->recipient_name);
            $this->assertEquals('Bandung', $address->city);
        });
    }

    /** @test */
    public function test_cannot_update_other_user_address()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $anotherUser = User::create([
                'name' => 'Another User',
                'email' => 'another@test.com',
                'password' => bcrypt('password'),
            ]);

            $address = UserAddress::create([
                'user_id' => $anotherUser->id,
                'label' => 'Home',
                'recipient_name' => 'Another Name',
                'phone' => '08123456789',
                'address_line_1' => 'Another Address',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->put('/addresses/' . $address->id, [
                    'recipient_name' => 'Hacked Name',
                ]);

            $response->assertStatus(403);
        });
    }

    /** @test */
    public function test_can_delete_address()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $address = UserAddress::create([
                'user_id' => $this->user->id,
                'label' => 'Home',
                'recipient_name' => 'John Doe',
                'phone' => '08123456789',
                'address_line_1' => 'Jl. Test',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->delete('/addresses/' . $address->id);

            $response->assertRedirect();

            $this->assertDatabaseMissing('user_addresses', [
                'id' => $address->id,
            ]);
        });
    }

    /** @test */
    public function test_can_set_address_as_default()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $address1 = UserAddress::create([
                'user_id' => $this->user->id,
                'label' => 'Home',
                'recipient_name' => 'John',
                'phone' => '08123456789',
                'address_line_1' => 'Home address',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $address2 = UserAddress::create([
                'user_id' => $this->user->id,
                'label' => 'Office',
                'recipient_name' => 'John',
                'phone' => '08123456789',
                'address_line_1' => 'Office address',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => false,
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->post('/addresses/' . $address2->id . '/set-default');

            $response->assertRedirect();

            $address1->refresh();
            $address2->refresh();

            $this->assertFalse($address1->is_default);
            $this->assertTrue($address2->is_default);
        });
    }

    /** @test */
    public function test_address_validation_works()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->post('/addresses', [
                    'label' => '', // Required
                    'recipient_name' => '', // Required
                    // Missing other required fields
                ]);

            $response->assertSessionHasErrors([
                'label',
                'recipient_name',
                'phone',
                'address_line_1',
                'city',
                'province',
                'postal_code',
            ]);
        });
    }

    /** @test */
    public function test_phone_validation()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'addresstest.localhost')
                ->post('/addresses', [
                    'label' => 'Home',
                    'recipient_name' => 'John',
                    'phone' => '123', // Too short
                    'address_line_1' => 'Test',
                    'city' => 'Jakarta',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '12345',
                ]);

            $response->assertSessionHasErrors(['phone']);
        });
    }
}
