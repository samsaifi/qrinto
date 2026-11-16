<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regression tests for the code-review fixes on the store profile form and
 * the route middleware changes (checkStore / throttle).
 *
 * The full migration set does not run on sqlite, so only minimal tables are built.
 */
class StoreProfileSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        \Illuminate\Support\Facades\URL::forceRootUrl('http://localhost');

        Schema::create('stores', function (Blueprint $t) {
            $t->id();
            foreach (['store_name', 'store_code', 'owner_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'country', 'logo'] as $c) {
                $t->string($c)->nullable();
            }
            $t->decimal('lat', 10, 7)->nullable();
            $t->decimal('lon', 10, 7)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            foreach (['name', 'email', 'password', 'role', 'phone', 'remember_token'] as $c) {
                $t->string($c)->nullable();
            }
            $t->unsignedBigInteger('store_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        DB::table('stores')->insert(['id' => 1, 'store_name' => 'Shop', 'store_code' => 'SHOP1', 'owner_name' => 'Owner',
            'email' => 'old@example.test', 'phone' => '1', 'address' => 'A', 'city' => 'C', 'state' => 'S', 'zip_code' => 'Z', 'country' => 'India']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Owner', 'email' => 'old@example.test',
            'password' => Hash::make('secret-pass'), 'role' => 'store', 'store_id' => 1]);

        Storage::fake('public');
        $this->withoutMiddleware(AdminMiddleware::class);
    }

    private function form(array $over = []): array
    {
        return $over + ['store_name' => 'Shop', 'store_code' => 'SHOP1', 'owner_name' => 'Owner',
            'email' => 'old@example.test', 'phone' => '1', 'address' => 'A', 'city' => 'C', 'state' => 'S', 'zip_code' => 'Z'];
    }

    private function putProfile(array $data)
    {
        return $this->actingAs(User::find(1))->put('/store/profile', $data);
    }

    public function test_svg_logo_is_rejected(): void
    {
        $svg = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->putProfile($this->form(['logo' => $svg]))->assertSessionHasErrors('logo');
        $this->assertNull(DB::table('stores')->value('logo'));
    }

    public function test_email_change_requires_current_password(): void
    {
        $this->putProfile($this->form(['email' => 'new@example.test']))->assertSessionHasErrors('current_password');
        $this->putProfile($this->form(['email' => 'new@example.test', 'current_password' => 'wrong']))->assertSessionHasErrors('current_password');
        $this->assertSame('old@example.test', DB::table('users')->value('email'));

        $this->putProfile($this->form(['email' => 'new@example.test', 'current_password' => 'secret-pass']))->assertSessionHasNoErrors();
        $this->assertSame('new@example.test', DB::table('users')->value('email'));
    }

    public function test_password_change_requires_current_password_and_rotates_remember_token(): void
    {
        $this->putProfile($this->form(['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass']))
            ->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('secret-pass', DB::table('users')->value('password')));

        $this->putProfile($this->form(['password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass', 'current_password' => 'secret-pass']))
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('brand-new-pass', DB::table('users')->value('password')));
        $this->assertNotEmpty(DB::table('users')->value('remember_token'));
    }

    public function test_other_fields_save_without_current_password(): void
    {
        $this->putProfile($this->form(['city' => 'New City']))->assertSessionHasNoErrors();
        $this->assertSame('New City', DB::table('stores')->value('city'));
    }

    public function test_payment_capture_and_confirmation_routes_do_not_require_a_store(): void
    {
        foreach (['flow.paypal.capture', 'flow.cart-checkout.paypal.capture', 'flow.confirmation', 'canonical.order.confirmation'] as $name) {
            $this->assertNotContains('checkStore', Route::getRoutes()->getByName($name)->gatherMiddleware(), $name);
        }
        // Starting a payment still needs a selected store.
        $this->assertContains('checkStore', Route::getRoutes()->getByName('flow.paypal.create')->gatherMiddleware());
    }

    public function test_business_card_download_is_throttled(): void
    {
        $this->assertContains('throttle:30,1', Route::getRoutes()->getByName('business-card.download')->gatherMiddleware());
    }
}
