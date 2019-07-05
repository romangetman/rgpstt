<?php

namespace RGPSTT;

class CurrencyUtils
{
    protected $conversion_rates = [];

//    protected $default_currency;

    public function __construct(array $rates)
    {
        $this->conversion_rates = $rates;

        /*$this->default_currency = array_reduce(array_keys($this->conversion_rates), function ($carry, $currency) {
            if ($this->conversion_rates[$currency]['rate'] === 1) {
                $carry = $currency;
            }
            return $carry;
        }, '');*/
    }

    public function convertToDefaultCurrency(float $amount, string $currency): float
    {
        return $amount / $this->conversion_rates[$currency]['rate'];
    }

    public function convertFromDefaultCurrency(float $amount, string $currency): float
    {
        return $amount * $this->conversion_rates[$currency]['rate'];
    }

    private function roundUp($value, $precision = 0)
    {
        if ($precision < 0) {
            $precision = 0;
        }

        $mult = pow(10, $precision);

        return ceil($value * $mult) / $mult;
    }

    public function roundCurrency($amount, $currency)
    {
        $precision = $this->conversion_rates[$currency]['precision'];

        return number_format($this->roundUp($amount, $precision), $precision, '.', '');
    }

}
