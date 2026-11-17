<?php

namespace App\Service\Crypto;

use App\Service\Crypto\CryptoApiService;
use Psr\Log\LoggerInterface;

class CryptoDataParserService
{
    public function __construct(
        private CryptoApiService $cryptoApiService,
        private LoggerInterface $logger
    ){}

    public function parse(array $cryptoData): array
    {
        $liveData = $this->cryptoApiService->getCryptoData('listings/latest');

        $liveDataBySymbol = [];
        foreach ($liveData['data'] as $coin) {
            $liveDataBySymbol[$coin['symbol']] = $coin;
        }

        $parsedData = [];
        foreach ($cryptoData as $crypto) {
            $name   = $crypto->getName() ?? null;
            $symbol = $crypto->getCryptoCurrency();

            if (!isset($parsedData[$name])) {
                $liveDataForSymbol = $this->getLiveDataForSymbol($symbol, $liveDataBySymbol);

                $parsedData[$name] = [
                    'price'          => $crypto->getPrice() ?? 0,
                    'cryptoCurrency' => $symbol ?? null,
                    'quantity'       => 0,
                    'costBasis'      => 0,
                    'value'          => 0,
                    'liveData'       => $liveDataForSymbol
                ];
            }

            $parsedData[$name]['price']    += $crypto->getPrice() ?? 0;
            $parsedData[$name]['quantity'] += $crypto->getQuantity() ?? 0;
            $parsedData[$name]['value']     = $parsedData[$name]['liveData'] ? $parsedData[$name]['liveData']['quote'][0]['price'] * $parsedData[$name]['quantity'] : 0;
            $parsedData[$name]['costBasis'] = $parsedData[$name]['price'] * $parsedData[$name]['quantity'];
        }

        return $parsedData;
    }

    private function getLiveDataForSymbol(string $symbol, array $liveDataBySymbol): ?array
    {
        if (!array_key_exists($symbol, $liveDataBySymbol)) {
            $this->logger->warning("Symbol not found in live data: {$symbol}");
            return null;
        }

        $data = $liveDataBySymbol[$symbol];

        if (!isset($data['quote'][0])) {
            $this->logger->error("Quote data malformed for symbol: {$symbol}");
            return null;
        }

        return $data;
    }
}