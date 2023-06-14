<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
              <img id="room_img" src="{{$room_list->image ? asset('storage/' . $room_list->image) : asset('images/avatars/no-avatar.png')}}" alt="{{$room_list->name}}"/>
            </div>
          </div>
        </div>
        <div class="md:w-96">
          <article class="prose">
            <h1>{{$room_list->name}}</h1>
            <p class="text-lg"><span class="font-bold">Amenities:</span> {{$room_list->amenities === null ? 'None': $room_list->amenities}}</p>
            <p class="text-lg"><span class="font-bold">Description:</span> {{$room_list->description === null ? 'None': $room_list->description}}</p>
            <p class="text-lg"><span class="font-bold">Occupancy:</span> {{$room_list->min_occupancy}} to {{$room_list->max_occupancy}} Guests</p>
            <p class="text-lg"><span class="font-bold">Location:</span> {{$room_list->location === null ? 'None': $room_list->location}}</p>
            <p class="text-lg"><span class="font-bold">How Many Rooms:</span> {{$room_list->many_room}}</p>
          </article>
           <div class="float-right space-x-3 my-5">
            <a href="{{ route('system.setting.rooms.edit', encrypt($room_list->id)) }}" class="btn btn-sm btn-primary">Edit</a>
            <a href="#delete-modal" class="btn btn-sm btn-ghost">Delete</a>
            <form id="delete-form" method="POST" action=" {{ route('system.setting.rooms.destroy', encrypt($room_list->id)) }}" enctype="multipart/form-data">
              @csrf
              @method('DELETE')
              <x-passcode-modal title="Delete Confirmation" id="delete-modal" formId="delete-form" />
            </form>
           </div>
        </div>
      </div>
    </x-system-content>
</x-system-layout>