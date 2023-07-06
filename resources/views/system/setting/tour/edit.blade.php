<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit {{$tour->name}}">
    <form id="add-form" action="{{ route('system.setting.tour.update', encrypt($tour->id))}}" method="post" enctype="multipart/form-data">
      @csrf
      @method('PUT')
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96 flex flex-col justify-center items-start">
        <div class="avatar">
          <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
            <img id="show_img" src="{{$tour->image ? asset('storage/'. $tour->image) : asset('images/avatars/no-avatar.png')}}" alt="{{$tour->name}} Img" />
          </div>
        </div>
        <x-file-input id="image" name="image" placeholder="Image"/>
      </div>
      <div class="w-full md:w-96">
        <x-input type="text" id="name" name="name" placeholder="Name" value="{{$tour->name}}"/>
        <x-textarea id="description" name="description" placeholder="Description (Optional)" value="{{$tour->description}}" />
        <x-input type="text" id="location" name="location" placeholder="Location" value="{{$tour->location}}"/>
        <label for="update_modal" class="btn btn-primary w-full">Save</label>
        <x-passcode-modal title="Edit Destination Confirmation" id="update_modal" formId="add-form" />        
      </div>
    </div>
  </form>
  </x-system-content>
</x-system-layout>
