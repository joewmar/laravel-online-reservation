<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit {{$room_type->name}}">
    <form id="update-form" action=" {{ route('system.setting.rooms.type.update', encrypt($room_type->id)) }}" method="post">
      @csrf
      @method('PUT')
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96">
          <x-input type="text" id="name" name="name" placeholder="Room Name" value="{{$room_type->name}}"/>
          <x-input type="text" id="max_occupancy" name="max_occupancy" placeholder="Max Occupancy" value="{{$room_type->max_occupancy}}"/>
          <x-input type="number" id="price" name="price" placeholder="Price" value="{{$room_type->price}}"/>
          <label for="update_modal" class="btn btn-primary w-full">Confirm</label>
          <x-passcode-modal title="Edit Type Confirmation" id="update_modal" formId="update-form" />        
      </div>
    </div>
  </form>
  </x-system-content>
</x-system-layout>