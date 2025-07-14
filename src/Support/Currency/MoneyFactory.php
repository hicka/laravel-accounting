<?php
namespace Hickr\Accounting\Support\Currency;

use Brick\Money\Money;
use Brick\Math\RoundingMode;
use Brick\Money\Context\CustomContext;

class MoneyFactory
{
    public static function make(string $amount, string $currency): Money
    {
        return Money::of($amount, $currency, new CustomContext(6), RoundingMode::HALF_UP);
    }

    public static function convertToBase(Money $money, float $exchangeRate, bool $inverse = false): Money
    {
        $baseCurrency = config('accounting.default_currency', 'MVR');

        $rate = $inverse ? (1 / $exchangeRate) : $exchangeRate;

        $baseAmount = $money->getAmount()->multipliedBy($rate, RoundingMode::HALF_UP);

        return Money::of($baseAmount, $baseCurrency, new CustomContext(6), RoundingMode::HALF_UP);
    }
}