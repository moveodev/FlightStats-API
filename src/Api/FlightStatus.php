<?php

namespace moveodev\FlightStatsApi\Api;

use DateTime;
use Illuminate\Support\Collection;

class FlightStatus extends AbstractApi
{
    /**
     * Get the API name to use in the URI.
     * @return string The API name
     */
    public function getApiName()
    {
        return 'flightstatus';
    }

    /**
     * Get the API version to use in the URI.
     * @return string The API version
     */
    public function getApiVersion()
    {
        return 'v2';
    }

    /**
     * Get the flight status from a flight associated with provided Flight ID.
     *
     * @param  string $flightId    FlightStats' Flight ID number for the desired
     *                             flight
     * @param  array  $queryParams Query parameters to add to the request
     *
     * @return Collection The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getFlightStatusById($flightId, array $queryParams = []): Collection
    {
        $endpoint = 'flight/status/' . $flightId;

        $response = $this->sendRequest($endpoint, $queryParams);

        return $this->parseResponse($response);
    }

    /**
     * Get the flight status from a flight that's arriving on the given date.
     *
     * @param  string   $carrier     The carrier (airline) code
     * @param  integer  $flight      The flight number
     * @param  DateTime $date        The arrival date
     * @param  array    $queryParams Query parameters to add to the request
     *
     * @return Collection The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getFlightStatusByArrivalDate($carrier, $flight, DateTime $date, array $queryParams = []): Collection
    {
        $endpoint = sprintf('flight/status/%s/%s/arr/%s', $carrier, $flight, $date->format('Y/n/j'));

        if (!isset($queryParams['utc'])) {
            $queryParams['utc'] = $this->flexClient->getConfig('use_utc_time');
        }

        $response = $this->sendRequest($endpoint, $queryParams);

        return $this->parseResponse($response);
    }

    /**
     * Get the flight status from a flight that's departing on the given date.
     *
     * @param  string   $carrier     The carrier (airline) code
     * @param  integer  $flight      The flight number
     * @param  DateTime $date        The departure date
     * @param  array    $queryParams Query parameters to add to the request
     *
     * @return Collection
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getFlightStatusByDepartureDate($carrier, $flight, DateTime $date, array $queryParams = []): Collection
    {
        $endpoint = sprintf('flight/status/%s/%s/dep/%s', $carrier, $flight, $date->format('Y/n/j'));

        if (!isset($queryParams['utc'])) {
            $queryParams['utc'] = $this->flexClient->getConfig('use_utc_time');
        }

        $response = $this->sendRequest($endpoint, $queryParams);

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
        if (empty($response['flightStatuses'])) {
            return new Collection([]);
        }

        $airlines = $this->parseAirlines($response['appendix']['airlines']);

        $airports = $this->parseAirports($response['appendix']['airports']);

        $flights = [];

        foreach ($response['flightStatuses'] as $flight) {
            // Set the carrier
            $carrier = $airlines[$flight['carrierFsCode']];

            $flight['carrier'] = $carrier;

            // Set the departure airport
            $departureAirport = $airports[$flight['departureAirportFsCode']];

            $flight['departureAirport'] = $departureAirport;

            // Set the arrival airport
            $arrivalAirport = $airports[$flight['arrivalAirportFsCode']];

            $flight['arrivalAirport'] = $arrivalAirport;

            $flights[] = $flight;
        }

        return new Collection($flights);
    }
}