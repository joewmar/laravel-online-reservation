
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Tour Menu">
    <form id="add-form" action=" {{ route('system.menu.store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
      @csrf
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96">
        <x-input type="text" id="title" name="title" placeholder="Title"/>
        <x-datalist-input id="type" name="type" placeholder="Type"/>, 
        <x-datalist-input id="category" name="category" placeholder="Category"/>, 
        <x-textarea id="inclusion" name="inclusion" placeholder="Inclusion (Item 1, Item 2)"/>
        <x-input type="number" id="no_day" name="no_day" placeholder="Number of days"/>
        <x-input type="number" id="hrs" name="hrs" placeholder="Number of hours"/>
        <x-input type="number" id="price" name="price" placeholder="Price"/>
        <x-input type="number" id="pax" name="pax" placeholder="Number of Pax"/>
        <label for="add_modal" class="btn btn-primary w-full">Add Menu</label>
        <x-passcode-modal title="Add Menu Confirmation" id="add_modal" formId="add-form" />        
      </div>
    </div>
  </form>
  </x-system-content>
</x-system-layout>
