<?php

namespace App\Observers;

use App\Models\Office;
use App\Models\Reservation;
use App\Notifications\NewHostReservation;
use App\Notifications\NewUserReservation;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ReservationObserver
{

    /**
     * @throws Throwable
     */

    public function creating(){

        if (request('office')) {
            throw_if(request('office')->user_id == auth()->id(),
                ValidationException::withMessages([
                    'office_id' => 'You cannot make a reservation on your own office'
                ])
            );
            throw_if(request('office')->hidden || request('office')->approval_status == Office::APPROVAL_PENDING,
                ValidationException::withMessages([
                    'office_id' => 'You cannot make a reservation on a hidden office'
                ])
            );
        }


    }

    public function created(Reservation $reservation){

        // Notification::send(auth()->user(), new NewUserReservation($reservation));
        // Notification::send($reservation->user, new NewHostReservation($reservation));

    }


    /**
     * @throws Throwable
     */
    public function updating(Reservation $reservation){

        throw_if($reservation->user_id != auth()->id() ||
            $reservation->status === Reservation::STATUS_CANCELLED ||
            $reservation->start_date < now()->toDateString(),
            ValidationException::withMessages([
                'reservation' => 'You cannot cancel this reservation'
            ])
        );
    }

}
