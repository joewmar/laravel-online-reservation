@props(['data'])
@if($data->status() == "Pending")
    <a href="{{route('system.reservation.show.rooms', encrypt($data->id))}}" class="btn btn-secondary btn-xs">Confirm</a>
    <label class="btn btn-success btn-xs" disabled>Check-in</label>
    <label class="btn btn-info btn-xs" disabled>Check-out</label>
@elseif($data->status() == "Confirmed")
    <a class="btn btn-secondary btn-xs" disabled>Confirm</a>
    <label for="checkin" class="btn btn-success btn-xs">Check-in</label>
    <x-checkin name="{{$data->userReservation->name() ?? ''}}" :datas="$data" />
    <label class="btn btn-info btn-xs" disabled>Check-out</label>
@elseif($data->status() == "Check-in")
    <a href="" class="btn btn-secondary btn-xs" disabled>Confirm</a>
    <label class="btn btn-success btn-xs" disabled>Check-in</label>
    <label for="checkout" class="btn btn-warning btn-xs">Check-out</label>
    <x-checkout name="{{$data->userReservation->name() ?? ''}}" :datas="$data" />
@endif
<a href="{{route('system.reservation.show.cancel', encrypt($data->id))}}" class="btn btn-error btn-xs">Cancel</a>
<label for="checkout" class="btn btn-accent btn-xs">Reschedule</label>
