<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UserReservationRequest;
use App\Models\Office;
use App\Models\Reservation;
use App\Repository\Interfaces\UserReservationRepositoryInterface;

class UserReservationController extends Controller
{


    protected $userReservation;

    public function __construct(UserReservationRepositoryInterface $userReservation)
    {
        $this->userReservation = $userReservation;
    }

    public function index(UserReservationRequest $request){

        return$this->userReservation->index($request) ;

    }

    public function store(StoreUserReservationRequest $request , Office $office){

        return $this->userReservation->store($request , $office);

    }

    public function cancel(Reservation $reservation){

        return $this->userReservation->cancel($reservation);

    }

}
