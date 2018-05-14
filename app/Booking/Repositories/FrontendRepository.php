<?php

namespace App\Booking\Repositories;

use App\TouristObject;
use App\Booking\Interfaces\FrontendRepositoryInterface;


class FrontendRepository implements FrontendRepositoryInterface  {


    public function getObjectsForMainPage()
    {
        return TouristObject::all();
    }

}


