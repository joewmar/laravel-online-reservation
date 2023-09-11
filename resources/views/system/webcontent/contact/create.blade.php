
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add New Contact">
        <section class="p-6 flex justify-center">
            <form x-data="{contactPerson: 'new'}" action="{{route('system.webcontent.contact.store')}}" method="post">
                @csrf
                <div class="w-96">
                    <section>
                        <x-input name="person" id="person" placeholder="Full Name of Contact Person" />
                        <x-input type="number" name="contact_no" id="contact_no" placeholder="Contact No." />
                        <x-input type="email" name="email" id="email" placeholder="Email Address" />
                        <x-input name="facebook_username" id="facebook_username" placeholder="facebook Username" />
                        <x-input type="number" name="whatsapp" id="whatsapp" placeholder="WhatsApp Number" />
                    </section>
                    <button class="btn btn-primary btn-block">Add</button> 
                </div>
            </form>
        </section>
    </x-system-content>
</x-system-layout>