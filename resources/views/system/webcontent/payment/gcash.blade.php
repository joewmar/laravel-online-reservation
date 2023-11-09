<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add Gcash Reference" back="{{route('system.webcontent.home','#payment')}}">
        <form id="add-form" action=" {{ route('system.webcontent.store.payment.gcash') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
                <div class="w-96">
                    <x-drag-drop name="image" title="QRCODE" id="image" fileValue="{{asset('images/logo.png')}}" />
                </div>
                <div class="w-full md:w-96">
                    <x-input type="text" id="name" name="name" placeholder="Gcash Name"/>
                    <x-input type="number" id="gcash_number" name="gcash_number" placeholder="Gcash Number"/>
                    <label for="add_modal" class="btn btn-primary w-full">Add Gcash Reference</label>
                    <x-passcode-modal title="Add Reference Confirmation" id="add_modal" formId="add-form" />        
                </div>
            </div>
        </form>
    </x-system-content>
</x-system-layout>