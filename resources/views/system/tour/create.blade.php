<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Tour Package">
    <form id="add-form" action=" {{ route('system.setting.rooms.rate.store') }}" method="POST">
      @csrf
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96">
            <x-input type="text" id="name" name="name" placeholder="Rate Name"/>
            <x-input type="number" id="occupancy" name="occupancy" placeholder="Number of Guests"/>
            <x-input type="number" id="price" name="price" placeholder="Price"/>
            <label for="add_modal" class="btn btn-primary w-full">Add Package</label>
            <x-passcode-modal title="Add Rate Confirmation" id="add_modal" formId="add-form" />        
        </div>
      </div>
    </form>
  </x-system-content>
</x-system-layout>