<?php

namespace App\Http\Controllers;


use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class CoinController
 * @package App\Http\Controllers
 */
class CoinController extends Controller
{
    const CACHE_KEY = 'coinbase.rates';

    /**
     * @var Client
     */
    protected $client;

    public function getRates(Request $request)
    {
        $client = $this->getCoinbaseClient();

        $defaultPairs = env('CURRENCY_PAIRS', '');
        $delta = (float)env('DELTA_PERCENT', 5);
        $gap = (float)env('GAP_PERCENT', 10);
        $cacheTTL = (float)env('CACHE_TTL');

        $pairs = $request->input('pair', $defaultPairs);
        $pairs = explode(',', $pairs);

        $rates = [];
        foreach ($pairs as $pair) {
            $rate = $client->getSpotPrice($pair)->jsonSerialize();
            $amount = 110; //(float)$rate['amount'];
            $rates[$pair] = [
                'amount' => $amount,
                'min' => 0.01 * (100 - $delta) * $amount,
                'max' => 0.01 * (100 + $delta) * $amount,
            ];
        }

        $cache = Cache::get(static::CACHE_KEY);
        if (!$cache) {
            // В кэше ничего нет - сохраняем текущие значения
            Cache::set(static::CACHE_KEY, $rates, $cacheTTL);
            $response = $rates;
        }
        else {
            $response = [];
            // Есть сохраненные данные в кэше
            foreach ($cache as $pair => $rate) {
                $cacheAmount = (float)$rate['amount'];
                $actualAmount = $rates[$pair]['amount'];
                $amount = $actualAmount + 0.01 * $gap * ($actualAmount - $cacheAmount);

                $response[$pair] = [
                    'amount' => $actualAmount,
                    'min' => $amount - 0.01 * $delta * $actualAmount,
                    'max' => $amount + 0.01 * $delta * $actualAmount,
                ];
            }

        }
        return $this->response($response);
    }

    /**
     * @return Client
     */
    protected function getCoinbaseClient()
    {
        if ($this->client) return $this->client;

        $apiKey = config('COINBASE_API_KEY');
        $apiSecret = config('COINBASE_API_SECRET');

        $configuration = Configuration::apiKey($apiKey, $apiSecret);
        $this->client = Client::create($configuration);

        return $this->client;
    }
}