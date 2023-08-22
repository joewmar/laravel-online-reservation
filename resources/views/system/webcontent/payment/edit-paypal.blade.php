<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Gcash Reference of {{$paypal[$key]['name']}}">
        <form id="update_form" action=" {{ route('system.webcontent.update.payment.paypal', encrypt($key)) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
                <div class="w-96">
                    <x-drag-drop name="image" id="image" fileValue="{{isset($paypal[$key]['image']) ? route('private.image', ['folder' => explode('/', $paypal[$key]['image'])[0], 'filename' => explode('/', $paypal[$key]['image'])[1]]) : asset('images/logo.png')}}" alt="QR Code of {{$paypal[$key]['name']}}" />
                </div>
                <div class="w-full md:w-96">
                    <x-input type="text" id="name" name="name" placeholder="PayPal Name" value="{{$paypal[$key]['name']}}"/>
                    <x-input type="number" id="paypal_number" name="paypal_number" placeholder="PayPal Mobile Number" value="{{$paypal[$key]['number']}}"/>
                    <x-input type="email" id="email" name="email" placeholder="PayPal Email" value="{{$paypal[$key]['email']}}"/>
                    <x-input type="text" id="username" name="username" placeholder="PayPal Username" value="{{$paypal[$key]['username']}}"/>
                    <label for="add_modal" class="btn btn-primary w-full" >Save</label>
                    <x-passcode-modal title="Edit Reference Confirmation" id="add_modal" formId="update_form" />        
                </div>
            </div>
        </form>
    </x-system-content>
</x-system-layout>