<?php

namespace Willemo\FlightStats\Api;


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
     * @return array
     */
    public function getActiveAirports()
    {
        $response = $this->sendRequest('/active', []);

        return $this->parseResponse($response);
    }

    /**
     * @param $iataCode
     * @return array
     */
    public function getAirportsByIataCode($iataCode)
    {
        $response = $this->sendRequest('/iata/' . $iataCode, []);

        return $this->parseResponse($response);
    }

    /**
     * @param $icaoCode
     * @return array
     */
    public function getAirportsByIcaoCode($icaoCode)
    {
        $response = $this->sendRequest('/icao/' . $icaoCode, []);

        return $this->parseResponse($response);
    }

    /**
     * Parse the response from the API to a more uniform and thorough format.
     *
     * @param  array $response The response from the API
     * @return array            The parsed response
     */
    protected function parseResponse(array $response)
    {
        // TODO: Implement parseResponse() method.
    }
}