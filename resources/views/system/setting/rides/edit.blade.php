<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Let's Edit {{$ride->model}}">
      <form id="edit-form" action=" {{ route('system.setting.rides.update', encrypt($ride->id)) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
              <img id="show_img" src="{{$ride->image ? asset('storage/' . $ride->image)  : asset('images/avatars/no-avatar.png')}}" alt="{{$ride->model}}" />
            </div>
          </div>
          <x-file-input id="image" name="image" placeholder="Image"/>
        </div>
        <div class="w-full md:w-96">
            <x-input type="text" id="model" name="model" placeholder="Model Name" value="{{$ride->model}}"/>
            <x-input type="number" id="max_passenger" name="max_passenger" placeholder="Max Guest Passenger" value="{{$ride->max_passenger}}"/>
            <x-input type="number" id="many" name="many" placeholder="How Many Vehicle" value="{{$ride->many}}"/>
            <label for="edit_modal" class="btn btn-primary w-full">Save</label>
            <x-passcode-modal title="Edit Ride Confirmation" id="edit_modal" formId="edit-form" />        
        </div>
      </div>
    </form>
    </x-system-content>
</x-system-layout>