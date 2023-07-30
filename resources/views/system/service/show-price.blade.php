
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit {{$tour_menu->tourMenu->title}} - {{$tour_menu->type}} Price" back=true>
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="w-full md:w-96">
        <form id="edit-price-form" action="{{route('system.menu.update.price', ['id' => encrypt($tour_menu->menu_id), 'priceid' => encrypt($tour_menu->id) ])}}" method="POST">
          @method('PUT')
          @csrf
          <x-input type="text" id="type" name="type" placeholder="What title of this price?" min="1" value="{{$tour_menu->type ?? ''}}" />
          <x-input type="number" id="pax" name="pax" placeholder="Number of Guest" min="1" value="{{$tour_menu->pax ?? ''}}"/>
          <x-input type="number" id="price" name="price" placeholder="Price" min="1" value="{{$tour_menu->price ?? ''}}" />
          <label for="edit_price" class="btn btn-primary w-full">Save</label>
          <x-passcode-modal title="Edit Price Confirmation" id="edit_price" formId="edit-price-form" />        
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
