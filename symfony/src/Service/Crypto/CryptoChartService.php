<?php

namespace App\Service\Crypto;

class CryptoChartService
{
	private const RANGES = [
		'24h' => ['-24 hours', 'hourly'],
		'1w'  => ['-1 week', '2h'],
		'1m'  => ['-1 month', '12h'],
		'6m'  => ['-6 months', 'daily'],
		'1y'  => ['-1 year', 'daily'],
	];

	public function __construct(private CryptoApiService $cryptoApiService)
	{
	}

	public function getPriceHistory(int $coinId, string $range): array
	{
		if ($coinId < 1 || !isset(self::RANGES[$range])) {
			throw new \InvalidArgumentException('Invalid cryptocurrency or chart range.');
		}

		[$start, $interval] = self::RANGES[$range];
		$now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
		$startTime = $now->modify($start);
		$response = $this->cryptoApiService->getCryptoData('quotes/historical', [
			'id' => $coinId,
			'time_start' => $startTime->format(DATE_ATOM),
			'time_end' => $now->format(DATE_ATOM),
			'interval' => $interval,
			'convert' => 'USD',
		]);

		$quotes = $response['data'][$coinId]['quotes'] ?? [];
		$points = [];
		foreach ($quotes as $quote) {
			$timestamp = $quote['timestamp'] ?? null;
			$price = $quote['quote']['USD']['price'] ?? null;
			if (is_string($timestamp) && is_numeric($price)) {
				$points[] = ['timestamp' => $timestamp, 'price' => (float) $price];
			}
		}

		return $points;
	}

}