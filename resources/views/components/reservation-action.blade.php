@props(['data'])
<div class="join">
    @if($data->status() == "Pending")
        <a href="{{route('system.reservation.show.rooms', encrypt($data->id))}}" class="btn btn-secondary btn-xs join-item">Confirm</a>
        <label class="btn btn-success btn-xs join-item" disabled>Check-in</label>
        <label class="btn btn-info btn-xs join-item" disabled>Check-out</label>
    @elseif($data->status() == "Confirmed")
        <a class="btn btn-secondary btn-xs join-item" disabled>Confirm</a>
        <label for="checkin" class="btn btn-success btn-xs join-item">Check-in</label>
        <x-checkin name="{{$data->userReservation->name() ?? ''}}" :datas="$data" />
        <label class="btn btn-info btn-xs join-item" disabled>Check-out</label>
    @elseif($data->status() == "Check-in")
        <a href="" class="btn btn-secondary btn-xs join-item" disabled>Confirm</a>
        <label class="btn btn-success btn-xs join-item" disabled>Check-in</label>
        <label for="checkout" class="btn btn-warning btn-xs join-item">Check-out</label>
        <x-checkout name="{{$data->userReservation->name() ?? ''}}" :datas="$data" />
    @endif
    <div class="dropdown dropdown-top dropdown-end">
        <label tabindex="0" class="btn btn-xs join-item">
            <i class="fa-solid fa-ellipsis"></i>
        </label>
        <ul tabindex="0" class="dropdown-content z-[50] menu p-2 shadow bg-base-100 rounded-box w-52">
          <li><a href="{{route('system.reservation.show.cancel', encrypt($data->id))}}">Cancel</a></li>
          <li><a href="{{route('system.reservation.show.reschedule', encrypt($data->id))}}">Reschedule</a></li>
        </ul>
    </div>
</div>
