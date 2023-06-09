<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="w-full md:w-96">
          <article class="prose">
            <h1>{{$tour_list->title}}</h1>
            <p class="text-lg"><span class="font-bold">Category:</span> {{$tour_list->description === null ? 'None': $tour_list->description}}</p>
            <p class="text-lg"><span class="font-bold">Inclusion:</span> {{$tour_list->inclusion === null ? 'None': $tour_list->inclusion}}</p>
            <p class="text-lg"><span class="font-bold">Number of Day:</span> {{$tour_list->no_day === null ? 'None': $tour_list->no_day}}</p>
            <p class="text-lg"><span class="font-bold">Number of Hour/s:</span> {{$tour_list->hrs === null ? 'None': $tour_list->hrs}}</p>
            <p class="text-lg">
              <span class="font-bold">Price</span>
              <ul class="list-outside md:list-inside marker:text-primary">
                @foreach ($tour_list->tourMenuLists as $tour_menu)
                    <li class="space-x-5">
                      <strong>{{$tour_menu->type}} ({{$tour_menu->pax}} pax): </strong> P{{ number_format($tour_menu->price, 2)}}
                      <div class="{{$loop->index === 0 && count($tour_list->tourMenuLists) == 1 ? 'hidden' : ''}} dropdown dropdown-right dropdown-hover">
                        <label tabindex="0" class="btn btn-circle btn-ghost btn-sm text-primary">
                          <i class="fa-solid fa-ellipsis text-lg"></i>                        
                        </label>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                          <li class="font-bold">More Action: {{$tour_menu->type}} ({{$tour_menu->pax}} pax)
                          </li>
                          <li><a href="{{route('system.menu.edit.price', ['id' => encrypt($tour_list->id), 'priceid' => encrypt($tour_menu->id) ])}}" class="link link-primary">Edit</a></li>
                          <li>
                            <label for="delete_price" class="link link-error">Delete</label>
                          </li>
                        </ul>
                      </div>
                    </li>
                    <form id="delete-price-form" method="POST" action=" {{ route('system.menu.destroy.price',['id' => encrypt($tour_list->id), 'priceid' => encrypt($tour_menu->id) ]) }}">
                      @csrf
                      @method('DELETE')
                      <x-passcode-modal title="Do you want remove this: {{$tour_menu->type}}" id="delete_price" formId="delete-price-form"  />
                    </form>
                @endforeach
              </ul>
            </p>
            {{-- <p class="text-lg"><span class="font-bold">Number of Pax:</span> {{$tour_list->pax === null ? 'None': $tour_list->pax}}</p> --}}
          </article>
           <div class="flex justify-between w-full space-x-3 my-5">
              <div class="w-full">
                <a href="{{ route('system.menu.edit', encrypt($tour_list->id)) }}" class="btn btn-primary w-full">Edit</a>
              </div class="w-full">
              <div class="w-full">
                <label for="delete_modal" class="btn btn-outline btn-error w-full">Delete</label>
              </div>
           </div>
           <form id="delete-form" method="POST" action=" {{ route('system.menu.destroy', encrypt($tour_list->id)) }}">
            @csrf
            @method('DELETE')
            <x-passcode-modal title="Do you want remove this: {{$tour_list->title}}" id="delete_modal" formId="delete-form"  />
          </form>
        </div>
      </div>
    </x-system-content>
</x-system-layout>