<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\ParkAndRide;
use App\ParkingSpace;
use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkingSpaceHttpService;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSearchEndpointUsesGatewayRanking()
    {
        ParkAndRide::factory()->createMany([
            ['id' => 10, 'lat' => 0.1, 'lng' => 0.1],
            ['id' => 1, 'lat' => 0.1, 'lng' => 0.1],
        ]);

        ParkingSpace::factory()->createMany([
            ['id' => 5, 'lat' => 0.1, 'lng' => 0.1],
            ['id' => 9, 'lat' => 0.1, 'lng' => 0.1],
        ]);

        $parkAndRideMock = \Mockery::mock(ParkAndRideSDK::class);
        $parkAndRideMock->shouldReceive('getRankingResponse')->once()->andReturnSelf();
        $parkAndRideMock->shouldReceive('getResult')->once()->andReturn([
            ['park_and_ride_id' => 10, 'rank' => 1],
            ['park_and_ride_id' => 1, 'rank' => 2]
        ]);
        $this->app->instance(ParkAndRideSDK::class, $parkAndRideMock);

        $parkingSpaceMock = \Mockery::mock(ParkingSpaceHttpService::class);
        $parkingSpaceMock->shouldReceive('getRanking')->once()->andReturn(new Response(200, [], collect([9, 5])));
        $this->app->instance(ParkingSpaceHttpService::class, $parkingSpaceMock);

        $response = $this->get('/api/search?lat=0.1&lng=0.1');
        $responseContent = json_decode($response->getContent());

        $response->assertStatus(200);

        // Assert Park and Ride precedes Parking Spaces, and raking is 10, 1 and 9, 5 respectively,
        // as returned by the relevant mocked third party ranking services
        $this->assertEquals(
            array_column($responseContent->data, 'id'),
            [10, 1, 9, 5]
        );
    }

    public function testSearchEndpointIsHealthy()
    {
        app()->singleton(ParkAndRideRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        app()->singleton(ParkingSpaceRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        $response = $this->get('/api/search?lat=0.1&lng=0.1');
        $response->assertStatus(200);
    }

    public function testDetailsEndpoint()
    {
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'disneyland',
            'location_description' => 'TCR',
            'minutes_to_destination' => 10,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway off street',
            'city' => 'London',
            'street_name' => 'Oxford Street',
            'no_of_spaces' => 2,
        ]);

        $response = $this->get('/api/details?lat=0.1&lng=0.1');

        $response->assertStatus(200);
        $this->assertEquals(json_encode([
            [
                "description" => "Park and Ride to disneyland. (approx 10 minutes to destination)",
                "location_name" => "TCR"
            ],
            [
                "description" => "Parking space with 2 bays: Driveway off street",
                "location_name" => "Oxford Street, London"
            ]
        ]), $response->getContent());
    }
}
