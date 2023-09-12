<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit {{$room_list->name}}">
      <form id="update-form" method="POST" action=" {{ route('system.setting.rooms.update', encrypt($room_list->id)) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center px-8 md:px-0">
          <div class="md:w-96 flex flex-col justify-start items-center">
            <x-drag-drop id="image" name="image" fileValue="{{$room_list->image}}" />
          </div>
        <div class="w-full md:w-96">
            <x-input type="text" id="name" name="name" placeholder="Room Name" value="{{$room_list->name}}"/>
            <x-input type="text" id="location" name="location" placeholder="Location" value="{{$room_list->location}}"/>
            <x-input type="number" id="max_occupancy" name="max_occupancy" placeholder="Max Guest" min="1" value="{{$room_list->max_occupancy}}"/>
            <x-input type="number" id="many_room" name="many_room" placeholder="How Many Room" min="1" value="{{$room_list->many_room}}"/>
            <label for="update_modal" class="btn btn-primary w-full">Save</label>
            <x-passcode-modal title="Edit Confirmation" id="update_modal" formId="update-form" />
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>