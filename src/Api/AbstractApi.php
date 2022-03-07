<?php

namespace moveodev\FlightStatsApi\Api;

use DateTime;
use DateTimeZone;
use Tightenco\Collect\Support\Collection;
use moveodev\FlightStatsApi\FlexClient;

abstract class AbstractApi implements ApiInterface
{
    /**
     * The FlexClient.
     * @var FlexClient
     */
    protected $flexClient;

    /**
     * Create an instance of the API object.
     *
     * @param FlexClient $flexClient The configured FlexClient object
     */
    public function __construct(FlexClient $flexClient)
    {
        $this->flexClient = $flexClient;
    }

    /**
     * Get the API name to use in the URI.
     * @return string The API name
     */
    abstract public function getApiName();

    /**
     * Get the API version to use in the URI.
     * @return string The API version
     */
    abstract public function getApiVersion();

    /**
     * Send the request through the FlexClient.
     *
     * @param  string $endpoint    The endpoint to make the
     *                             request to
     * @param  array  $queryParams The query parameters
     *
     * @return array               The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \moveodev\FlightStatsApi\Exception\ClientException
     */
    protected function sendRequest($endpoint, array $queryParams)
    {
        return $this->flexClient->sendRequest($this->getApiName(), $this->getApiVersion(), $endpoint, $queryParams);
    }

    /**
     * Parse the airlines array into an associative array with the airline's
     * FS code as the key.
     *
     * @param  array $airlines The airlines from the response
     *
     * @return array            The associative array of airlines
     */
    protected function parseAirlines(array $airlines)
    {
        $parsed = [];

        foreach ($airlines as $airline) {
            $parsed[$airline['fs']] = $airline;
        }

        return $parsed;
    }

    /**
     * Parse the airports array into an associative array with the airport's
     * FS code as the key.
     *
     * @param  array $airports The airports from the response
     *
     * @return array            The associative array of airports
     */
    protected function parseAirports(array $airports)
    {
        $parsed = [];

        foreach ($airports as $airport) {
            $parsed[$airport['fs']] = $airport;
        }

        return $parsed;
    }

    /**
     * Parse the equipments array into an associative array with the equipment's
     * IATA code as the key.
     *
     * @param  array $equipments The equipments from the response
     *
     * @return array            The associative array of equipments
     */
    protected function parseEquipments(array $equipments)
    {
        $parsed = [];

        foreach ($equipments as $equipment) {
            $parsed[$equipment['iata']] = $equipment;
        }

        return $parsed;
    }

    /**
     * Change a date/time in a local time zone to UTC.
     *
     * @param  string  $dateTimeString The local date/time as a string
     * @param  string  $timeZone       The local time zone name
     * @param  boolean $shouldFormat   Should the response be formatted ('c')
     *
     * @return DateTime|string         The date/time in UTC
     */
    protected function dateToUtc(
        $dateTimeString,
        $timeZone,
        $shouldFormat = true
    ) {
        $date = new DateTime($dateTimeString, new DateTimeZone($timeZone));

        $date->setTimeZone(new DateTimeZone('UTC'));

        if (!$shouldFormat) {
            return $date;
        }

        return $date->format('c');
    }

    /**
     * Parse the response from the API to a more uniform and thorough format.
     *
     * @param  array $response The response from the API
     *
     * @return Collection The parsed response
     */
    abstract protected function parseResponse(array $response): Collection;
}