<?php

namespace Gvozdb\FlightStatsApi\Api;

use DateTime;
use Tightenco\Collect\Support\Collection;

class AirportStatus extends AbstractApi
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
     * @param  string   $airport     The arrival airport code
     * @param  DateTime $date        The arrival date
     * @param  integer  $hourOfDay   The Hour of day (0-23)
     * @param  array    $queryParams Query parameters to add to the request
     *
     * @return Collection The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Gvozdb\FlightStatsApi\Exception\ClientException
     */
    public function getAirportStatusByArrivalDate($airport, DateTime $date, $hourOfDay, array $queryParams = []): Collection
    {
        $endpoint = sprintf('airport/status/%s/arr/%s/%s', $airport, $date->format('Y/n/j'), $hourOfDay);

        if (!isset($queryParams['utc'])) {
            $queryParams['utc'] = $this->flexClient->getConfig('use_utc_time');
        }

        $response = $this->sendRequest($endpoint, $queryParams);

        return $this->parseResponse($response);
    }

    /**
     * @param  string   $airport     The arrival airport code
     * @param  DateTime $date        The arrival date
     * @param  integer  $hourOfDay   The Hour of day (0-23)
     * @param  array    $queryParams Query parameters to add to the request
     *
     * @return Collection The response from the API
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Gvozdb\FlightStatsApi\Exception\ClientException
     */
    public function getAirportStatusByDepartureDate($airport, DateTime $date, $hourOfDay, array $queryParams = []): Collection
    {
        $endpoint = sprintf('airport/status/%s/dep/%s/%s', $airport, $date->format('Y/n/j'), $hourOfDay);

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
        $equipments = $this->parseEquipments($response['appendix']['equipments']);

        $flights = [];

        foreach ($response['flightStatuses'] as $flight) {
            // Set the carrier
            $carrier = $airlines[$flight['carrierFsCode']];
            $flight['carrier'] = $carrier;

            // Set the equipment
            $equipment = array();
            if (!empty($flight['flightEquipment'])) {
                if (isset($flight['flightEquipment']['scheduledEquipmentIataCode'])) {
                    $equipment = $equipments[$flight['flightEquipment']['scheduledEquipmentIataCode']] ?: array();
                }
                if (isset($flight['flightEquipment']['actualEquipmentIataCode'])) {
                    $equipment = $equipments[$flight['flightEquipment']['actualEquipmentIataCode']] ?: array();
                }
            }
            $flight['equipment'] = $equipment;

            // Set the departure airport
            $departureAirport = $airports[$flight['departureAirportFsCode']];
            $flight['departureAirport'] = $departureAirport;

            // Set the arrival airport
            $arrivalAirport = $airports[$flight['arrivalAirportFsCode']];
            $flight['arrivalAirport'] = $arrivalAirport;

            //
            $flights[] = $flight;
        }

        return new Collection($flights);
    }
}
