<?php

namespace moveodev\FlightStatsApi\Api;

use DateTime;
use Tightenco\Collect\Support\Collection;

class Schedules extends AbstractApi
{
    /**
     * Get the API name to use in the URI.
     * @return string The API name
     */
    public function getApiName()
    {
        return 'schedules';
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
     * Get information about a scheduled flight arriving on the given date.
     *
     * @param  string   $carrier     The carrier (airline) code
     * @param  integer  $flight      The flight number
     * @param  DateTime $date        The arrival date
     * @param  array    $queryParams Query parameters to add to the request
     *
     * @return array                 The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getFlightByArrivalDate($carrier, $flight, DateTime $date, array $queryParams = []): Collection
    {
        $endpoint = sprintf('flight/%s/%s/arriving/%s', $carrier, $flight, $date->format('Y/n/j'));

        $response = $this->sendRequest($endpoint, $queryParams);

        return $this->parseResponse($response);
    }

    /**
     * Get information about a scheduled flight departing on the given date.
     *
     * @param  string   $carrier     The carrier (airline) code
     * @param  integer  $flight      The flight number
     * @param  DateTime $date        The departure date
     * @param  array    $queryParams Query parameters to add to the request
     *
     * @return Collection The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    public function getFlightByDepartureDate($carrier, $flight, DateTime $date, array $queryParams = []): Collection
    {
        $endpoint = sprintf('flight/%s/%s/departing/%s', $carrier, $flight, $date->format('Y/n/j'));

        $response = $this->sendRequest($endpoint, $queryParams);

        return $this->parseResponse($response);
    }

    /**
     * @param string   $origin
     * @param string   $destination
     * @param DateTime $departure_date
     *
     * @return Collection
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFlightsByRouteByDepartureDate(string $origin, string $destination, DateTime $departure_date): Collection
    {

        $endpoint = sprintf('from/%s/to/%s/departing/%s', $origin, $destination, $departure_date->format('Y/n/j'));

        $response = $this->sendRequest($endpoint, ['codeType' => 'ICAO']);

        return $this->parseResponse($response);
    }

    /**
     * @param string   $origin
     * @param string   $destination
     * @param DateTime $departure_date
     *
     * @return Collection
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFlightsByRouteByArrivalDate(string $origin, string $destination, DateTime $arrival_date): Collection
    {

        $endpoint = sprintf('from/%s/to/%s/arriving/%s', $origin, $destination, $arrival_date->format('Y/n/j'));

        $response = $this->sendRequest($endpoint, ['codeType' => 'ICAO']);

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
        if (empty($response['scheduledFlights'])) {
            return new Collection([]);
        }

        $airlines = $this->parseAirlines($response['appendix']['airlines']);

        $airports = $this->parseAirports($response['appendix']['airports']);

        $flights = [];

        foreach ($response['scheduledFlights'] as $flight) {
            // Set the carrier
            $carrier = $airlines[$flight['carrierFsCode']];

            $flight['carrier'] = $carrier;

            // Set the departure airport
            $departureAirport = $airports[$flight['departureAirportFsCode']];

            $flight['departureAirport'] = $departureAirport;

            // Set the arrival airport
            $arrivalAirport = $airports[$flight['arrivalAirportFsCode']];

            $flight['arrivalAirport'] = $arrivalAirport;

            // Set the UTC departure time
            $flight['departureDate'] = [
                'dateLocal' => $flight['departureTime'],
                'dateUtc' => $this->dateToUtc($flight['departureTime'], $departureAirport['timeZoneRegionName']),
            ];

            // Set the UTC arrival time
            $flight['arrivalDate'] = [
                'dateLocal' => $flight['arrivalTime'],
                'dateUtc' => $this->dateToUtc($flight['arrivalTime'], $arrivalAirport['timeZoneRegionName']),
            ];

            $flights[] = $flight;
        }

        return new Collection($flights);
    }
}