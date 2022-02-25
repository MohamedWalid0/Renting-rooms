<?php


namespace App\Repository\Interfaces;

use App\Http\Requests\StoreUserReservationRequest;
use App\Models\Office;
use App\Models\Reservation;

interface UserReservationRepositoryInterface
{

    public function index($request);
    public function store($request  , Office $office);
    public function cancel(Reservation $reservation);

}
