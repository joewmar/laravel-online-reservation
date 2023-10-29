<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.webcontent.home', '#tour')}}">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
              <img id="room_img" src="{{$tour['image'] ? asset('storage/' . $tour['image']) : asset('images/logo.png')}}" alt="{{$tour['title']}}"/>
            </div>
          </div>
        </div>
        <div class="w-full md:w-96">
          <article class="prose">
            <h1>{{$tour['title']}}</h1>
            @php $word = ''; @endphp 
            @if($type == 'mainTour')
                @php $word = 'Main Tour'; @endphp 
                <p class="text-lg"><span class="font-bold">Type:</span> {{$word}}</p>
            @elseif($type == 'sideTour')
                @php $word = 'Side Tour'; @endphp 
                <p class="text-lg"><span class="font-bold">Type:</span> {{$word}}</p>
            @endif
            <p class="text-lg"><span class="font-bold">Location:</span> {{$tour['location'] === null ? 'None': $tour['location']}}</p>
          </article>
           <div class="flex justify-between w-full space-x-3 my-5">
              <div class="w-full">
                <label for="tourupd" class="btn btn-primary w-full">Edit</label>
                <x-modal id="tourupd" title="Edit {{$tour['title']}}" >
                    <form action="{{ route('system.webcontent.image.tour.update', ['type' => encrypt($type), 'key' => encrypt($key)])}}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <x-input name="tour" id="tour" placeholder="Tour Destination" value="{{$tour['title']}}" noRequired />
                        <x-input name="location" id="location" placeholder="Location" value="{{$tour['location'] ?? ''}}" noRequired />
                        <x-drag-drop id="image" name="image" title="Picture" noRequired fileValue="{{asset('storage/'.$tour['image'])}}" />
                        <div class="modal-action">
                            <button class="btn btn-primary" @click="loader = true">Save</button>
                        </div>
                    </form>
                </x-modal>
              </div class="w-full">
              <div class="w-full">
                <label for="tourdl" class="btn btn-outline btn-error w-full">Delete</label>
                <x-modal id="tourdl" title="Do you want to delete" type="YesNo" formID="tourdlf" loader>
                </x-modal>
              </div>
           </div>
           <form id="tourdlf" method="POST" action=" {{ route('system.webcontent.image.tour.destroy.one', ['type' => encrypt($type), 'key' => encrypt($key)]) }}" enctype="multipart/form-data">
            @csrf
            @method('DELETE')
          </form>
        </div>
      </div>
    </x-system-content>
</x-system-layout>