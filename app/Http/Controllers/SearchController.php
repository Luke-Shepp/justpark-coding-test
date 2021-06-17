<?php

namespace App\Http\Controllers;

use App\Enums\ParkingTypes;
use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;

class SearchController extends Controller
{
    /** @var SearchService */
    private $searchService;

    /** @var ParkAndRideRankerGateway */
    private $parkAndRideGateway;

    /** @var ParkingSpaceRankerGateway */
    private $parkingSpaceGateway;

    public function __construct(
        SearchService $searchService,
        ParkAndRideRankerGateway $parkAndRideGateway,
        ParkingSpaceRankerGateway $parkingSpaceGateway
    ) {
        $this->searchService = $searchService;
        $this->parkAndRideGateway = $parkAndRideGateway;
        $this->parkingSpaceGateway = $parkingSpaceGateway;
    }

    public function index(Request $request) {
        $boundingBox = $this->searchService->getBoundingBox($request->input('lat'), $request->input('lng'), 5);
        $parkingSpaces = $this->searchService->searchParkingSpaces($boundingBox);
        // @todo Part 2) rank parking spaces

        $parkAndRide = $this->searchService->searchParkAndRide($boundingBox);
        $rankedParkAndRide = $this->parkAndRideGateway->rank($parkAndRide);

        $resultArray = [];/*@todo Part 2)*/
        return \App\Http\Resources\Location::collection(collect($resultArray));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function details(Request $request): JsonResponse
    {
        $boundingBox = $this->searchService->getBoundingBox(
            $request->input('lat'),
            $request->input('lng'),
            5
        );

        $locations = [
            ParkingTypes::PARK_AND_RIDE => $this->searchService->searchParkAndRide($boundingBox),
            ParkingTypes::PARKING_SPACE => $this->searchService->searchParkingSpaces($boundingBox),
        ];

        return response()->json($this->formatLocations($locations));
    }

    /**
     * @param array $unformattedLocations
     * @return Collection
     */
    private function formatLocations(array $unformattedLocations): Collection
    {
        $formatted = new Collection();

        foreach ($unformattedLocations as $type => $locations) {
            $formatted = $formatted->merge(collect($locations)
                ->map(function ($location) use ($type) {
                    return [
                        'description'   => __("locations.$type.description", $location->toArray()),
                        'location_name' => __("locations.$type.name", $location->toArray()),
                    ];
                }));
        }

        return $formatted;
    }
}
