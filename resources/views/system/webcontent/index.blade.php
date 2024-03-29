<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Website Content">
      <div class="w-full text-center">
          <span x-show="!document.querySelector('[x-cloak]')" class="loading loading-spinner loading-lg text-primary"></span>
      </div>
      <div x-data="{ wbtab: window.location.hash ? window.location.hash.substring(1) : 'hero' }" class="my-10 w-full" x-cloak>

        <div class="tabs tabs-boxed my-5 flex justify-center md:justify-start bg-transparent">
          <a @click="wbtab = 'hero' " :class="wbtab == 'hero' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Main Hero</a> 
          <a @click="wbtab = 'gallery' " :class="wbtab == 'gallery' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Gallery</a> 
          <a @click="wbtab = 'tour' " :class="wbtab == 'tour' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Tour</a> 
          <a @click="wbtab = 'contact' " :class="wbtab == 'contact' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Contact Info</a> 
          <a @click="wbtab = 'payment' " :class="wbtab == 'payment' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Online Payment Reference</a> 
          <a @click="wbtab = 'reservation' " :class="wbtab == 'reservation' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Reservation</a>
          
          <template x-if="wbtab === 'hero' ">
            <section class="p-6 w-full">
              <form id="hero-form" action="{{route('system.webcontent.image.hero')}}" method="post" class="col-span-full lg:col-span-3" enctype="multipart/form-data">                
                <div class="flex justify-between">
                  <p class="font-medium text-xl">Main Hero Photos</p>
                  <label for="add_pic_modal" class="btn btn-primary btn-sm md:btn-md">Add Hero Picture</label>
                    @csrf
                    <x-modal id="add_pic_modal" title="Add Picture" noBottom>
                      <x-drag-drop name="main_hero" id="main_hero" />
                      <div class="modal-action">
                        <button class="btn btn-primary">Add</button>
                      </div>
                    </x-modal>
                </div>
              </form>
    
              <div x-data="{select: []}" class="overflow-x-auto">
                <form id="remove_hero_form" action="{{route('system.webcontent.image.hero.destroy.all')}}" method="post">
                  @csrf
                  @method("DELETE")
                  <table class="table">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th><label for="rh" class="btn btn-error btn-sm" :disabled="!(Array.isArray(select) && select.length)">Remove</label></th>
                        <th>Hero</th>
                        <th>Action</th>
                      </tr>
                
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                      @forelse ($webcontents->hero ?? [] as $key => $item)
                          <tr>
                            <th>
                              <label>
                                <input type="checkbox" x-model="select" :name="select.includes('mh{{$loop->index + 1}}') ? 'remove_hero[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="mh{{$loop->index + 1}}" />
                              </label>
                            </th>
                            <td>
                              <div class="flex items-center space-x-3">
                                <label for="hero_modal{{$loop->index + 1}}" class="cursor-pointer">
                                  <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                      <img src="{{asset('storage/'.$item)}}" alt="Main Hero {{str_replace('main_hero', '', $key)}}" />
                                    </div>
                                  </div>
                                </label>
                                <div>
                                  <div class="font-bold">Main Hero {{str_replace('main_hero', '', $key)}}</div>
                                </div>
                              </div>
                            </td>
                            <td><a href="{{route('system.webcontent.image.hero.show', encrypt($key) )}}" class="btn btn-info btn-xs">View</a></td>
                          </tr>  
                        <x-modal id="hero_modal{{$loop->index + 1}}" title="Main Hero {{str_replace('main_hero', '', $key)}}" width>
                            <img src="{{asset('storage/'.$item)}}" alt="Main Hero {{str_replace('main_hero', '', $key)}}" />
                        </x-modal>
                      @empty
                          <tr><td class="text-center font-bold" colspan="2">No Hero Picture</td></tr>
                      @endforelse
                    </tbody>
          
                  </table>
                  <x-modal id="rh" title="Do you want to remove hero images?" type="YesNo" formID="remove_hero_form">
                  </x-modal>
                </form>
              </div>
            </section>
          </template>
          {{-- Gallery  --}}
          <template x-if="wbtab === 'gallery' ">
            <section class="p-6 w-full">
              <form id="add_gallery" action="{{route('system.webcontent.image.gallery')}}" method="post" class="col-span-full lg:col-span-3" enctype="multipart/form-data">                
                <div class="flex justify-between">
                  <p class="font-medium text-xl">Gallery Photos</p>
                  <label for="add_gallery_modal" class="btn btn-primary btm-sm md:btn-md">Add Gallery</label>
                    @csrf
                    <x-modal id="add_gallery_modal" title="Add Picture" formID="add_gallery" noBottom>
                      <x-drag-drop name="gallery" id="gallery" />
                      <div class="modal-action">
                        <button class="btn btn-primary">Add</button>
                      </div>
                    </x-modal>
                </div>
              </form>
    
              <div x-data="{selectGallery: []}" class="overflow-x-auto">
                <form id="remove_gallery_form" action="{{route('system.webcontent.image.gallery.destroy.all')}}" method="post">
                  @csrf
                  @method("DELETE")
                  <table class="table">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th><label for="rg" class="btn btn-error btn-sm" :disabled="!(Array.isArray(selectGallery) && selectGallery.length)">Remove</label></th>
                        <th>Gallery</th>
                        <th>Action</th>
                      </tr>
                
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                      @forelse ($webcontents->gallery ?? [] as $key => $item)
                          <tr>
                            <th>
                              <label>
                                <input type="checkbox" x-model="selectGallery" :name="selectGallery.includes('gly{{$loop->index + 1}}') ? 'remove_gallery[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="gly{{$loop->index + 1}}" />
                              </label>
                            </th>
                            <td>
                              <div class="flex items-center space-x-3">
                                  <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                      <img src="{{asset('storage/'.$item)}}" alt="Gallery Photo {{$loop->index + 1}}" />
                                    </div>
                                  </div>
                                <div>
                                  <div class="font-bold">Gallery Photo {{str_replace('gallery', '', $key)}}</div>
                                </div>
                              </div>
                            </td>
                            <td><a href="{{route('system.webcontent.image.gallery.show', encrypt($key) )}}" class="btn btn-warning btn-xs">View</a></td>
                          </tr>  
                      @empty
                          <tr><td class="text-center font-bold" colspan="2">No Gallery Photo</td></tr>
                      @endforelse
                    </tbody>
          
                  </table>
                  <x-modal id="rg" title="Do you want to remove gallery images selected?" type="YesNo" formID="remove_gallery_form">
                  </x-modal>
                </form>
              </div>
            </section>
          </template>
          <template x-if="wbtab === 'tour' ">
            <section x-data="{ttype: 'Main Tour'}" class="p-6 w-full">
              <div class="flex justify-between">
                <div class="flex justify-between">
                  <div class="w-52 md:w-96">
                    <x-select name="" id="" placeholder="" xModel="ttype" :value="['Main Tour', 'Side Tour']" :title="['Main Tour', 'Side Tour']" noRequired />
                  </div>
                </div>
                <label for="addtmdl" class="btn btn-primary">Add Picture</label>
                <x-modal id="addtmdl" title="Add Tour Picture" noBottom>
                  <form action="{{route('system.webcontent.image.tour')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <x-select name="tour_type" id="tour_type" placeholder="Type" :value="['Main Tour', 'Side Tour']" :title="['Main Tour', 'Side Tour']" />
                    <x-input name="tour" id="tour" placeholder="Tour Destination" />
                    <x-input name="location" id="location" placeholder="Location" noRequired />
                    <x-drag-drop id="image" name="image" title="Picture" />
                    <div class="modal-action">
                      <button class="btn btn-primary" @click="loader = true">Add</button>
                    </div>
                  </form>
                </x-modal>
              </div>
              {{-- <form id="hero-form" action="{{route('system.webcontent.image.hero')}}" method="post" class="col-span-full lg:col-span-3" enctype="multipart/form-data">                
                <div class="flex justify-between">
                  <p class="font-medium text-xl">Tour Destination Photos</p>
                  <label for="add_pic_modal" class="btn btn-primary btn-sm md:btn-md">Add Hero Picture</label>
                    @csrf
                    <x-modal id="add_pic_modal" title="Add Picture" noBottom>
                      <x-drag-drop name="main_hero" id="main_hero" />
                      <div class="modal-action">
                        <button class="btn btn-primary">Add</button>
                      </div>
                    </x-modal>
                </div>
              </form> --}}
              <div x-show="ttype === 'Main Tour'" x-data="{sTourM: []}" class="overflow-x-auto">
                <form id="rtm" action="{{route('system.webcontent.image.tour.destroy.main.all')}}" method="post">
                  @csrf
                  @method("DELETE")
                  <table class="table">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th><label for="rt" class="btn btn-error btn-sm" :disabled="!(Array.isArray(sTourM) && sTourM.length)">Remove</label></th>
                        <th>Main Tour</th>
                        <th>Action</th>
                      </tr>
                
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                      @forelse ($webcontents->tour['mainTour'] ?? [] as $key => $item)
                          <tr>
                            <th>
                              <label>
                                <input type="checkbox" x-model="sTourM" :name="sTourM.includes('tm{{$loop->index + 1}}') ? 'rtr[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="tm{{$loop->index + 1}}" />
                              </label>
                            </th>
                            <td>
                              <div class="flex items-center space-x-3">
                                <label for="tour_main{{$loop->index + 1}}" class="cursor-pointer">
                                  <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                      <img src="{{asset('storage/'.$item['image'])}}" alt="{{$item['title']}}" />
                                    </div>
                                  </div>
                                </label>
                                <div>
                                  <div class="font-bold">{{$item['title']}}</div>
                                </div>
                              </div>
                            </td>
                            <td><a href="{{route('system.webcontent.image.tour.show', ['type' => encrypt('mainTour'), 'key' => encrypt($key)] )}}" class="btn btn-info btn-xs">View</a></td>
                          </tr>  
                        <x-modal id="tour_main{{$loop->index + 1}}" title="{{$item['title']}}" width>
                            <img src="{{asset('storage/'.$item['image'])}}" alt="{{$item['title']}}" />
                        </x-modal>
                      @empty
                          <tr><td class="text-center font-bold" colspan="2">No Main Tour Picture</td></tr>
                      @endforelse
                    </tbody>
          
                  </table>
                  <x-modal id="rt" title="Do you want to remove hero images?" type="YesNo" formID="rtm" loader>
                  </x-modal>
                </form>
              </div>
              <div x-show="ttype === 'Side Tour'" x-data="{sTourS: []}" class="overflow-x-auto">
                <form id="remove_hero_form" action="{{route('system.webcontent.image.tour.destroy.side.all')}}" method="post">
                  @csrf
                  @method("DELETE")
                  <table class="table">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th><label for="rts" class="btn btn-error btn-sm" :disabled="!(Array.isArray(sTourS) && sTourS.length)">Remove</label></th>
                        <th>Side Tour</th>
                        <th>Action</th>
                      </tr>
                
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                      @forelse ($webcontents->tour['sideTour'] ?? [] as $key => $item)
                          <tr>
                            <th>
                              <label>
                                <input type="checkbox" x-model="sTourS" :name="sTourS.includes('ts{{$loop->index + 1}}') ? 'rts[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="ts{{$loop->index + 1}}" />
                              </label>
                            </th>
                            <td>
                              <div class="flex items-center space-x-3">
                                <label for="tour_side{{$loop->index + 1}}" class="cursor-pointer">
                                  <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                      <img src="{{asset('storage/'.$item['image'])}}" alt="{{$item['title']}}" />
                                    </div>
                                  </div>
                                </label>
                                <div>
                                  <div class="font-bold">{{$item['title']}}</div>
                                </div>
                              </div>
                            </td>
                            <td><a href="{{route('system.webcontent.image.tour.show',['type' => encrypt('sideTour'), 'key' => encrypt($key)] )}}" class="btn btn-info btn-xs">View</a></td>
                          </tr>  
                        <x-modal id="tour_side{{$loop->index + 1}}" title="{{$item['title']}}" width>
                            <img src="{{asset('storage/'.$item['image'])}}" alt="{{$item['title']}}" />
                        </x-modal>
                      @empty
                          <tr><td class="text-center font-bold" colspan="2">No Side Tour Picture</td></tr>
                      @endforelse
                    </tbody>
          
                  </table>
                  <x-modal id="rts" title="Do you want to remove hero images?" type="YesNo" formID="remove_hero_form">
                  </x-modal>
                </form>
              </div>
            </section>
          </template>
          <template x-if="wbtab === 'contact' ">
            <section x-data="{ctType: 'Main Contact'}" class="p-6">
              <div class="flex justify-between">
                <div class="flex justify-between">
                  <div class="w-52 md:w-96">
                    <x-select name="wala" id="walaID" placeholder="" xModel="ctType" :value="['Main Contact', 'Other Contacts']" :title="['Main Contact', 'Other Contacts']" noRequired />
                  </div>
                </div>
                <a x-show="ctType === 'Other Contacts'" href="{{route('system.webcontent.contact.create')}}" class="btn btn-primary">Add Contact</a>
              </div>
              <div x-show="ctType === 'Main Contact'">
                @if (isset($webcontents->contact['main']))
                  <div class="overflow-x-auto">
                    <table class="table">
                      <tbody>
                        <tr>
                          <th>Email Address</th>
                          <td>{{$webcontents->contact['main']['email'] }}</td>
                          <td></td>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Contact Number</th>
                          <td>{{$webcontents->contact['main']['contactno'] }}</td>
                          <td></td>
                          <td></td>
                        </tr>
                        <tr>
                          <th>Facebook</th>
                          <td>{{$webcontents->contact['main']['fbuser']}}</td>
                          <td></td>
                          <td></td>
                        </tr>
                        <tr>
                          <th>WhatsApp Contact No</th>
                          <td>{{$webcontents->contact['main']['whatsapp']}}</td>
                          <td></td>
                          <td></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="flex justify-end">
                      <label for="editMCT_modal" class="btn btn-info">Update</label>
                      <x-modal id="editMCT_modal" title="Edit Main Contact">
                        <form action="{{route('system.webcontent.main.contact.update')}}" method="post">
                          @csrf
                          @method('PUT')
                          <x-input type="email" id="email" name="email" placeholder="Email Address" value="{{$webcontents->contact['main']['email'] }}" />
                          <x-input type="number" id="contact" name="contact" placeholder="Contact Number" value="{{$webcontents->contact['main']['contactno'] }}" />
                          <x-input type="number" id="whatsapp_number" name="whatsapp_number" placeholder="WhatsApp Number" value="{{$webcontents->contact['main']['whatsapp'] }}" />
                          <x-input type="url" id="facebook_link" name="facebook_link" placeholder="Facebook Link (Facebook Profile Page)" value="{{$webcontents->contact['main']['fbuser'] }}" />
                          <div class="modal-action">
                            <button class="btn btn-primary">Save</button>
                          </div>
                        </form>
                      </x-modal>
                  </div>
                @else
                  <label for="addMCT_modal" class="btn btn-primary">Add Main Contact</label>
                  <x-modal id="addMCT_modal" title="Add Main Contact">
                    <form action="{{route('system.webcontent.main.contact.store')}}" method="post">
                      @csrf
                      <x-input type="email" id="email" name="email" placeholder="Email Address" />
                      <x-input type="number" id="contact"  name="contact" placeholder="Contact Number" />
                      <x-input type="number" id="whatsapp_number" name="whatsapp_number" placeholder="WhatsApp Number" />
                      <x-input type="url" id="facebook_link" name="facebook_link" placeholder="Facebook Link (Facebook Profile Page)" />
                      <div class="modal-action">
                        <button class="btn btn-primary">Add</button>
                      </div>
                    </form>
                  </x-modal>
                @endif
              </div>
              <div x-show="ctType === 'Other Contacts'" x-data="{selectContact: []}" class="overflow-x-auto">
                <form id="remove_contact_form" action="{{route('system.webcontent.contact.destroy.all')}}" method="post">
                  @csrf
                  @method("DELETE")
                  <table class="table">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th><label for="rc" class="btn btn-error btn-sm" :disabled="!(Array.isArray(selectContact) && selectContact.length)">Remove</label></th>
                        <th>Contact Name</th>
                        <th>Action</th>
                      </tr>
                
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                      @forelse ($webcontents->contact['other'] ?? [] as $key => $item)
                          <tr>
                            <th>
                              <label>
                                <input type="checkbox" x-model="selectContact" :name="selectContact.includes('ctnt{{$loop->index + 1}}') ? 'remove_contact[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="ctnt{{$loop->index + 1}}" />
                              </label>
                            </th>
                            <td>
                              <div class="font-bold">{{$item['name']}}</div>
                            </td>
                            <td><a href="{{route('system.webcontent.contact.show', encrypt($key) )}}" class="btn btn-warning btn-xs">View</a></td>
                          </tr>  
                      @empty
                          <tr><td class="text-center font-bold" colspan="3">No Contact Information</td></tr>
                      @endforelse
                    </tbody>
          
                  </table>
                  <x-modal id="rc" title="Do you want to remove contact information selected?" type="YesNo" formID="remove_contact_form">
                  </x-modal>
                </form>
              </div>
            </section>
          </template>
          <template x-if="wbtab === 'payment' ">
            <section x-data="{type: 'Gcash'}" class="p-6 w-full">
              <article class="my-5 w-full">
                <div class="flex justify-between">
                  <div class="w-52 md:w-96">
                    <x-select name="wala" id="walaID" placeholder="" xModel="type" :value="['Gcash', 'PayPal', 'Bank Transfer']" :title="['Gcash', 'Paypal', 'Bank Transfer']" noRequired />
                  </div>
                  <a x-show="type === 'Gcash'" href="{{route('system.webcontent.create.payment.gcash')}}" class="btn btn-primary btn-sm md:btn-md">Add Gcash</a>
                  <a x-show="type === 'PayPal'" href="{{route('system.webcontent.create.payment.paypal')}}" class="btn btn-primary btn-sm md:btn-md">Add PayPal</a>
                  <a x-show="type === 'Bank Transfer'" href="{{route('system.webcontent.create.payment.bnktr')}}" class="btn btn-primary btn-sm md:btn-md">Add Bank Transfer</a>
                </div>
                <div x-show="type === 'Gcash' " x-data="{priorityGcash: ''}" class="overflow-x-auto" x-transition.1000ms>
                  <form id="remove_gcash_reference" action="{{route('system.webcontent.priority.payment.gcash')}}" method="post">
                    @csrf
                    @method("PUT")
                    <table class="table">
                      <!-- head -->
                      <thead>
                        <tr>
                          <th><label for="gch" class="btn btn-info btn-sm" :disabled="priorityGcash === '' ">Set Priority</label></th>
                          <th>Gcash References</th>
                          <th>Action</th>
                        </tr>
                  
                      </thead>
                      <tbody>
                        <!-- row 1 -->
                        @forelse ($webcontents->payment['gcash'] ?? [] as $key => $item)
                            <tr>
                              <th>
                                <input type="radio" id="priority{{$key+1}}" x-model="priorityGcash" name="priority" class="radio radio-primary" value="{{encrypt($key)}}" />
                              </th>
                              <td>
                                <div class="flex flex-col">
                                  <div class="font-bold">{{$item['name']}}</div>
                                  @if(isset($item['priority']) && $item['priority'] === true)
                                  <div class="font-bold text-sm text-error">Currecnt Priority</div>
                                  @endif
                                </div>
                              </td>
                              <td><a href="{{route('system.webcontent.show.payment.gcash', encrypt($key) )}}" class="btn btn-warning btn-xs">View</a></td>
                            </tr>  
                        @empty
                            <tr><td class="text-center font-bold" colspan="2">No Gcash Reference</td></tr>
                        @endforelse
                      </tbody>
            
                    </table>
                    <x-modal id="gch" title="Do you want to save priority selected?" type="YesNo" formID="remove_gcash_reference">
                    </x-modal>
                  </form>
                </div>
                <div x-show="type === 'PayPal' " x-data="{priorityPayPal: []}" class="overflow-x-auto" x-transition.1000ms>
                  <form id="priority_paypal_reference" action="{{route('system.webcontent.priority.payment.paypal')}}" method="post">
                    @csrf
                    @method("PUT")
                    <table class="table">
                      <!-- head -->
                      <thead>
                        <tr>
                          <th><label for="ppl" class="btn btn-info btn-sm" :disabled="priorityPayPal.length === 0">Set Priority</label></th>
                          <th>PayPal References</th>
                          <th>Action</th>
                        </tr>
                  
                      </thead>
                      <tbody>
                        <!-- row 1 -->
                        @forelse ($webcontents->payment['paypal'] ?? [] as $key => $item)
                            <tr>
                              <th>
                                <input type="radio" id="priorityPpl{{$key+1}}" x-model="priorityPayPal" name="priority" class="radio radio-primary" value="{{encrypt($key)}}" />
                              </th>
                              <td>
                                <div class="flex flex-col">
                                  <div class="font-bold">{{$item['name']}}</div>
                                  @if(isset($item['priority']) && $item['priority'] === true)
                                    <div class="font-bold text-sm text-error">Current Priority</div>
                                  @endif
                                </div>
                              </td>
                              <td><a href="{{route('system.webcontent.show.payment.paypal', encrypt($key) )}}" class="btn btn-warning btn-xs">View</a></td>
                            </tr>  
                        @empty
                            <tr><td class="text-center font-bold" colspan="2">No PayPal Reference</td></tr>
                        @endforelse
                      </tbody>
            
                    </table>
                    <x-modal id="ppl" title="Do you want to PayPal Reference set priority selected?" type="YesNo" formID="priority_paypal_reference">
                    </x-modal>
                  </form>
                </div>
                <div x-show="type === 'Bank Transfer' " x-data="{priorityBT: []}" class="overflow-x-auto" x-transition.1000ms>
                  <form id="priority_bt_reference" action="{{route('system.webcontent.priority.payment.bnktr')}}" method="post">
                    @csrf
                    @method("PUT")
                    <table class="table">
                      <!-- head -->
                      <thead>
                        <tr>
                          <th><label for="bt" class="btn btn-info btn-sm" :disabled="priorityBT.length === 0">Set Priority</label></th>
                          <th>Bank Transfer References</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- row 1 -->
                        @forelse ($webcontents->payment['bankTransfer'] ?? [] as $key => $item)
                            <tr>
                              <th>
                                <input type="radio" id="priorityPpl{{$key+1}}" x-model="priorityBT" name="priority" class="radio radio-primary" value="{{encrypt($key)}}" />
                              </th>
                              <td>
                                <div class="flex flex-col">
                                  <div class="font-bold">{{$item['name']}}</div>
                                  @if(isset($item['priority']) && $item['priority'] === true)
                                    <div class="font-bold text-sm text-error">Current Priority</div>
                                  @endif
                                </div>
                              </td>
                              <td><a href="{{route('system.webcontent.show.payment.bnktr', encrypt($key) )}}" class="btn btn-warning btn-xs">View</a></td>
                            </tr>  
                        @empty
                            <tr><td colspan="3" class="text-center font-bold" colspan="2">No Bank Transfer Reference</td></tr>
                        @endforelse
                      </tbody>
            
                    </table>
                    <x-modal id="bt" title="Do you want to PayPal Reference set priority selected?" type="YesNo" formID="priority_bt_reference">
                    </x-modal>
                  </form>
                </div>
              </article>
            </section>
          </template>
          <template x-if="wbtab === 'reservation' ">
            <section class="p-6 w-full">
              <div class="flex justify-between">
                <p class="font-medium text-xl">Reservation Operations</p>
                {{-- <a href="{{route('system.webcontent.contact.create')}}" class="btn btn-primary">Add Contact</a> --}}
              </div>
              <form id="rsrvchfr" action="{{route('system.webcontent.reservation.operation')}}" method="post">
                @csrf
                @method('PUT')
                <div x-data="{allow: {{$webcontents->operation ?? true}}}" class="my-5 w-full">
                  <div class="my-3">
                    <input id="al" type="checkbox" x-model="allow" name="operation" class="checkbox checkbox-primary" :checked="allow"/>
                    <label for="al">Allow to make online reservation</label> 
                    @error('operation')
                        <p class="text-error">$message</p>
                    @enderror
                  </div>
                  <div x-show="!allow" x-transition>
                    <div class="py-5">
                      <x-input type="date" name="from" id="from" placeholder="Start Date Stop Operation" value="{{$webcontents->from ?? Carbon\Carbon::now()->format('Y-m-d')}}" />
                      <x-input type="date" name="to" id="to" placeholder="End Date Stop Operation" value="{{$webcontents->to ?? ''}}" />
                      <x-textarea name="reason" id="reason" placeholder="Reason to Stop Reservation?" value="{{$webcontents->reason ?? ''}}" />
                    </div>
                  </div>
                  <label for="reservationchmdl" class="btn btn-primary">Save</label>

                </div>
                <x-modal title="Do you want to Change Operation" id="reservationchmdl" type="YesNo" formID="rsrvchfr" noBottom >        
                </x-modal>        
              </form>
            </section>
          </template>
        </div>
      </div>
    </x-system-content>
</x-system-layout>