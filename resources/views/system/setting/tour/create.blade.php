<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Tour Destination">
    <form id="add-form" action=" {{ route('system.setting.tour.store') }}" method="post" enctype="multipart/form-data">
      @csrf
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96 flex flex-col justify-center items-start">
        <div class="avatar">
          <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
            <img id="show_img" src="{{asset('images/avatars/no-avatar.png')}}" />
          </div>
        </div>
        <x-file-input id="image" name="image" placeholder="Image"/>
      </div>
      <div class="w-full md:w-96">
        <x-input type="text" id="name" name="name" placeholder="Name"/>
        <x-textarea id="description" name="description" placeholder="Description (Optional)"/>
        <x-input type="text" id="location" name="location" placeholder="Location"/>
        {{-- <x-select-multiple /> --}}
        <label for="add_modal" class="btn btn-primary w-full">Add Destination</label>
        <x-passcode-modal title="Add Destination Confirmation" id="add_modal" formId="add-form" />        
      </div>
    </div>
  </form>
  </x-system-content>
</x-system-layout>
