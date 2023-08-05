
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add {{$addon->title}}" back=true>
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="w-full md:w-96">
        <form id="update-form" action=" {{ route('system.menu.addons.update', encrypt($addon->id))}}" method="post">
          @csrf
          @method('PUT')
          <x-input type="text" id="title" name="title" placeholder="Title" value="{{old('title') ?? $addon->title }}" />
          <x-input type="number" id="price" name="price" placeholder="Price" value="{{old('price') ?? $addon->price }}" />
          <label for="update_modal" class="btn btn-primary w-full">Save</label>
          <x-passcode-modal title="Edit Add-on Confirmation" id="update_modal" formId="update-form" />        
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
