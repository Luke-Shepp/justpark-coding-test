<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\ParkAndRide;
use App\ThirdParty\ParkingSpaceHttpService;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkingSpaceRankerGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkingSpaceRanker()
    {
        app()->singleton(ParkingSpaceHttpService::class, function () {
            $httpService = $this->getMockBuilder(ParkingSpaceHttpService::class)->disableOriginalConstructor()->getMock();
            $httpService->method('getRanking')->willReturn(new Response(200, [], collect([8,9,7])));
            return $httpService;
        });

        $parkingSpace7 = ParkAndRide::factory()->create(['id' => 7]);
        $parkingSpace8 = ParkAndRide::factory()->create(['id' => 8]);
        $parkingSpace9 = ParkAndRide::factory()->create(['id' => 9]);

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        $result = $gateway->rank([$parkingSpace7, $parkingSpace8, $parkingSpace9]);

        $this->assertEquals([$parkingSpace8, $parkingSpace9, $parkingSpace7], $result);
    }
}
