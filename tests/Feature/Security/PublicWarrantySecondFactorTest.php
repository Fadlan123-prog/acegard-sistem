<?php

namespace Tests\Feature\Security;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWarrantySecondFactorTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): Customer
    {
        return Customer::factory()->create([
            'branch_id' => Branch::factory()->create()->id,
            'card_number' => 'CARD-0001',
            'phone_number' => '0812-3456-7890',
        ]);
    }

    public function test_lookup_without_or_with_wrong_phone_digits_fails_with_same_message(): void
    {
        $this->customer();

        $this->post(route('warranty.lookup'), ['card_number' => 'CARD-0001'])->assertSessionHasErrors('phone_last4');

        $wrong = $this->post(route('warranty.lookup'), ['card_number' => 'CARD-0001', 'phone_last4' => '0000']);
        $missing = $this->post(route('warranty.lookup'), ['card_number' => 'NOPE', 'phone_last4' => '7890']);

        $this->assertSame(session('error'), $missing->baseResponse->getSession()->get('error'));
        $wrong->assertSessionHas('error');
    }

    public function test_result_is_blocked_without_verification_and_works_after_it(): void
    {
        $c = $this->customer();
        $params = ['type' => 'car_film', 'id' => $c->id, 'card_number' => 'CARD-0001'];

        $this->get(route('warranty.result', $params))->assertForbidden();

        $this->post(route('warranty.lookup'), ['card_number' => 'CARD-0001', 'phone_last4' => '7890'])
            ->assertRedirect();
        $this->get(route('warranty.result', $params))->assertOk();
    }

    public function test_claim_and_card_download_need_verification(): void
    {
        $c = $this->customer();

        $this->post(route('warranty.claim.store'), [
            'card_number' => 'CARD-0001', 'type' => 'car_film', 'id' => $c->id, 'complaint' => 'x',
        ])->assertForbidden();

        $this->get(route('warranty.card.download.public', ['type' => 'car_film', 'id' => $c->id]) . '?card_number=CARD-0001')
            ->assertForbidden();
    }
}
