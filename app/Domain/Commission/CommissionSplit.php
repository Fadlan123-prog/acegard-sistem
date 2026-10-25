<?php

namespace App\Domain\Commission;

use InvalidArgumentException;

/**
 * Immutable value object representing a fully-resolved installer commission split.
 *
 * Encodes exactly who gets what for a given job and why.
 * Constructed only via named factory methods — never with `new CommissionSplit()` directly.
 *
 * Zero framework dependencies: pure PHP 8.2, unit-testable without a database.
 *
 * Example usage:
 *   $split = CommissionSplit::standard(140_000, 60_000, 'fullset', false, false);
 *   $split->total();      // 200_000
 *   $split->hasPair();    // true
 *   $split->toArray();    // ['teknisi_amount' => 140000, ...]
 */
final readonly class CommissionSplit
{
    private function __construct(
        public readonly int    $teknisiAmount,
        public readonly int    $kenekAmount,
        public readonly string $configKey,
        public readonly bool   $isSecondCar,
        public readonly bool   $isThirdCar,
        public readonly bool   $isLebaran,
        public readonly string $splitType,  // 'standard' | 'solo' | 'pair_50_50'
    ) {
        if ($teknisiAmount < 0 || $kenekAmount < 0) {
            throw new InvalidArgumentException(
                "Commission amounts cannot be negative (teknisi={$teknisiAmount}, kenek={$kenekAmount})."
            );
        }
    }

    // ── Factory methods ──────────────────────────────────────────

    /**
     * Standard pairing: teknisi and kenek receive different amounts per config.
     */
    public static function standard(
        int    $teknisiAmount,
        int    $kenekAmount,
        string $configKey,
        bool   $isSecondCar = false,
        bool   $isThirdCar  = false,
        bool   $isLebaran   = false,
    ): self {
        return new self($teknisiAmount, $kenekAmount, $configKey, $isSecondCar, $isThirdCar, $isLebaran, 'standard');
    }

    /**
     * Solo installation: teknisi works alone, no kenek commission.
     */
    public static function solo(
        int    $teknisiAmount,
        string $configKey,
        bool   $isSecondCar = false,
        bool   $isThirdCar  = false,
        bool   $isLebaran   = false,
    ): self {
        return new self($teknisiAmount, 0, $configKey, $isSecondCar, $isThirdCar, $isLebaran, 'solo');
    }

    /**
     * Same-position pair (teknisi+teknisi or kenek+kenek): each receives equal amounts.
     * Triggered when both installers share the same job_position value.
     */
    public static function fiftyFifty(
        int    $eachAmount,
        string $configKey,
        bool   $isSecondCar = false,
        bool   $isThirdCar  = false,
        bool   $isLebaran   = false,
    ): self {
        return new self($eachAmount, $eachAmount, $configKey, $isSecondCar, $isThirdCar, $isLebaran, 'pair_50_50');
    }

    // ── Computed properties ───────────────────────────────────────

    public function total(): int
    {
        return $this->teknisiAmount + $this->kenekAmount;
    }

    public function hasPair(): bool
    {
        return $this->kenekAmount > 0;
    }

    public function isSamePairType(): bool
    {
        return $this->splitType === 'pair_50_50';
    }

    // ── Serialization ─────────────────────────────────────────────

    public function toArray(): array
    {
        return [
            'teknisi_amount' => $this->teknisiAmount,
            'kenek_amount'   => $this->kenekAmount,
            'total'          => $this->total(),
            'config_key'     => $this->configKey,
            'is_second_car'  => $this->isSecondCar,
            'is_third_car'   => $this->isThirdCar,
            'is_lebaran'     => $this->isLebaran,
            'split_type'     => $this->splitType,
        ];
    }
}
