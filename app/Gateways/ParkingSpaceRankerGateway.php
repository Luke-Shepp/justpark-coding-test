<?php

namespace App\Gateways;

use App\ThirdParty\ParkingSpaceHttpService;
use App\ThirdParty\TimeoutException;
use Illuminate\Support\Facades\Log;

class ParkingSpaceRankerGateway
{
    /** @var ParkingSpaceHttpService */
    private $service;

    /** @var int Service timeout in milliseconds */
    private const TIMEOUT = 1000;

    /**
     * @param ParkingSpaceHttpService $service
     */
    public function __construct(ParkingSpaceHttpService $service)
    {
        $this->service = $service;
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
            $response = $this->service->getRanking(json_encode(array_keys($keyedItems)), self::TIMEOUT);
        } catch (TimeoutException $exception) {
            Log::error('Timeout ranking parking spaces');

            // In the event of a timeout from the ranking service, return the items as they
            // were passed. This isn't great, because the items are not ranked, and any consuming code
            // doesn't know this has happened - but it is better than the app crashing for the user.
            // The exception is handled at this level, instead of the controller to keep logic out of
            // the controller layer.
            return $items;
        }

        $ranking = array_values(json_decode($response->getBody(), true));

        Log::info('Got ranking: ' . json_encode($ranking));

        $rankedItems = [];
        foreach ($ranking as $rank) {
            $rankedItems[] = $keyedItems[$rank];
        }

        return $rankedItems;
    }
}
