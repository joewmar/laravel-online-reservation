<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Gcash Reference of {{$gcash[$key]['name']}}">
        <form id="update_form" action=" {{ route('system.webcontent.update.payment.gcash', encrypt($key)) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
                <div class="w-96">
                    <x-drag-drop name="image" id="image" fileValue="{{isset($gcash[$key]['qrcode']) ? route('private.image', ['folder' => explode('/', $gcash[$key]['qrcode'])[0], 'filename' => explode('/', $gcash[$key]['qrcode'])[1]]) : asset('images/logo.png')}}" alt="QR Code of {{$gcash[$key]['name']}}" />
                </div>
                <div class="w-full md:w-96">
                    <x-input type="text" id="name" name="name" placeholder="Gcash Name" value="{{$gcash[$key]['name']}}"/>
                    <x-input type="number" id="gcash_number" name="gcash_number" placeholder="Gcash Number" value="{{$gcash[$key]['number']}}"/>
                    <label for="add_modal" class="btn btn-primary w-full" >Edit Gcash Reference</label>
                    <x-passcode-modal title="Edit Reference Confirmation" id="add_modal" formId="update_form" />        
                </div>
            </div>
        </form>
    </x-system-content>
</x-system-layout>