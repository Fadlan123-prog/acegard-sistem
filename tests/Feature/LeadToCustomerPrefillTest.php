<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\LeadConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Konversi lead -> customer tanpa quotation: form customer terisi dari data lead. */
class LeadToCustomerPrefillTest extends TestCase
{
    use RefreshDatabase;

    private function lead(Branch $branch, array $over = []): Lead
    {
        return Lead::create(array_merge([
            'branch_id' => $branch->id, 'name' => 'Budi Lead', 'phone' => '081234567890', 'email' => 'budi@lead.test',
            'address' => 'Jl. Lead 1', 'city' => 'Jambi', 'source' => 'walk_in', 'category' => 'car_film',
            'vehicle_brand' => 'Toyota', 'vehicle_model' => 'Fortuner', 'vehicle_year' => 2022, 'status' => 'new',
        ], $over));
    }

    public function test_car_customer_form_is_prefilled_from_a_lead_without_quotation(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $lead = $this->lead($branch);

        $this->actingAs($admin)->get(route('customer.create', ['from_lead' => $lead->id]))
            ->assertOk()
            ->assertSee('Budi Lead')->assertSee('081234567890')->assertSee('budi@lead.test')
            ->assertSee('Jl. Lead 1')->assertSee('Jambi')->assertSee('Fortuner')
            ->assertSee('name="from_lead"', false);
    }

    public function test_building_and_ppf_forms_are_prefilled_too(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $lead = $this->lead($branch, ['category' => 'ppf']);

        $this->actingAs($admin)->get(route('customer.building.create', ['from_lead' => $lead->id]))
            ->assertOk()->assertSee('Budi Lead')->assertSee('Jl. Lead 1')->assertSee('name="from_lead"', false);
        $this->actingAs($admin)->get(route('ppf-customers.create', ['from_lead' => $lead->id]))
            ->assertOk()->assertSee('Budi Lead')->assertSee('Fortuner')->assertSee('name="from_lead"', false);
    }

    public function test_a_lead_from_another_branch_cannot_be_used_and_converted_or_lost_leads_are_ignored(): void
    {
        $branch = Branch::factory()->create();
        $other = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);

        $foreign = $this->lead($other);
        $this->actingAs($admin)->get(route('customer.create', ['from_lead' => $foreign->id]))->assertForbidden();

        $converted = $this->lead($branch, ['name' => 'Sudah Konversi', 'converted_at' => now(), 'status' => 'won']);
        $lost = $this->lead($branch, ['name' => 'Lead Gagal', 'status' => 'lost']);
        $this->actingAs($admin)->get(route('customer.create', ['from_lead' => $converted->id]))->assertOk()->assertDontSee('Sudah Konversi');
        $this->actingAs($admin)->get(route('customer.create', ['from_lead' => $lost->id]))->assertOk()->assertDontSee('Lead Gagal');
    }

    public function test_saving_the_customer_marks_the_lead_converted(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $lead = $this->lead($branch);
        $customer = Customer::factory()->create(['branch_id' => $branch->id]);

        $this->actingAs($admin);
        app(LeadConversion::class)->markConverted($lead->id, $customer);

        $lead->refresh();
        $this->assertTrue($lead->isConverted());
        $this->assertSame('won', $lead->status);
        $this->assertSame(Customer::class, $lead->converted_to_type);
        $this->assertSame($customer->id, (int) $lead->converted_to_id);

        // dipanggil lagi: tidak menimpa konversi yang sudah ada
        $other = Customer::factory()->create(['branch_id' => $branch->id]);
        app(LeadConversion::class)->markConverted($lead->id, $other);
        $this->assertSame($customer->id, (int) $lead->fresh()->converted_to_id);
    }

    public function test_conversion_buttons_show_for_new_leads_without_a_quotation(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $lead = $this->lead($branch, ['status' => 'new']);

        $this->actingAs($admin)->get(route('leads.show', $lead->id))
            ->assertOk()->assertSee('Buat Customer Kaca Film')->assertSee('from_lead=' . $lead->id, false);
    }

    public function test_saving_a_building_customer_from_a_lead_marks_the_lead_converted(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $admin->update(['password' => \Illuminate\Support\Facades\Hash::make('rahasia-123')]);
        \Illuminate\Support\Facades\DB::table('category_product_building')->insert(['id' => 1, 'name' => 'C1', 'duration' => 5, 'created_at' => now(), 'updated_at' => now()]);
        \Illuminate\Support\Facades\DB::table('product_building')->insert(['id' => 1, 'category_product_building_id' => 1, 'name' => 'P1', 'description' => '20%', 'created_at' => now(), 'updated_at' => now()]);
        $lead = $this->lead($branch, ['category' => 'building_film']);

        $this->actingAs($admin)->post(route('customer.building.store'), [
            'from_lead' => $lead->id, 'wsn' => 'WSN1', 'name' => 'Budi Lead', 'phone_number' => '0812',
            'admin_password' => 'rahasia-123',
            'products' => [['category_product_building_id' => 1, 'product_building_id' => 1, 'meters' => 10, 'warantee_duration' => 5]],
        ])->assertRedirect();

        $lead->refresh();
        $this->assertTrue($lead->isConverted());
        $this->assertSame(\App\Models\CustomerBuilding::class, $lead->converted_to_type);
    }
}
