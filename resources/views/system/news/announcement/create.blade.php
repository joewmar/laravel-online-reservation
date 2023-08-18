<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Add Annoucement">
      <form id="add-form" action=" {{ route('system.news.announcement.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
          <div x-data="{deadline: '{{old('deadline')}}'}" class="w-full md:w-96">
              <x-input type="text" id="title" name="title" placeholder="Annoucement"/>
              <div class="mb-5 space-x-3">
                <h1 class="text-lg font-medium mb-3">
                  Deadline
                  @error('deadline')
                    <sup class="text-error text-md">*{{$message}}</sup>
                  @enderror
                </h1>
                <label for="forever" class="space-x-1">
                  <input id="forever" x-model="deadline" type="radio" name="deadline" value="forever" class="radio radio-primary" />
                  <span>Forever</span>
                </label>
                <label for="limit" class="space-x-1">
                  <input id="limit" x-model="deadline" type="radio" name="deadline" value="limit" class="radio radio-primary"/>
                  <span>Limit</span>
                </label>

              </div>
              <div x-show="deadline == 'limit'" x-transition.500ms>
                <x-datetime-picker name="date_from" id="date_from" placeholder="Date From" class="flatpickr-reservation" value="{{old('date_from')}}"/>
                <x-datetime-picker name="date_to" id="date_to" placeholder="Date To" class="flatpickr-reservation flatpickr-input2" value="{{old('date_to')}}" />
              </div>
              <label for="add_modal" class="btn btn-primary w-full">Add Annoucement</label>
              <x-passcode-modal title="Add Annoucement Confirmation" id="add_modal" formId="add-form" />        
          </div>
        </div>
      </form>
    </x-system-content>
</x-system-layout>