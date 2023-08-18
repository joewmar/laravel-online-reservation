<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Website Content">
      <div x-data="{ wbtab: window.location.hash ? window.location.hash.substring(1) : 'hero' }" class="my-10 w-full">
        <div class="tabs tabs-boxed my-5 bg-transparent">
          <a @click="wbtab = 'hero' " :class="wbtab == 'hero' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Main Hero</a> 
          <a @click="wbtab = 'gallery' " :class="wbtab == 'gallery' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Gallery</a> 
          <a @click="wbtab = 'contact' " :class="wbtab == 'contact' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Contact Info</a> 
          <a @click="wbtab = 'reservation' " :class="wbtab == 'reservation' ? 'tab md:tab-lg tab-active' : 'tab md:tab-lg' ">Reservation</a>
        </div>
        <template x-if="wbtab === 'hero' ">
          <section class="p-6">
            <form id="hero-form" action="{{route('system.webcontent.image.hero')}}" method="post" class="col-span-full lg:col-span-3" enctype="multipart/form-data">                
              <div class="flex justify-between">
                <p class="font-medium text-xl">Main Hero Photos</p>
                <label for="add_pic_modal" class="btn btn-primary">Add Hero Picture</label>
                  @csrf
                  <x-modal id="add_pic_modal" title="Add Picture">
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
                                    <img src="{{asset('storage/'.$item)}}" alt="Avatar Tailwind CSS Component" />
                                  </div>
                                </div>
                              </label>
                              <div>
                                <div class="font-bold">Main Hero {{$loop->index + 1}}</div>
                              </div>
                            </div>
                          </td>
                          <td><a href="{{route('system.webcontent.image.hero.show', encrypt($key) )}}" class="btn btn-info btn-xs">View</a></td>
                        </tr>  
                      {{-- <x-modal id="hero_modal{{$loop->index + 1}}" title="Main Hero {{$loop->index + 1}}">
                          <img src="{{asset('storage/'.$item)}}" alt="Avatar Tailwind CSS Component" />
                      </x-modal> --}}
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
          <section class="p-6">
            <form id="add_gallery" action="{{route('system.webcontent.image.gallery')}}" method="post" class="col-span-full lg:col-span-3" enctype="multipart/form-data">                
              <div class="flex justify-between">
                <p class="font-medium text-xl">Gallery Photos</p>
                <label for="add_gallery_modal" class="btn btn-primary">Add Gallery</label>
                  @csrf
                  <x-modal id="add_gallery_modal" title="Add Picture" formID="add_gallery">
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
                                <div class="font-bold">Gallery Photo {{$loop->index + 1}}</div>
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
        <template x-if="wbtab === 'contact' ">
          <section class="p-6">
            <div class="flex justify-between">
              <p class="font-medium text-xl">Contact Information</p>
              <a href="{{route('system.webcontent.contact.create')}}" class="btn btn-primary">Add Contact</a>
            </div>
            <div x-data="{selectContact: []}" class="overflow-x-auto">
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
                    @forelse ($webcontents->contact ?? [] as $key => $item)
                        <tr>
                          <th>
                            <label>
                              <input type="checkbox" x-model="selectGallery" :name="selectGallery.includes('gly{{$loop->index + 1}}') ? 'remove_gallery[{{encrypt($key)}}]' : '' " class="checkbox checkbox-primary" value="gly{{$loop->index + 1}}" />
                            </label>
                          </th>
                          <td>
                            <div class="font-bold">{{$item['name']}}</div>
                          </td>
                          <td><a href="{{route('system.webcontent.contact.show', encrypt($key) )}}" class="btn btn-warning btn-xs">View</a></td>
                        </tr>  
                    @empty
                        <tr><td class="text-center font-bold" colspan="2">No Gallery Photo</td></tr>
                    @endforelse
                  </tbody>
        
                </table>
                <x-modal id="rc" title="Do you want to remove gallery images selected?" type="YesNo" formID="remove_contact_form">
                </x-modal>
              </form>
            </div>
          </section>
        </template>
      </div>
    </x-system-content>
</x-system-layout>