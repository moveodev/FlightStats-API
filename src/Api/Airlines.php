<?php

namespace moveodev\FlightStatsApi\Api;

use Illuminate\Support\Collection;

class Airlines extends AbstractApi
{
    /**
     * Get the API name to use in the URI.
     * @return string The API name
     */
    public function getApiName()
    {
        return 'airlines';
    }

    /**
     * Get the API version to use in the URI.
     * @return string The API version
     */
    public function getApiVersion()
    {
        return 'v1';
    }

    /**
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getActiveAirlines(): Collection
    {
        $response = $this->sendRequest('active', []);

        return $this->parseResponse($response);
    }

    /**
     * @param $iataCode
     *
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getAirlinesByIataCode(string $iataCode): Collection
    {
        $response = $this->sendRequest("iata/{$iataCode}", []);

        return $this->parseResponse($response);
    }

    /**
     * @param $icaoCode
     *
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getAirlinesByIcaoCode(string $icaoCode): Collection
    {
        $response = $this->sendRequest("icao/{$icaoCode}", []);

        return $this->parseResponse($response);
    }

    /**
     * Parse the response from the API to a more uniform and thorough format.
     *
     * @param  array $response The response from the API
     *
     * @return Collection The parsed response
     */
    protected function parseResponse(array $response): Collection
    {
        if (empty($response['airlines'])) {
            return new Collection([]);
        }

        return new Collection($response['airlines']);
    }
}