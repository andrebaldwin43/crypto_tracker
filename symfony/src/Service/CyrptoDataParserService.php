<?php

namespace App\Service;

class CyrptoDataParserService
{
    public function parse(array $cryptoData): array
    {
        $parsedData = [];
        foreach ($cryptoData as $crypto) {
            $name = $crypto->getName() ?? null;
            if (!isset($parsedData[$name])) {
                $parsedData[$name] = [
                    'price' => 0,
                    'cryptoCurrency' => $crypto->getCryptoCurrency() ?? null,
                    'quantity' => 0,
                    'costBasis' => 0,
                    'value' => 0,
                ];
            }

            $parsedData[$name]['price'] += $crypto->getPrice() ?? 0;
            $parsedData[$name]['quantity'] += $crypto->getQuantity() ?? 0;
            $parsedData[$name]['costBasis'] = $parsedData[$name]['price'] / $parsedData[$name]['quantity'];
            // replace this with current coin price once I get the API integration
            $parsedData[$name]['value'] = $parsedData[$name]['price'] * $parsedData[$name]['quantity'];
        }

        return $parsedData;
    }
}