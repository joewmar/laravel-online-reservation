<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add New Room" back>
      <form id="add-form" action=" {{ route('system.setting.rooms.store') }}" method="post" enctype="multipart/form-data">
        @csrf
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center px-8 md:px-0">
        <div class="md:w-96 flex flex-col justify-start items-center">
          <x-drag-drop id="image" name="image" noRequired />
        </div>
        <div class="w-full md:w-96">
            <x-input type="text" id="name" name="name" placeholder="Room Name"/>
            <x-input type="text" id="location" name="location" placeholder="Location" noRequired />
            <x-input type="number" id="max_occupancy" name="max_occupancy" placeholder="Max Guest" min="1" />
            <x-input type="number" id="many_room" name="many_room" placeholder="How Many Room" min="1" />
            <label for="add_modal" class="btn btn-primary w-full">Add Room</label>
            <x-passcode-modal title="Add Confirmation" id="add_modal" formId="add-form" />        
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>