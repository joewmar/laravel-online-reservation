<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add PayPal Reference">
        <form id="add-form" action=" {{ route('system.webcontent.store.payment.paypal') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
                <div class="w-96">
                    <x-drag-drop name="image" id="image" />
                </div>
                <div class="w-full md:w-96">
                    <x-input type="text" id="name" name="name" placeholder="PayPal Name"/>
                    <x-input type="number" id="paypal_number" name="paypal_number" placeholder="PayPal Mobile Number"/>
                    <x-input type="email" id="email" name="email" placeholder="PayPal Email"/>
                    <x-input type="text" id="username" name="username" placeholder="PayPal Username"/>
                    <label for="add_modal" class="btn btn-primary w-full">Add PayPal Reference</label>
                    <x-passcode-modal title="Add Reference Confirmation" id="add_modal" formId="add-form" />        
                </div>
            </div>
        </form>
    </x-system-content>
</x-system-layout>