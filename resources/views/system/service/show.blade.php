<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96">
          <article class="prose">
            <h1>{{$service_menu->title}}</h1>
            <p class="text-lg"><span class="font-bold">Type:</span> {{$service_menu->amenities === null ? 'None': $service_menu->amenities}}</p>
            <p class="text-lg"><span class="font-bold">Category:</span> {{$service_menu->description === null ? 'None': $service_menu->description}}</p>
            <p class="text-lg"><span class="font-bold">Inclusion:</span> {{$service_menu->inclusion === null ? 'None': $service_menu->inclusion}}</p>
            <p class="text-lg"><span class="font-bold">Number of Day:</span> {{$service_menu->no_day === null ? 'None': $service_menu->no_day}}</p>
            <p class="text-lg"><span class="font-bold">Number of Hour/s:</span> {{$service_menu->hrs === null ? 'None': $service_menu->hrs}}</p>
            <p class="text-lg"><span class="font-bold">Price:</span> {{$service_menu->price === null ? 'None': number_format($service_menu->price, 2)}}</p>
            <p class="text-lg"><span class="font-bold">Number of Pax:</span> {{$service_menu->pax === null ? 'None': $service_menu->pax}}</p>
          </article>
           <div class="flex justify-between w-full space-x-3 my-5">
              <div class="w-full">
                <a href="{{ route('system.menu.edit', encrypt($service_menu->id)) }}" class="btn btn-primary w-full">Edit</a>
              </div class="w-full">
              <div class="w-full">
                <label for="delete_modal" class="btn btn-outline btn-error w-full">Delete</label>
              </div>
           </div>
           <form id="delete-form" method="POST" action=" {{ route('system.menu.destroy', encrypt($service_menu->id)) }}">
            @csrf
            @method('DELETE')
            <x-passcode-modal title="Do you want remove this: {{$service_menu->title}}" id="delete_modal" formId="delete-form"  />
          </form>
        </div>
      </div>
    </x-system-content>
</x-system-layout>