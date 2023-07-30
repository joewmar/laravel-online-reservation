
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit {{$service_menu->title}}" back=true>
    <form id="update-form" action=" {{ route('system.menu.update', encrypt($service_menu->id)) }}" method="post" autocomplete="off">
      @csrf
      @method('PUT')
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="w-full md:w-96">
        <x-input type="text" id="title" name="title" placeholder="Title" value="{{$service_menu->title}}"/>
        <x-datalist-input id="type" name="type" placeholder="Type" value="{{$service_menu->type}}"/>, 
        <x-datalist-input id="category" name="category" placeholder="Category" value="{{$service_menu->category}}"/>
        {{-- <x-multiple-input type="text" id="inclusion" name="inclusion" placeholder="Inclusion" /> --}}
        <x-textarea id="inclusion" name="inclusion" placeholder="Inclusion (Item 1, Item 2)" value="{{$service_menu->inclusion}}" />
        <x-input type="number" id="no_day" name="no_day" placeholder="Number of days" value="{{$service_menu->days}}" />
        <x-input type="number" id="hrs" name="hrs" placeholder="Number of hours" value="{{$service_menu->hrs}}" />
        <x-input type="number" id="price" name="price" placeholder="Price" value="{{$service_menu->price}}" />
        <x-input type="number" id="pax" name="pax" placeholder="Number of Pax" value="{{$service_menu->pax}}" />
        <label for="add_modal" class="btn btn-primary w-full">Save</label>
        <x-passcode-modal title="Edit Menu Confirmation" id="add_modal" formId="update-form" />        
      </div>
    </div>
  </form>
  </x-system-content>
</x-system-layout>
