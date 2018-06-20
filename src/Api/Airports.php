<?php

namespace Willemo\FlightStats\Api;


use Tightenco\Collect\Support\Collection;

class Airports extends AbstractApi
{

    /**
     * Get the API name to use in the URI.
     *
     * @return string The API name
     */
    public function getApiName()
    {
        return 'airports';
    }

    /**
     * Get the API version to use in the URI.
     *
     * @return string The API version
     */
    public function getApiVersion()
    {
        return 'v1';
    }

    /**
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Willemo\FlightStats\Exception\ClientException
     */
    public function getActiveAirports(): Collection
    {
        $response = $this->sendRequest('/active', []);

        return $this->parseResponse($response);
    }

    /**
     * @param $iataCode
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Willemo\FlightStats\Exception\ClientException
     */
    public function getAirportsByIataCode(string $iataCode): Collection
    {
        $response = $this->sendRequest("iata/{$iataCode}", []);

        return $this->parseResponse($response);
    }

    /**
     * @param $icaoCode
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Willemo\FlightStats\Exception\ClientException
     */
    public function getAirportsByIcaoCode(string $icaoCode): Collection
    {
        $response = $this->sendRequest("icao/{$icaoCode}", []);

        return $this->parseResponse($response);
    }

    /**
     * @param string $longitude
     * @param string $latitude
     * @param string $radius
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Willemo\FlightStats\Exception\ClientException
     */
    public function getAirportsWithinRadius(string $longitude, string $latitude, string $radius): Collection {
        $response = $this->sendRequest("withinRadius/{$longitude}/{$latitude}/{$radius}", []);

        return $this->parseResponse($response);
    }

    /**
     * Parse the response from the API to a more uniform and thorough format.
     *
     * @param  array $response The response from the API
     * @return Collection The parsed response
     */
    protected function parseResponse(array $response): Collection
    {
        if (empty($response['airports'])) {
            return collect([]);
        }

        return collect($response['airports']);
    }
}