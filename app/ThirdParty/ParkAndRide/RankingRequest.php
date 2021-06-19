<?php
/** DO NOT EDIT */

namespace App\ThirdParty\ParkAndRide;

use Illuminate\Support\Collection;

class RankingRequest
{
    private $ids;

    public function __construct(Collection $ids)
    {
        $this->ids = $ids;
    }

    public function getIds(): Collection
    {
        return $this->ids;
    }
}
