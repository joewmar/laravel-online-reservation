<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add New Room">
      <form action=" {{ route('system.setting.rooms.store') }}" method="post" enctype="multipart/form-data">
        @csrf
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
              <img id="room_img" src="{{ asset('images/avatars/no-avatar.png')}}" alt="Room Image" />
            </div>
          </div>
          <x-file-input id="image" name="image" placeholder="Image"/>
        </div>
        <div class="md:w-96">
            <x-input type="text" id="name" name="name" placeholder="Room Name"/>
            <x-input type="text" id="amenities" name="amenities" placeholder="Amenities (Always Use Comma)"/>
            <x-input type="text" id="description" name="description" placeholder="Description"/>
            <x-input type="number" id="min_occupancy" name="min_occupancy" placeholder="Min Guest" min="1"/>
            <x-input type="number" id="max_occupancy" name="max_occupancy" placeholder="Max Guest" min="1"/>
            <x-input type="text" id="location" name="location" placeholder="Location"/>
            <x-input type="number" id="many_room" name="many_room" placeholder="How Many Room" min="1"/>
            <button type="submit" class="btn btn-primary float-right">Add Room</button>
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>