<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add Room Type">
      <form id="add-form" action=" {{ route('system.setting.rooms.type.store') }}" method="post">
        @csrf
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96">
            <x-input type="text" id="name" name="name" placeholder="Room Name"/>
            <x-input type="text" id="max_occupancy" name="max_occupancy" placeholder="Max Occupancy"/>
            <x-input type="number" id="price" name="price" placeholder="Price"/>
            <label for="add_modal" class="btn btn-primary w-full">Add Type</label>
            <x-passcode-modal title="Add Type Confirmation" id="add_modal" formId="add-form" />        
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>