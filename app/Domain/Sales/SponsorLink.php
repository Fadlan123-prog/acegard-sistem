<?php

namespace App\Domain\Sales;

/**
 * Satu simpul di rantai sponsor — dipakai SalesCommissionCalculator untuk
 * menentukan penerima stage/marketing-referral tanpa perlu query DB sendiri.
 */
final readonly class SponsorLink
{
    public function __construct(
        public int $employeeId,
        public bool $isSales,
        public bool $isMarketing,
    ) {}
}
