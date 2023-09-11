@php
    $contactNo = $contact[$key]['contactno'] ?? [];
    $email = $contact[$key]['email'] ?? [];
    $facebook_user = $contact[$key]['fbuser'] ?? [];
    $whatsApp = $contact[$key]['whatsapp'] ?? [];
    $personID = $key;
@endphp

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="{{$contact[$key]['name']}}'s Contact Information">
        <div class="flex justify-end mt-5">
            <x-modal id="update_name" title="Change Name">
                <form id="update_name_form" action="{{route('system.webcontent.contact.update', encrypt($key))}}" method="post">
                    @csrf
                    @method('PUT')
                    <x-input name="name" id="name" placeholder="Change Name" value="{{old('name') ?? $contact[$key]['name']}}" />
                    <div class="modal-action">
                        <button @click="event.preventDefault(); document.getElementById('update_name_form').submit();" class="btn btn-primary">Change Name</button>
                    </div>
                </form>
            </x-modal>   
            <label for="update_name" class="btn btn-primary">Change Name</label>
        </div>
        <section class="p-6 flex justify-center">
            <div class="w-96">
                    <article class="prose">
                        <div>
                            <x-modal id="add_contact" title="Add Contact No.">
                                <form id="add_contact_form" action="{{route('system.webcontent.contact.update', encrypt($key))}}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <x-input type="number" name="contact" id="contact" placeholder="Contact No." />
                                    <div class="modal-action">
                                        <button @click="event.preventDefault(); document.getElementById('add_contact_form').submit();" class="btn btn-primary">Add Contact</button>
                                    </div>
                                </form>
                            </x-modal>
                            <h3>
                                Contact Numbers: 
                                <x-tooltip title="Add Contact No." color="primary">
                                    <label for="add_contact" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-plus"></i>
                                    </label> 
                                </x-tooltip>
                                <x-tooltip title="Remove Contact No." color="error">
                                    <label for="remove_contact" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-trash"></i>
                                    </label> 
                                </x-tooltip>
                            </h3> 
                            <ul class="marker:text-primary">
                                @forelse ($contactNo ?? [] as $item)
                                    <li>{{$item}}</li>
                                @empty
                                    <li>No Contact Numbers</li>
                                @endforelse
                            </ul>
                            
                        </div>
                        <div>
                            <x-modal id="add_email" title="Add Email Address">
                                <form id="add_email_form" action="{{route('system.webcontent.contact.update', encrypt($key))}}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <x-input type="email" name="email" id="email" placeholder="Email" />
                                    <div class="modal-action">
                                        <button @click="event.preventDefault(); document.getElementById('add_email_form').submit();" class="btn btn-primary">Add Email</button>
                                    </div>
                                </form>
                            </x-modal>
                            <h3>
                                Email
                                <x-tooltip title="Add Email Address" color="primary">
                                    <label for="add_email" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-plus"></i>
                                    </label> 
                                </x-tooltip>
                                <x-tooltip title="Remove Email" color="error">
                                    <label for="remove_email" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-trash"></i>
                                    </label> 
                                </x-tooltip>
                            </h3>
                            <ul class="marker:text-primary">
                                @forelse ($email as $item)
                                    <li>{{$item}}</li>
                                @empty
                                    <li>No Email Address</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <x-modal id="add_facebook" title="Add Facebook Link">
                                <form id="add_facebook_form" action="{{route('system.webcontent.contact.update', encrypt($key))}}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <x-input name="facebook_username" id="facebook_username" placeholder="Facebook Username" />
                                    <div class="modal-action">
                                        <button @click="event.preventDefault(); document.getElementById('add_facebook_form').submit();" class="btn btn-primary">Add Facebook Username</button>
                                    </div>
                                </form>
                            </x-modal>
                            <h3>
                                Facebook Link
                                <x-tooltip title="Add Facebook Link" color="primary">
                                    <label for="add_facebook" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-plus"></i>
                                    </label> 
                                </x-tooltip>
                                <x-tooltip title="Remvoe Facebook User" color="error">
                                    <label for="remove_fbuser" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-trash"></i>
                                    </label> 
                                </x-tooltip>
                            </h3>
                            <ul class="marker:text-primary">
                                @forelse ($facebook_user as $item)
                                    <li><a href="https://www.facebook.com/{{$item}}">https://www.facebook.com/{{$item}}</a></li>
                                @empty
                                    <li>No Facebook Link</li>
                                @endforelse
                            </ul>
                        </div>
                        <div>
                            <x-modal id="add_whatsapp" title="Add WhatsApp Contact No.">
                                <form id="add_whatsapp_form" action="{{route('system.webcontent.contact.update', encrypt($key))}}" method="post">
                                    @csrf
                                    @method('PUT')
                                    <x-input type="number" name="whatsapp" id="whatsapp" placeholder="WhatsApp Contact No." />
                                    <div class="modal-action">
                                        <button @click="event.preventDefault(); document.getElementById('add_whatsapp_form').submit();" class="btn btn-primary">Add WhatsApp Contact No.</button>
                                    </div>
                                </form>
                            </x-modal>
                            <h3>
                                WhatsApp Contact No.
                                <x-tooltip title="Add WhatsApp Contact No." color="primary">
                                    <label for="add_whatsapp" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-plus"></i>
                                    </label> 
                                </x-tooltip>
                                <x-tooltip title="Remvoe WhatsApp Contact No." color="error">
                                    <label for="remove_wapp" class="btn btn-ghost btn-circle">
                                        <i class="fa-solid fa-trash"></i>
                                    </label> 
                                </x-tooltip>
                            </h3>
                            <ul class="marker:text-primary">
                                @forelse ($whatsApp as $item)
                                    <li>{{$item}}</li>
                                @empty
                                    <li>No WhatsApp Contact No.</li>
                                @endforelse
                            </ul>
                        </div>
                    </article>
                    <x-modal id="remove_contact" title="Select Contact What you Remove">
                        <form x-data="{selectContact: []}" id="remove_contact_form" action="{{route('system.webcontent.contact.destroy.one', encrypt($personID))}}" method="post">
                            @csrf
                            @method('DELETE')
                            @forelse ($contactNo as $key => $item)
                                <label for="cn[{{$key}}]" class="block my-3">
                                    <input x-model="selectContact" type="checkbox" :name="selectContact.includes('cn{{$key}}') ? 'rcontact[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="cn{{$key}}" />
                                    <span>{{$item}}</span>
                                </label>
                            @empty
                                <span>No Contact No.</span>
                            @endforelse
                            <div class="modal-action">
                                <button :disabled="!(Array.isArray(selectContact) && selectContact.length)"  @click="event.preventDefault(); document.getElementById('remove_contact_form').submit();" class="btn btn-primary">Remove</button>
                            </div>
                        </form>
                    </x-modal>
                    <x-modal id="remove_email" title="Select Email What you Remove">
                        <form x-data="{selectEmail: []}" id="remove_email_form" action="{{route('system.webcontent.contact.destroy.one', encrypt($personID))}}" method="post">
                            @csrf
                            @method('DELETE')
                            @forelse ($email as $key => $item)
                                <label for="cn[{{$key}}]" class="block my-3">
                                    <input x-model="selectEmail" type="checkbox" :name="selectEmail.includes('el{{$key}}') ? 'remail[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="el{{$key}}" />
                                    <span>{{$item}}</span>
                                </label>
                            @empty
                                <span>No Email Address</span>
                            @endforelse
                            <div class="modal-action">
                                <button :disabled="!(Array.isArray(selectEmail) && selectEmail.length)"  @click="event.preventDefault(); document.getElementById('remove_email_form').submit();" class="btn btn-primary">Remove</button>
                            </div>
                        </form>
                    </x-modal>
                    <x-modal id="remove_fbuser" title="Select Email What you Remove">
                        <form x-data="{selectFBUser: []}" id="remove_fbuser_form" action="{{route('system.webcontent.contact.destroy.one', encrypt($personID))}}" method="post">
                            @csrf
                            @method('DELETE')
                            @forelse ($facebook_user as $key => $item)
                                <label for="cn[{{$key}}]" class="block my-3">
                                    <input x-model="selectFBUser" type="checkbox" :name="selectFBUser.includes('fb{{$key}}') ? 'rfb[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="fb{{$key}}" />
                                    <span>{{$item}}</span>
                                </label>
                            @empty
                                <span>No Facebook User</span>
                            @endforelse
                            <div class="modal-action">
                                <button :disabled="!(Array.isArray(selectFBUser) && selectFBUser.length)"  @click="event.preventDefault(); document.getElementById('remove_fbuser_form').submit();" class="btn btn-primary">Remove</button>
                            </div>
                        </form>
                    </x-modal>
                    <x-modal id="remove_wapp" title="Select Email What you Remove">
                        <form x-data="{selectWApp: []}" id="remove_wapp_form" action="{{route('system.webcontent.contact.destroy.one', encrypt($personID))}}" method="post">
                            @csrf
                            @method('DELETE')
                            @forelse ($whatsApp as $key => $item)
                                <label for="cn[{{$key}}]" class="block my-3">
                                    <input x-model="selectWApp" type="checkbox" :name="selectWApp.includes('wp{{$key}}') ? 'rwapp[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="wp{{$key}}" />
                                    <span>{{$item}}</span>
                                </label>
                            @empty
                                <span>No WhatsApp</span>
                            @endforelse
                            <div class="modal-action">
                                <button :disabled="!(Array.isArray(selectWApp) && selectWApp.length)"  @click="event.preventDefault(); document.getElementById('remove_wapp_form').submit();" class="btn btn-primary">Remove</button>
                            </div>
                        </form>
                    </x-modal>
            </div>
        </section>
    </x-system-content>
</x-system-layout>