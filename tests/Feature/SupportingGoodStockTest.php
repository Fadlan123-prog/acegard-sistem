<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\SupportingGood;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for the Phase 3 fix to SupportingGoodController::mutate():
 * over-deduction is now rejected with a validation error instead of being silently
 * clamped to zero (which previously corrupted the audit trail — the mutation log
 * recorded the requested quantity even when the applied delta was smaller).
 */
class SupportingGoodStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_out_mutation_exceeding_stock_is_rejected(): void
    {
        $branch = Branch::factory()->create();
        $good   = SupportingGood::factory()->create(['branch_id' => $branch->id, 'current_stock' => 5]);
        $staff  = $this->userWithRole('admin', $branch);

        $this->actingAs($staff)
            ->post(route('supporting-goods.mutate', $good->id), [
                'type'     => 'out',
                'quantity' => 10,
            ])
            ->assertSessionHasErrors('quantity');

        $good->refresh();
        $this->assertEquals(5, $good->current_stock, 'Stock must be unchanged after a rejected over-deduction.');
        $this->assertSame(0, $good->mutations()->count(), 'No mutation record should exist for a rejected request.');
    }

    public function test_out_mutation_within_stock_succeeds_and_matches_the_audit_record(): void
    {
        $branch = Branch::factory()->create();
        $good   = SupportingGood::factory()->create(['branch_id' => $branch->id, 'current_stock' => 10]);
        $staff  = $this->userWithRole('admin', $branch);

        $this->actingAs($staff)
            ->post(route('supporting-goods.mutate', $good->id), [
                'type'     => 'out',
                'quantity' => 4,
            ])
            ->assertRedirect();

        $good->refresh();
        $this->assertEquals(6, $good->current_stock);

        $mutation = $good->mutations()->latest()->first();
        $this->assertEquals(4, $mutation->quantity);
        $this->assertEquals(10, $mutation->stock_before);
        $this->assertEquals(6, $mutation->stock_after);
        // The applied delta must always match what was actually recorded — no silent clamping.
        $this->assertEquals((float) $mutation->stock_before - (float) $mutation->quantity, (float) $mutation->stock_after);
    }

    public function test_in_mutation_increases_stock(): void
    {
        $branch = Branch::factory()->create();
        $good   = SupportingGood::factory()->create(['branch_id' => $branch->id, 'current_stock' => 3]);
        $staff  = $this->userWithRole('admin', $branch);

        $this->actingAs($staff)
            ->post(route('supporting-goods.mutate', $good->id), [
                'type'     => 'in',
                'quantity' => 7,
            ])
            ->assertRedirect();

        $good->refresh();
        $this->assertEquals(10, $good->current_stock);
    }

    public function test_exact_stock_out_is_allowed_and_reaches_zero(): void
    {
        $branch = Branch::factory()->create();
        $good   = SupportingGood::factory()->create(['branch_id' => $branch->id, 'current_stock' => 5]);
        $staff  = $this->userWithRole('admin', $branch);

        $this->actingAs($staff)
            ->post(route('supporting-goods.mutate', $good->id), [
                'type'     => 'out',
                'quantity' => 5,
            ])
            ->assertRedirect();

        $good->refresh();
        $this->assertEquals(0, $good->current_stock);
    }
}
