<?php

use App\Enums\ParkingTypes;

return [
    ParkingTypes::PARK_AND_RIDE => [
        'description' => 'Park and Ride to :attraction_name. (approx :minutes_to_destination minutes to destination)',
        'name' => ':location_description',
    ],
    ParkingTypes::PARKING_SPACE => [
        'description' => 'Parking space with :no_of_spaces bays: :space_details',
        'name' => ':street_name, :city',
    ],
];
