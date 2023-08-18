@php
    $name = [];
    if(isset($contacts)){
        foreach ($contacts as $value) $name[] = $value['name'];
    }
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add New Contact">
        <section class="p-6 flex justify-center">
            <form x-data="{contactPerson: 'new'}" action="{{route('system.webcontent.contact.store')}}" method="post">
                @csrf
                <div class="w-96">
                    <div class="flex justify-start space-x-5 mb-5">
                        <label for="new_person" class="space-x-1">
                            <input id="new_person" type="radio" x-model="contactPerson" value="new" class="radio radio-primary" />
                            <span>New Person</span>
                        </label>
                        @if(isset($contacts))
                            <label for="new_person" class="space-x-2">
                                <input id="new_person" type="radio" x-model="contactPerson" value="currect" class="radio radio-primary" />
                                <span>Currect Person</span>
                            </label>
                        @endif
                    </div>
                    <template x-if="contactPerson === 'new' ">
                        <section>
                            <x-input name="person" id="person" placeholder="Full Name of Contact Person" />
                            <x-input type="number" name="contact_no" id="contact_no" placeholder="Contact No." />
                            <x-input type="email" name="email" id="email" placeholder="Email Address" />
                            <x-input name="facebook_username" id="facebook_username" placeholder="facebook Username" />
                            <x-input type="number" name="whatsapp" id="whatsapp" placeholder="WhatsApp Number" />
                        </section>
                    </template>
                    @if(isset($contacts))
                        <template x-if="contactPerson == 'currect'">
                            <section>
                                <x-select name="person" id="person" placeholder="Who Person" :value="$name" :title="$name" />
                                <x-input type="number" name="contact_no" id="contact_no" placeholder="Contact No." />
                                <x-input type="email" name="email" id="email" placeholder="Email Address" />
                                <x-input name="facebook_username" id="facebook_username" placeholder="facebook Username" />
                                <x-input type="number" name="whatsapp" id="whatsapp" placeholder="WhatsApp Number" />
                            </section>
                        </template>
                    @endif
                    <button class="btn btn-primary btn-block">Add</button> 
                </div>
            </form>
        </section>
    </x-system-content>
</x-system-layout>