<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.news.home')}}">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="w-full md:w-96">
          <article class="prose">
            <h1>{{$new->title}}</h1>
            <p class="text-lg"><span class="font-bold">Type:</span> {{$new->type()}}</p>
            @if(isset($new->from) && isset($new->to))
              <p class="text-lg"><span class="font-bold">Deadline:</span> {{ Carbon\Carbon::createFromFormat('Y-m-d', $new->from)->format('M j, Y')}} to {{ Carbon\Carbon::createFromFormat('Y-m-d', $new->to)->format('M j, Y')}}</p>
            @else
                <p class="text-lg"><span class="font-bold">Deadline:</span> Forever</p>
            @endif
          </article>
           <div class="flex justify-between w-full space-x-3 my-5">
              <div class="w-full">
                <a href="{{ route('system.news.announcement.edit', encrypt($new->id)) }}" class="btn btn-primary w-full">Edit</a>
              </div class="w-full">
              <div class="w-full">
                <label for="delete_modal" class="btn btn-outline btn-error w-full">Delete</label>
              </div>
           </div>
           <form id="delete-form" method="POST" action=" {{ route('system.news.announcement.destroy', encrypt($new->id)) }}" enctype="multipart/form-data">
            @csrf
            @method('DELETE')
            <x-passcode-modal title="Do you want remove this: {{$new->title}}" id="delete_modal" formId="delete-form"  />
          </form>
        </div>
      </div>
    </x-system-content>
</x-system-layout>