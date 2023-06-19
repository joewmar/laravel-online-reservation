<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add New Ride">
      <form id="add-form" action=" {{ route('system.setting.rides.store') }}" method="post" enctype="multipart/form-data">
        @csrf
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
              <img id="show_img" src="{{ asset('images/avatars/no-avatar.png')}}" alt="Vehicle Image" />
            </div>
          </div>
          <x-file-input id="image" name="image" placeholder="Image"/>
        </div>
        <div class="w-full md:w-96">
            <x-input type="text" id="model" name="model" placeholder="Model Name"/>
            <x-input type="number" id="max_passenger" name="max_passenger" placeholder="Max Guest Passenger"/>
            <x-input type="number" id="many" name="many" placeholder="How Many Vehicle"/>
            <label for="add_modal" class="btn btn-primary w-full">Add Ride</label>
            <x-passcode-modal title="Add Ride Confirmation" id="add_modal" formId="add-form" />        
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>