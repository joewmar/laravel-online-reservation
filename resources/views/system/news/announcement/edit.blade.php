@php
     $deadline = old('deadline') ?? 'forever';
    if(isset($new->from) && isset($new->to)){
      $deadline = old('deadline') ?? 'limit';
    }
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Annoucement {{$new->title}}">
      <form id="update-form" action=" {{ route('system.news.announcement.update', encrypt($new->id)) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
          <div x-data="{deadline: '{{$deadline}}'}" class="w-full md:w-96">
            <x-input type="text" id="title" name="title" placeholder="Annoucement" value="{{old('title') ?? $new->title}}" />
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
                @error('deadline')
                  <span class="text-error text-md">{{$message}}</span>
                @enderror
              </div>
              <div x-show="deadline == 'limit'" x-transition.500ms>
                <x-datetime-picker name="date_from" id="date_from" placeholder="Date From" class="flatpickr-room-today" value="{{old('date_from') ?? $new->from}}"/>
                <x-datetime-picker name="date_to" id="date_to" placeholder="Date To" class="flatpickr-room-today" value="{{old('date_to') ?? $new->to}}" />
              </div>
              <label for="edit_modal" class="btn btn-primary w-full">Save</label>
              <x-passcode-modal title="Edit Announcement Confirmation" id="edit_modal" formId="update-form" />        
          </div>
        </div>
      </form>
    </x-system-content>
</x-system-layout>