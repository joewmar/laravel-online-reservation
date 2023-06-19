<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit {{$room_list->name}}">
      <form id="update-form" method="POST" action=" {{ route('system.setting.rooms.update', encrypt($room_list->id)) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
              <img id="show_img" src="{{$room_list->image ? asset('storage/' . $room_list->image) : asset('images/avatars/no-avatar.png')}}" alt="{{$room_list->name}}"/>
            </div>
          </div>
          <x-file-input id="image" name="image" placeholder="Image"/>
        </div>
        <div class="md:w-96">
            <x-input type="text" id="name" name="name" placeholder="Room Name" value="{{$room_list->name}}"/>
            <x-input type="text" id="amenities" name="amenities" placeholder="Amenities (Always Use Comma)" value="{{$room_list->amenities}}"/>
            <x-input type="text" id="description" name="description" placeholder="Description" value="{{$room_list->many_room}}" value="{{$room_list->description}}"/>
            <x-input type="number" id="min_occupancy" name="min_occupancy" placeholder="Min Guest" min="1" value="{{$room_list->min_occupancy}}"/>
            <x-input type="number" id="max_occupancy" name="max_occupancy" placeholder="Max Guest" min="1" value="{{$room_list->max_occupancy}}"/>
            <x-input type="text" id="location" name="location" placeholder="Location" value="{{$room_list->location}}"/>
            <x-input type="number" id="many_room" name="many_room" placeholder="How Many Room" min="1" value="{{$room_list->many_room}}"/>
            <label for="update_modal" class="btn btn-primary w-full">Save</label>
            <x-passcode-modal title="Edit Confirmation" id="update_modal" formId="update-form" />
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>