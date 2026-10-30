<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CategoryProduct;
use App\Models\Employee;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderFilmSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function so(Branch $branch, string $category = 'car_film'): SalesOrder
    {
        return SalesOrder::create([
            'branch_id' => $branch->id, 'so_number' => 'SO-T-' . uniqid(), 'date' => now()->toDateString(),
            'prospect_name' => 'Budi', 'category' => $category, 'status' => 'confirmed',
            'subtotal' => 0, 'discount_amount' => 0, 'total_amount' => 0,
        ]);
    }

    private function film(): array
    {
        $cat = CategoryProduct::forceCreate(['name' => 'Premium', 'duration' => 5]);
        $other = CategoryProduct::forceCreate(['name' => 'Standar', 'duration' => 3]);
        $p = Product::create(['category_id' => $cat->id, 'name' => 'AP50', 'description' => '20%']);

        return [$cat, $other, $p];
    }

    public function test_film_type_and_darkness_are_saved_and_shown(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        $tukang = Employee::factory()->create(['branch_id' => $branch->id]);
        [$cat, , $p] = $this->film();
        $so = $this->so($branch);

        $this->actingAs($admin)->post(route('sales-orders.assign', $so), [
            'tukang_id' => $tukang->id, 'film_category_id' => $cat->id, 'film_product_id' => $p->id,
        ])->assertSessionHasNoErrors();

        $so->refresh();
        $this->assertSame($cat->id, (int) $so->film_category_id);
        $this->assertSame($p->id, (int) $so->film_product_id);
        $this->assertSame('Premium — AP50 (20%)', $so->film_label);

        $this->actingAs($admin)->get(route('sales-orders.show', $so))->assertOk()->assertSee('Premium — AP50 (20%)');
    }

    public function test_darkness_must_belong_to_selected_type(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        [, $other, $p] = $this->film();
        $so = $this->so($branch);

        $this->actingAs($admin)->post(route('sales-orders.assign', $so), [
            'film_category_id' => $other->id, 'film_product_id' => $p->id,
        ])->assertSessionHasErrors('film_product_id');

        $this->assertNull($so->fresh()->film_product_id);
    }

    public function test_film_can_be_changed_without_changing_technician(): void
    {
        $branch = Branch::factory()->create();
        $admin = $this->userWithRole('admin', $branch);
        [$cat, , $p] = $this->film();
        $so = $this->so($branch);

        $this->actingAs($admin)->post(route('sales-orders.assign', $so), [
            'film_category_id' => $cat->id, 'film_product_id' => $p->id,
        ])->assertSessionHas('success');

        $this->assertSame($p->id, (int) $so->fresh()->film_product_id);
    }
}
