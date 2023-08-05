
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Add-on" back=true>
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="w-full md:w-96">
        <form id="add-form" action=" {{ route('system.menu.addons.store')}}" method="post">
          @csrf
          <x-input type="text" id="title" name="title" placeholder="Title"  />
          <x-input type="number" id="price" name="price" placeholder="Price" />
          <label for="add_modal" class="btn btn-primary w-full">Add Add-on</label>
          <x-passcode-modal title="Add Add-on Confirmation" id="add_modal" formId="add-form" />        
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
