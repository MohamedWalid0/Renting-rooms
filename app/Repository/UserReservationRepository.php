<?php


namespace App\Repository;

use App\Http\Requests\StoreUserReservationRequest;
use App\Http\Requests\UserReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Notifications\NewHostReservation;
use App\Notifications\NewUserReservation;
use App\Pipelines\FilterByDate;
use App\Pipelines\FilterByOfficeId;
use App\Pipelines\FilterByStatus;
use App\Pipelines\FilterByUserId;
use App\Repository\Interfaces\UserReservationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;


class UserReservationRepository implements UserReservationRepositoryInterface {


    public function index($request){

        $pipes = [

            FilterByUserId::class ,
            FilterByOfficeId::class ,
            FilterByStatus::class ,
            FilterByDate::class ,

        ] ;


        abort_unless(auth()->user()->tokenCan('reservations.show'),
            Response::HTTP_FORBIDDEN
        );

        return ReservationResource::collection(
            app(Pipeline::class)
            ->send(Reservation::query())
            ->through($pipes)
            ->thenReturn()
            ->with(['office.featuredImage'])
            ->paginate(20)
        ) ;



    }

    public function store($request , Office $office){

        abort_unless(auth()->user()->tokenCan('reservations.make'),
            Response::HTTP_FORBIDDEN
        );

        $reservation = Cache::lock('reservations_office_'.$office->id, 10)->block(3, function () use ($request, $office) {
            $numberOfDays = Carbon::parse($request->end_date)->endOfDay()->diffInDays(
                    Carbon::parse($request->start_date)->startOfDay()
                ) + 1;

            if ($office->reservations()->activeBetween($request->start_date, $request->end_date)->exists()) {
                throw ValidationException::withMessages([
                    'office_id' => 'You cannot make a reservation during this time'
                ]);
            }

            $price = $numberOfDays * $office->price_per_day;

            if ($numberOfDays >= 28 && $office->monthly_discount) {
                $price = $price - ($price * $office->monthly_discount / 100);
            }

            return Reservation::create([
                'user_id' => auth()->id(),
                'office_id' => $office->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => Reservation::STATUS_ACTIVE,
                'price' => $price,
                'wifi_password' => Str::random()
            ]);

        });


        return ReservationResource::make(
            $reservation->load('office')
        );
    }

    public function cancel(Reservation $reservation){

        abort_unless(auth()->user()->tokenCan('reservations.cancel'),
            Response::HTTP_FORBIDDEN
        );

        $reservation->update([
            'status' => Reservation::STATUS_CANCELLED
        ]);

        return ReservationResource::make(
            $reservation->load('office')
        );
    }


}
