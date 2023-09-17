@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Walk-in', 'Other Booking', 'Gcash', 'PayPal'];
    $arrStatus = ['Pending', 'Confirmed', 'Check-in', 'Previous', 'Previous', 'Reshedule', 'Cancel'];
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation Information of {{$r_list->userReservation->name()}}" back=true>
        <form id="edit-info" method="POST" action="{{route('system.reservation.edit.information.update', encrypt($r_list->id))}}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <section class="w-full flex justify-center">
                <div class="w-96 my-8">
                    <x-input type="number" name="age" id="age" placeholder="{{$r_list->userReservation->name()}} Age" value="{{old('age') ?? $r_list->age}}"/>
                    <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{old('check_in') ?? $r_list->check_in}}"/>
                    <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" value="{{old('check_out') ?? $r_list->check_out}}" />
                    <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" selected="{{old('accommodation_type') ?? $r_list->accommodation_type}}" />
                    <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{old('pax') ?? $r_list->pax}}"/>
                    <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{old('payment_method') ?? $r_list->payment_method}}"/>
                    @if($r_list->status >= 3)
                        <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('status')] ?? $arrStatus[$r_list->status]}}" disabled=true />
                    @else
                        <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('status')] ?? $arrStatus[$r_list->status]}}"  />
                    @endif
                    <label for="info_modal" class="btn btn-primary btn-block">Save</label>
                    <x-passcode-modal title="Enter the correct passcode to save information for {{$r_list->userReservation->name()}}" id="info_modal" formId="edit-info" />

                </div>
            </section>
        </form>
    </x-system-content>
</x-system-layout>