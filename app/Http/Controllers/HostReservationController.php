<?php

namespace App\Http\Controllers;

use App\Http\Requests\HostReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Repository\Interfaces\HostReservationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;


class HostReservationController extends Controller
{

    protected $hostReservation;

    public function __construct(HostReservationRepositoryInterface $hostReservation){

        $this->hostReservation = $hostReservation;

    }

    public function index(HostReservationRequest $request){

        return $this->hostReservation->index($request) ;

    }


}
