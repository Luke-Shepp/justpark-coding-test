<?php

namespace App\Http\Controllers;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\SearchService;
use Illuminate\Support\Facades\Input;

class SearchController extends Controller
{
    public function index(
        SearchService $searchService,
        ParkAndRideRankerGateway $parkAndRideGateway,
        ParkingSpaceRankerGateway $parkingSpaceGateway
    ) {
        $boundingBox = $searchService->getBoundingBox(request()->input('lat'), request()->input('lng'), 5);
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox);
        // @todo Part 2) rank parking spaces

        $parkAndRide = $searchService->searchParkAndRide($boundingBox);
        $rankedParkAndRide = $parkAndRideGateway->rank($parkAndRide);

        $resultArray = [];/*@todo Part 2)*/
        return \App\Http\Resources\Location::collection(collect($resultArray));
    }

    public function details(SearchService $searchService)
    {
        $boundingBox = $searchService->getBoundingBox(request()->input('lat'), request()->input('lng'), 5);
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox);
        $parkAndRide = $searchService->searchParkAndRide($boundingBox);

        $things = [];
        return response()->json($this->formatLocations($things));
    }

    private function formatLocations(array $things)
    {
        //@todo Part 1) format 'park and rides' and parking spaces for response
    }
}
