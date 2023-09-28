<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Bank Transfer Reference ({{$bankTransfer[$key]['name']}})">
        <form id="edit-form" action=" {{ route('system.webcontent.update.payment.bnktr', encrypt($key)) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
                <div class="w-full md:w-96">
                    <x-input id="name" name="name" placeholder="Full Name" value="{{$bankTransfer[$key]['name'] ?? ''}}" />
                    <x-input type="text" id="acc_no" name="acc_no" placeholder="Account No." value="{{$bankTransfer[$key]['acc_no'] ?? ''}}" />  
                    <x-input type="tel" id="contact" name="contact" placeholder="Contact No." value="{{$bankTransfer[$key]['contact'] ?? ''}}" />  
                    <label for="edit_modal" class="btn btn-primary w-full">Save</label>
                    <x-passcode-modal title="Edit Reference Confirmation" id="edit_modal" formId="edit-form" />        
                </div>
            </div>
        </form>
    </x-system-content>
</x-system-layout>