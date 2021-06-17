<?php

namespace App\Http\Controllers;

use App\Enums\ParkingTypes;
use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\Http\Resources\Location;
use App\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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

    /**
     * @param SearchService $searchService
     * @param ParkAndRideRankerGateway $parkAndRideGateway
     * @param ParkingSpaceRankerGateway $parkingSpaceGateway
     */
    public function __construct(
        SearchService $searchService,
        ParkAndRideRankerGateway $parkAndRideGateway,
        ParkingSpaceRankerGateway $parkingSpaceGateway
    ) {
        $this->searchService = $searchService;
        $this->parkAndRideGateway = $parkAndRideGateway;
        $this->parkingSpaceGateway = $parkingSpaceGateway;
    }

    /**
     * @param Request $request
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        $locations = new Collection();

        $boundingBox = $this->searchService->getBoundingBox(
            $request->input('lat'),
            $request->input('lng'),
            5
        );

        $parkAndRide = $this->searchService->searchParkAndRide($boundingBox);
        $locations = $locations->merge($this->parkAndRideGateway->rank($parkAndRide));

        $parkingSpaces = $this->searchService->searchParkingSpaces($boundingBox);
        $locations = $locations->merge($this->parkingSpaceGateway->rank($parkingSpaces));

        return Location::collection($locations);
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
