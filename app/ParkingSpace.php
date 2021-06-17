<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ParkingSpace
 *
 * @property int $id
 * @property string $name
 * @property float $lat
 * @property float $lng
 * @property string $space_details
 * @property string $city
 * @property string $street_name
 * @property int $no_of_spaces
 * @property-read User $owner
 */
class ParkingSpace extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getNumberOfSpaces()
    {
        return $this->no_of_spaces;
    }

    public function getSpaceDetails()
    {
        return $this->space_details;
    }

    public function getStreetName()
    {
        return $this->street_name;
    }

    public function getCity()
    {
        return $this->city;
    }
}
