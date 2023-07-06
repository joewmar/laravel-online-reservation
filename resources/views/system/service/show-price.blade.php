
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit ">
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96">
        <form action="{{route('system.menu.replace')}}" method="POST">
          @method('PUT')
          @csrf
          <h3 class="text-xl font-medium mb-4">Price Details</h3>
          <x-input type="text" id="type" name="type" placeholder="What title of this price?" min="1" />
          <x-input type="number" id="pax" name="pax" placeholder="Number of Guest" min="1" />
          <x-input type="number" id="price" name="price" placeholder="Price" min="1" />
          <label for="add_modal" class="btn btn-primary w-full">Add Menu</label>
          <x-passcode-modal title="Add Menu Confirmation" id="add_modal" formId="add-form" />        
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
