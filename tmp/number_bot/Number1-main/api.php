<?php

class Api
{
	private string $baseUrl = "https://api.spider-service.com";
	private string $apiKey;

	public function __construct(string $api_key)
	{
		$this->apiKey = $api_key;
	}

	private function request(array $params): ?array
	{
		$query = http_build_query(array_merge(['apiKey' => $this->apiKey], $params));
		$url = "{$this->baseUrl}?{$query}";

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_CONNECTTIMEOUT => 5,
		]);

		$response = curl_exec($ch);
		if ($response === false) {
			curl_close($ch);
			return null;
		}

		curl_close($ch);

		$decoded = json_decode($response, true);
		return is_array($decoded) ? $decoded : null;
	}

	public function getBalance()
	{
		$data = $this->request([
			'action' => 'getBalance',
		]);

		if (($data['error'] ?? '') === 'INFORMATION_SUCCESS') {
			return $data['result']['wallet'];
		}

		return 0;
	}

	public function getNumber($countryCode)
	{
		$data = $this->request([
			'action' => 'getNumber',
			'country' => $countryCode,
		]);

		if (($data['error'] ?? '') === 'INFORMATION_SUCCESS') {
			return [
				'number' => $data['result']['phone'],
				'hash_code' => $data['result']['hash_code'],
			];
		}

		return "error";
	}

	public function getCode($hashCode)
	{
		$data = $this->request([
			'action' => 'getCode',
			'hash_code' => $hashCode,
		]);

		if (($data['error'] ?? '') === 'INFORMATION_SUCCESS') {
			return $data['result'];
		}

		return "error";
	}

	public function getCountries()
	{
		$data = $this->request([
			'action' => 'getCountrys',
		]);

		if (($data['error'] ?? '') === 'INFORMATION_SUCCESS') {
			return $data['result']['countries'][1];
		}

		return "error";
	}
}