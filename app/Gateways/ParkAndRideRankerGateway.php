<?php

namespace App\Gateways;

use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\TimeoutException;
use Illuminate\Support\Facades\Log;

class ParkAndRideRankerGateway
{
    /** @var ParkAndRideSDK */
    private $parkAndRide;

    /**
     * @param ParkAndRideSDK $parkAndRide
     */
    public function __construct(ParkAndRideSDK $parkAndRide)
    {
        $this->parkAndRide = $parkAndRide;
    }

    /**
     * @param array $items
     * @return array
     */
    public function rank(array $items): array
    {
        $keyedItems = [];
        foreach ($items as $item) {
            $keyedItems[$item['id']] = $item;
        }

        try {
            $rankedResponse = $this->parkAndRide->getRankingResponse(new RankingRequest(array_keys($keyedItems)))->getResult();
        } catch (TimeoutException $exception) {
            Log::error('Timeout ranking park and ride locations');

            // In the event of a timeout from the ranking service, return the items as they
            // were passed. This isn't great, because the items are not ranked, and any consuming code
            // doesn't know this has happened - but it is better than the app crashing for the user.
            // The exception is handled at this level, instead of the controller to keep logic out of
            // the controller layer.
            return $items;
        }

        $var = array_column($rankedResponse, 'rank');
        array_multisort($var, SORT_ASC, $rankedResponse);
        $ranking = array_column($rankedResponse, 'park_and_ride_id');

        Log::info('Got ranking: ' . json_encode($ranking));

        $rankedItems = [];
        foreach ($ranking as $rank) {
            $rankedItems[] = $keyedItems[$rank];
        }
        return $rankedItems;
    }
}
