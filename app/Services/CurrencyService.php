<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\Session;

class CurrencyService
{
    /**
     * Country values that indicate a Canadian store.
     */
    protected static array $canadianCountries = ['Canada', 'CA', 'canada', 'ca', 'CAN'];

    /**
     * Fixed USD → CAD exchange rate.
     * Update this value when rates change significantly.
     */
    protected static float $usdToCadRate = 1.42;

    /**
     * Check if the active store is Canadian.
     */
    public static function isCanadian(): bool
    {
        $storeId = Session::get('active_store_id');
        if (!$storeId) {
            return false;
        }

        $store = Store::find($storeId);
        if (!$store) {
            return false;
        }

        return in_array($store->country, self::$canadianCountries);
    }

    /**
     * Get the exchange rate (USD to CAD).
     */
    public static function getRate(): float
    {
        return self::$usdToCadRate;
    }

    /**
     * Get the currency code (ISO 4217) for the active store.
     */
    public static function getCode(): string
    {
        return self::isCanadian() ? 'CAD' : 'USD';
    }

    /**
     * Get the display symbol for the active store's currency.
     */
    public static function getSymbol(): string
    {
        return self::isCanadian() ? 'C$' : '$';
    }

    /**
     * Convert a USD amount to the active currency.
     * If Canadian → multiply by exchange rate.
     * If USD → return as-is.
     */
    public static function convert(float $amount): float
    {
        if (self::isCanadian()) {
            return $amount * self::$usdToCadRate;
        }

        return $amount;
    }

    /**
     * Format a price with the correct currency symbol.
     * Automatically converts from USD to CAD if the store is Canadian.
     *
     * @param float|null $amount   The price amount (always stored in USD)
     * @param int        $decimals Number of decimal places (default 2)
     * @return string Formatted price string like "$9.99" or "C$5.51"
     */
    public static function format(?float $amount, int $decimals = 2): string
    {
        if ($amount === null) {
            $amount = 0;
        }

        $converted = self::convert($amount);

        return self::getSymbol() . number_format($converted, $decimals);
    }

    /**
     * Get the correct price from a model, auto-converted.
     * Reads the base USD field and converts if Canadian.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $field The base field name (e.g. 'price', 'base_price')
     * @return float
     */
    public static function getPrice($model, string $field = 'price'): float
    {
        $usdAmount = (float) ($model->{$field} ?? 0);

        return self::convert($usdAmount);
    }

    /**
     * Format a price with the active currency symbol, but do NOT convert it.
     */
    public static function formatOnly(?float $amount, int $decimals = 2): string
    {
        if ($amount === null) {
            $amount = 0;
        }
        return self::getSymbol() . number_format($amount, $decimals);
    }

    /**
     * Format a price with a specific currency code. Does NOT perform conversion.
     */
    public static function formatWithCurrency(?float $amount, ?string $currency, int $decimals = 2): string
    {
        if ($amount === null) {
            $amount = 0;
        }
        $symbol = ($currency === 'CAD') ? 'C$' : '$';
        return $symbol . number_format($amount, $decimals);
    }

    /**
     * Return currency data as an array (useful for passing to JS/views).
     */
    public static function toArray(): array
    {
        return [
            'code'   => self::getCode(),
            'symbol' => self::getSymbol(),
            'is_cad' => self::isCanadian(),
            'rate'   => self::isCanadian() ? self::$usdToCadRate : 1,
        ];
    }
}

