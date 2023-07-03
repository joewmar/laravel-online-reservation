@php
    $countPricePlan = 0;
    if (old('type') != null || old('price') != null || old('pax') != null){
        foreach (old('type.*') ?? [] as $item){
          $countPricePlan += 1;
        }
    }
         
@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Tour Menu">
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96">
        <div class="flex">
          <form id="replace-form" action="{{route('system.menu.replace')}}" method="POST">
            @csrf
            <div class="flex justify-between w-full h-full">
              <x-select id="replace" name="replace" placeholder="What list do you want to replace" :value="$service_menus->pluck('id')" :title="$service_menus->pluck('title')" />
              <button onclick="event.preventDefault(); document.getElementById('replace-form').submit();" class="btn btn-primary">Replace Now</button>
            </div>
          </form>
        </div>
        <form id="add-form" action=" {{ route('system.menu.store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
          @csrf
          <x-input type="text" id="title" name="title" placeholder="Title" value="{{$service_menu == '' ? '' : $service_menu->title}}" />
          <x-datalist-input id="category" name="category" placeholder="Category"/>
          <x-textarea id="inclusion" name="inclusion" placeholder="Inclusion (Item 1, Item 2)"/>
          <x-input type="number" id="no_day" name="no_day" placeholder="Number of days" min="0.0"/>
          <x-input type="number" id="hrs" name="hrs" placeholder="Number of hours" min="1" />
          <x-input type="number" id="hrs" name="hrs" placeholder="Number of hours" min="1" />
          <div class="flex justify-between items-center mb-4">
            <div class="text-xl font-bold">Price Plan</div>
                <div x-data="{count: 1}" x-cloak>
                  <div class="join">
                    <button type="button"x-on:click="count === 1 ? count = 1 : count--" class="btn join-item btn-sm btn-primary">
                      &minus;
                    </button>
                    <input x-model.number="count" type="number" name="count" class="input input-primary input-sm join-item w-10" />
                    <button type="button" x-on:click="count++" class="btn join-item btn-sm btn-primary">
                      &plus;
                    </button>
                </div>
              </div>
          </div>

          {{-- <div class="border border-primary mb-8 rounded-md shadow-md p-8">
            <h3 class="text-xl font-medium mb-4">Price Details</h3>
            <x-input type="number" id="type" name="type" placeholder="What title of this price?" min="1" />
            <x-input type="number" id="pax" name="pax" placeholder="Number of Guest" min="1" />
            <x-input type="number" id="price" name="price" placeholder="Price" min="1" />
          </div> --}}
        
          {{-- <button class="btn btn-primary w-full">Back</button> --}}
          <button class="btn btn-primary w-full">Next</button>
          {{-- <label for="add_modal" class="btn btn-primary w-full">Add Menu</label>
          <x-passcode-modal title="Add Menu Confirmation" id="add_modal" formId="add-form" />         --}}
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
