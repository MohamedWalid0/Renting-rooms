<?php


namespace App\Repository;

use App\Repository\Interfaces\HostReservationRepositoryInterface;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Symfony\Component\HttpFoundation\Response;

class HostReservationRepository implements HostReservationRepositoryInterface {


    public function index($request)
    {
        abort_unless(auth()->user()->tokenCan('reservations.show'),
            Response::HTTP_FORBIDDEN
        );

        $reservations = Reservation::query()
            ->whereRelation('office', 'user_id', '=', auth()->id())
            ->when(request('office_id'),
                fn($query) => $query->where('office_id', request('office_id'))
            )
            ->when(request('user_id'),
                fn($query) => $query->where('user_id', request('user_id'))
            )->when(request('status'),
                fn($query) => $query->where('status', request('status'))
            )->when(request('from_date') && request('to_date'),
                fn($query) => $query->betweenDates(request('from_date'), request('to_date'))
            )
            ->with(['office.featuredImage'])
            ->paginate(20);

        return ReservationResource::collection(
            $reservations
        );
    }

}
