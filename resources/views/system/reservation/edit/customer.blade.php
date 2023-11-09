@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Walk-in', 'Other Online Booking', 'Gcash', 'PayPal', 'Bank Transfer'];
    $arrStatus = [0 => 'Pending', 1 => 'Confirmed', 2 => 'Check-in', 3 => 'Check-out', 5 => 'Cancel'];
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Customer Information ({{$r_list->userReservation->name()}})" back="{{route('system.reservation.show', encrypt($r_list->id))}}">
            <form id="edtcusfrm" method="POST" action="{{route('system.reservation.edit.customer.update', encrypt($user->id))}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <section class="w-full flex justify-center">
                    <div class="w-96 my-8">
                        <x-input name="first_name" id="first_name" placeholder="First Name" value="{{$user->first_name}}" />
                        <x-input name="last_name" id="last_name" placeholder="Last Name" value="{{$user->last_name}}" />
                        <x-birthday-input value="{{$user->birthday}}" />
                        <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{$user->country}}" />
                        <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{$user->nationality}}" />
                        <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$user->email}}" />
                        <x-input type="tel" name="contact" id="contact" placeholder="Contact Number" value="{{$user->contact}}" noRequired />
                        <div class="mb-5">
                            <x-drag-drop title="Validation ID" id="valid_id" name="valid_id" fileValue="{{!empty($user->valid_id) ? route('private.image', ['folder' => explode('/', $user->valid_id)[0], 'filename' => explode('/',$user->valid_id)[1]]) : ''}}" />
                        </div>
                        <label for="cusmdl" class="btn btn-primary btn-block">Save</label>
                        <x-passcode-modal title="Enter the correct passcode to save information for {{$r_list->userReservation->name()}}" id="cusmdl" formId="edtcusfrm" />
                    </div>
                </section>
            </form>
        </div>

    </x-system-content>
</x-system-layout>