<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add Bank Transfer Reference" back="{{route('system.webcontent.home', '#payment')}}>
        <form id="add-form" action=" {{ route('system.webcontent.store.payment.bnktr') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
                <div class="w-full md:w-96">
                    <x-input id="name" name="name" placeholder="Full Name"/>
                    <x-input type="text" id="acc_no" name="acc_no" placeholder="Account No."/>  
                    <x-input type="tel" id="contact" name="contact" placeholder="Contact No."/>  
                    <label for="add_modal" class="btn btn-primary w-full">Add Bank Transfer Reference</label>
                    <x-passcode-modal title="Add Reference Confirmation" id="add_modal" formId="add-form" />        
                </div>
            </div>
        </form>
    </x-system-content>
</x-system-layout>