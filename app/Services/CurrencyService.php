<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    protected $baseCurrency = 'IDR';
    protected $cacheKey = 'currency_rates';
    protected $cacheDuration = 129600; // 360 * 60 * 6 = 129600 seconds (36 hours)

    /**
     * Get currency rates based on IDR.
     */
    public function getRates(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheDuration, function () {
            try {
                $response = Http::get("https://api.frankfurter.app/latest?base={$this->baseCurrency}");
                
                if ($response->successful()) {
                    return $response->json()['rates'];
                }
                Log::warning('Currency API request failed.', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            } catch (\Exception $e) {
                Log::error('Currency API exception: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Convert amount from one currency to another.
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = $this->getRates();

        // 1. Convert source to base (IDR)
        $amountInBase = $from === $this->baseCurrency 
            ? $amount 
            : $amount / ($rates[$from] ?? 1);

        // 2. Convert base to target
        $finalAmount = $to === $this->baseCurrency 
            ? $amountInBase 
            : $amountInBase * ($rates[$to] ?? 1);

        return $finalAmount;
    }

    /**
     * Convert amount from given currency back to base currency (IDR).
     */
    public function convertToBase(float $amount, string $from): float
    {
        return $this->convert($amount, $from, $this->baseCurrency);
    }

    /**
     * Check if a rate is available for the given currency.
     */
    public function hasRate(string $currency): bool
    {
        if ($currency === $this->baseCurrency) {
            return true;
        }
        $rates = $this->getRates();
        return isset($rates[$currency]) && $rates[$currency] > 0;
    }
}
