<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\LaundryOrder;
use App\Models\LaundrySetting;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LaundryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_a_customer_and_ignores_privilege_fields(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jamie Customer',
            'username' => 'jamie',
            'email' => 'jamie@example.test',
            'phone' => '+639123456789',
            'password' => 'laundry-password',
            'password_confirmation' => 'laundry-password',
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'jamie',
            'role' => 'customer',
            'is_active' => true,
        ]);
    }

    public function test_users_can_sign_in_with_username_and_inactive_accounts_are_refused(): void
    {
        $user = User::factory()->create([
            'username' => 'laundry-staff',
            'password' => Hash::make('laundry-password'),
        ]);
        $user->role = 'staff';
        $user->save();

        $this->post('/login', [
            'username' => 'laundry-staff',
            'password' => 'laundry-password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        auth()->logout();
        $user->is_active = false;
        $user->save();

        $this->from('/login')->post('/login', [
            'username' => 'laundry-staff',
            'password' => 'laundry-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_customer_login_requires_a_fresh_four_second_human_check(): void
    {
        $customer = User::factory()->create([
            'username' => 'captcha-customer',
            'password' => Hash::make('laundry-password'),
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Human verification (required for customer accounts)')
            ->assertSee("I'm not a robot", false);

        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'captcha-customer',
            'password' => 'laundry-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('human_check');
        $this->assertGuest();

        $this->get(route('login'));
        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'captcha-customer',
            'password' => 'laundry-password',
            'human_check' => '1',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('human_check');
        $this->assertGuest();

        $this->get(route('login'));
        $this->travel(301)->seconds();
        $this->post(route('login.store'), [
            'username' => 'captcha-customer',
            'password' => 'laundry-password',
            'human_check' => '1',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('human_check');
        $this->assertGuest();

        $this->get(route('login'));
        $this->travel(4)->seconds();
        $this->post(route('login.store'), [
            'username' => 'captcha-customer',
            'password' => 'laundry-password',
            'human_check' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($customer);
    }

    public function test_unknown_usernames_cannot_bypass_customer_captcha_requirement(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'not-a-real-user',
            'password' => 'wrong-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('human_check');

        $this->get(route('login'));
        $this->travel(4)->seconds();
        $this->from(route('login'))->post(route('login.store'), [
            'username' => 'not-a-real-user',
            'password' => 'wrong-password',
            'human_check' => '1',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('username')
            ->assertSessionDoesntHaveErrors('human_check');

        $this->assertGuest();
    }

    public function test_customers_cannot_open_admin_pages_or_view_another_customers_order(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $this->assertTrue($customer->fresh()->is_active);
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        $order = $this->createOrder($otherCustomer, $staff);

        $this->signInAs($customer);
        $this->get(route('admin.users.index'))->assertForbidden();
        $this->delete(route('admin.backups.destroy', 'laundry_20261105_120000_a1b2c3.sqlite'))->assertForbidden();

        $this->get(route('orders.show', $order))->assertNotFound();
    }

    public function test_customer_can_request_an_appointment_and_admin_can_approve_it(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $settings = LaundrySetting::current();
        $settings->update([
            'wash_price_per_kg' => 26,
            'press_price_per_kg' => 35,
            'full_service_addon_per_kg' => 15,
        ]);

        $this->signInAs($customer);
        $this->from(route('appointments.index'))->post(route('appointments.store'), [
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'preferred_staff_id' => 999999,
            'estimated_weight' => 3.5,
            'service_type' => 'deluxe',
        ])->assertSessionHasErrors(['preferred_staff_id', 'service_type']);
        $this->assertSame(0, Appointment::query()->count());

        $preferredStaff = User::factory()->create(['role' => 'staff']);
        $this->post(route('appointments.store'), [
            'preferred_staff_id' => $preferredStaff->id,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'estimated_weight' => 3.5,
            'service_type' => 'full',
            'press_service' => true,
            'customer_note' => 'Please separate towels.',
        ])->assertRedirect()
            ->assertSessionHas('status', 'Appointment requested. You will be notified after it is reviewed.');

        $appointment = Appointment::query()->sole();
        $this->assertSame('pending', $appointment->status);
        $this->assertSame('full', $appointment->service_type);
        $this->assertSame($preferredStaff->id, $appointment->preferred_staff_id);

        $this->post(route('logout'));
        $this->signInAs($admin);
        $this->get(route('appointments.index'))
            ->assertOk()
            ->assertSee('Full — folded & bagged')
            ->assertSee($preferredStaff->name)
            ->assertSee('₱266.00');

        $this->patch(route('appointments.review', $appointment), [
            'status' => 'approved',
            'admin_note' => 'Confirmed for tomorrow.',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $customer->id,
            'title' => 'Appointment approved',
        ]);

        $this->post(route('appointments.check-in', $appointment))
            ->assertRedirect(route('orders.create', ['appointment' => $appointment->id]));
        $this->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'weight' => 2,
            'service_type' => 'partial',
            'press_service' => false,
        ])->assertRedirect();

        $order = LaundryOrder::query()->sole();
        $this->assertSame('full', $order->service_type);
        $this->assertTrue($order->press_service);
        $this->assertSame('15.00', $order->full_service_addon_per_kg);
        $this->assertSame('152.00', $order->total_amount);
        $this->assertSame('fulfilled', $appointment->fresh()->status);
    }

    public function test_appointment_preferred_staff_must_be_an_active_staff_account(): void
    {
        $customer = User::factory()->create();
        $inactiveStaff = User::factory()->create();
        $inactiveStaff->role = 'staff';
        $inactiveStaff->is_active = false;
        $inactiveStaff->save();
        $nonStaff = User::factory()->create();

        $this->signInAs($customer);
        foreach ([$inactiveStaff, $nonStaff] as $user) {
            $this->post(route('appointments.store'), [
                'preferred_staff_id' => $user->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'estimated_weight' => 3,
                'service_type' => 'partial',
            ])->assertSessionHasErrors('preferred_staff_id');
        }

        $this->assertSame(0, Appointment::query()->count());
    }

    public function test_staff_can_create_orders_with_price_snapshots_and_record_payments(): void
    {
        $customer = User::factory()->create(['phone' => null]);
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        $settings = LaundrySetting::current();
        $settings->update(['wash_price_per_kg' => 26, 'press_price_per_kg' => 35]);

        $this->signInAs($staff);
        $response = $this->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'weight' => 2,
            'service_type' => 'partial',
            'press_service' => true,
        ])->assertRedirect()
            ->assertSessionHas('status', 'Laundry order created.');

        $order = LaundryOrder::query()->sole();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('122.00', $order->total_amount);
        $this->assertSame('26.00', $order->wash_price_per_kg);
        $this->assertSame('partial', $order->service_type);
        $this->assertSame('0.00', $order->full_service_addon_per_kg);
        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $customer->id,
            'title' => 'Laundry received',
        ]);

        $this->post(route('orders.payments.store', $order), [
            'amount' => 50,
            'method' => 'gcash',
            'reference' => 'GC-1234',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'laundry_order_id' => $order->id,
            'amount' => 50,
            'method' => 'gcash',
        ]);

        $this->post(route('orders.payments.store', $order), [
            'amount' => 73,
            'method' => 'cash',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(1, Payment::query()->count());
    }

    public function test_order_status_must_follow_the_laundry_workflow(): void
    {
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        $order = $this->createOrder(User::factory()->create(), $staff);

        $this->signInAs($staff);
        $this->patch(route('orders.status', $order), [
            'status' => 'drying',
        ])->assertSessionHasErrors('status');

        $this->assertSame('received', $order->fresh()->status);

        $this->patch(route('orders.status', $order), ['status' => 'washing'])
            ->assertRedirect();
        $this->assertSame('washing', $order->fresh()->status);
    }

    public function test_partial_service_can_skip_folding_and_full_service_adds_its_per_kilogram_rate(): void
    {
        $customer = User::factory()->create(['phone' => null]);
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        LaundrySetting::current()->update(['wash_price_per_kg' => 26, 'full_service_addon_per_kg' => 15]);
        $partialOrder = $this->createOrder($customer, $staff);
        $partialOrder->update(['status' => 'drying', 'service_type' => 'partial']);

        $this->signInAs($staff);
        $this->patch(route('orders.status', $partialOrder), ['status' => 'folding'])
            ->assertSessionHasErrors('status');
        $this->patch(route('orders.status', $partialOrder), ['status' => 'ready_for_pickup'])
            ->assertRedirect();

        $this->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'weight' => 2,
            'service_type' => 'full',
        ])->assertRedirect();

        $fullOrder = LaundryOrder::query()->where('service_type', 'full')->latest('id')->firstOrFail();
        $this->assertSame('15.00', $fullOrder->full_service_addon_per_kg);
        $this->assertSame('82.00', $fullOrder->total_amount);
    }

    public function test_admin_can_create_staff_and_deactivate_customer_accounts(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $customer = User::factory()->create();
        $this->signInAs($admin);

        $this->post(route('admin.staff.store'), [
            'name' => 'Laundry Staff',
            'username' => 'laundry-staff',
            'email' => 'staff@example.test',
            'password' => 'staff-password',
            'password_confirmation' => 'staff-password',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'laundry-staff',
            'role' => 'staff',
        ]);

        $this->patch(route('admin.users.toggle', $customer))->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_pages_and_customer_dashboard_render(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $this->signInAs($admin);

        $this->get(route('dashboard'))->assertOk()->assertSee('Recent laundry records');
        $this->get(route('appointments.index'))->assertOk()->assertSee('Appointment requests');
        $this->get(route('orders.index'))->assertOk()->assertSee('Active and completed orders');
        $this->get(route('orders.create'))->assertOk()->assertSee('Record a laundry drop-off');
        $this->get(route('admin.users.index'))->assertOk()->assertSee('Create a staff account');
        $this->get(route('reports'))->assertOk()->assertSee('Order status breakdown');
        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Service pricing');
        $this->get(route('admin.backups.index'))->assertOk()->assertSee('Database backups');
        $this->get(route('profile.edit'))->assertOk()->assertSee('My profile');
    }

    public function test_navigation_shows_pending_appointment_count_only_to_staff_and_admins(): void
    {
        $customer = User::factory()->create();
        $pendingCustomer = User::factory()->create();
        $pendingCustomer->appointments()->create([
            'scheduled_at' => now()->addDay(),
            'estimated_weight' => 3,
            'service_type' => 'partial',
            'status' => 'pending',
        ]);
        $pendingCustomer->appointments()->create([
            'scheduled_at' => now()->addDays(2),
            'estimated_weight' => 2,
            'service_type' => 'full',
            'status' => 'pending',
        ]);
        $pendingCustomer->appointments()->create([
            'scheduled_at' => now()->addDays(3),
            'estimated_weight' => 1,
            'service_type' => 'partial',
            'status' => 'approved',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();

        $this->signInAs($admin);
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('aria-label="2 pending appointment requests"', false);

        $this->post(route('logout'));
        $this->signInAs($staff);
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('aria-label="2 pending appointment requests"', false);

        $this->post(route('logout'));
        $this->signInAs($customer);
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertDontSee('aria-label="2 pending appointment requests"', false);
    }

    public function test_staff_can_view_reports_and_customers_cannot(): void
    {
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        $customer = User::factory()->create(['role' => 'customer']);

        $this->signInAs($staff);
        $this->get(route('reports'))
            ->assertOk()
            ->assertSee('Order status breakdown');

        $this->post(route('logout'));
        $this->signInAs($customer);
        $this->get(route('reports'))->assertForbidden();
    }

    public function test_monthly_report_includes_counts_for_each_order_status(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $customer = User::factory()->create();
        $order = $this->createOrder($customer, $admin);
        $order->update(['status' => 'ready_for_pickup']);

        $this->signInAs($admin);
        $this->get(route('reports', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Ready For Pickup')
            ->assertSee('Order status breakdown');
    }

    public function test_customers_cannot_print_receipts_but_staff_can(): void
    {
        $customer = User::factory()->create();
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        $order = $this->createOrder($customer, $staff);

        $this->signInAs($customer);
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertDontSee('Print receipt');

        $this->post(route('logout'));
        $this->signInAs($staff);
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Print receipt');
    }

    public function test_admin_backup_listing_formats_file_sizes_without_intl(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $this->signInAs($admin);

        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $backupPath = $directory.DIRECTORY_SEPARATOR.'laundry_20990101_000000_'.bin2hex(random_bytes(3)).'.sqlite';
        File::put($backupPath, str_repeat('x', 2048));

        try {
            $this->get(route('admin.backups.index'))
                ->assertOk()
                ->assertSee('2.0 KB')
                ->assertSee('Delete');
        } finally {
            File::delete($backupPath);
        }
    }

    public function test_admin_can_delete_a_backup_and_invalid_names_are_not_deleted(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();
        $this->signInAs($admin);

        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $backupName = 'laundry_20990101_000000_'.bin2hex(random_bytes(3)).'.sqlite';
        $backupPath = $directory.DIRECTORY_SEPARATOR.$backupName;
        $unrelatedPath = $directory.DIRECTORY_SEPARATOR.'not-a-laundry-backup.sqlite';
        File::put($backupPath, 'backup');
        File::put($unrelatedPath, 'keep');

        try {
            $this->delete(route('admin.backups.destroy', $backupName))
                ->assertRedirect()
                ->assertSessionHas('status', 'Database backup deleted: '.$backupName);
            $this->assertFileDoesNotExist($backupPath);

            $this->delete(route('admin.backups.destroy', 'not-a-laundry-backup.sqlite'))
                ->assertNotFound();
            $this->assertFileExists($unrelatedPath);
        } finally {
            File::delete($backupPath, $unrelatedPath);
        }
    }

    public function test_customer_can_change_password_only_after_proving_the_current_password(): void
    {
        $customer = User::factory()->create();
        $this->signInAs($customer);

        $this->put(route('profile.update'), [
            'name' => $customer->name,
            'username' => 'laundry-customer',
            'email' => $customer->email,
            'current_password' => 'wrong-password',
            'password' => 'new-laundry-password',
            'password_confirmation' => 'new-laundry-password',
        ])->assertSessionHasErrors('current_password');

        $this->put(route('profile.update'), [
            'name' => $customer->name,
            'username' => 'laundry-customer',
            'email' => $customer->email,
            'current_password' => 'password',
            'password' => 'new-laundry-password',
            'password_confirmation' => 'new-laundry-password',
        ])->assertSessionHas('status', 'Your profile has been updated.');

        $this->assertTrue(Hash::check('new-laundry-password', $customer->fresh()->password));
    }

    private function createOrder(User $customer, User $staff): LaundryOrder
    {
        return LaundryOrder::query()->create([
            'order_number' => 'CL-TEST-'.fake()->unique()->numerify('######'),
            'customer_id' => $customer->id,
            'received_by' => $staff->id,
            'weight' => 2,
            'press_service' => false,
            'service_type' => 'full',
            'wash_price_per_kg' => 26,
            'press_price_per_kg' => 35,
            'full_service_addon_per_kg' => 0,
            'total_amount' => 52,
            'status' => 'received',
        ]);
    }

    private function signInAs(User $user): void
    {
        $credentials = [
            'username' => $user->username,
            'password' => 'password',
        ];

        if ($user->fresh()->isCustomer()) {
            $this->get(route('login'));
            $this->from(route('login'))->post(route('login.store'), [
                'username' => $user->username,
                'password' => 'password',
            ])->assertSessionHasErrors('human_check');

            $this->get(route('login'));
            $this->travel(4)->seconds();
            $credentials['human_check'] = '1';
        }

        $this->post(route('login.store'), $credentials)->assertRedirect(route('dashboard'));
    }
}
