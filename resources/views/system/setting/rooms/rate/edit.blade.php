<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit {{$room_rate->name}}">
    <form id="update-form" action=" {{ route('system.setting.rooms.rate.update', encrypt($room_rate->id)) }}" method="post">
      @csrf
      @method('PUT')
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96">
          <x-input type="text" id="name" name="name" placeholder="Room Name" value="{{$room_rate->name}}"/>
          <x-input type="number" id="occupancy" name="occupancy" placeholder="Number of Guest" value="{{$room_rate->occupancy}}"/>
          <x-input type="number" id="price" name="price" placeholder="Price" value="{{$room_rate->price}}"/>
          <label for="update_modal" class="btn btn-primary w-full">Save</label>
          <x-passcode-modal title="Edit Type Confirmation" id="update_modal" formId="update-form" />        
      </div>
    </div>
  </form>
  </x-system-content>
</x-system-layout>