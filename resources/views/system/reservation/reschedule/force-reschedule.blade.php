
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
        {{-- User Details --}}
       <div class="px-3 md:px-20">
        <x-profile :rlist="$r_list" />
        <div class="divider"></div>
        <article x-data="{reason: '{{old('reason')}}'}" class="text-md tracking-tight text-neutral my-5 w-auto">
            <form id="force-resched" action="{{route('system.reservation.force.reschedule.update', ['id' => encrypt($r_list->id), 'ncin='. encrypt($new_cin), 'ncout='. encrypt($new_cout)])}}" method="post">
                @csrf
                @method('PUT')
                <h2 class="text-2xl mb-5 font-bold">Reason To Reschedule</h2>
                <x-textarea name="message" id="message" placeholder="Message" />

                <div class="divider"></div>
                <div x-data="{force: false, rooms: {{$r_list->roomid ? '[' . implode(',', $r_list->roomid) .']' : '[]'}} }" class="mt-5 w-full">
                    <div class="flex justify-between">
                        <div>
                            <h2 class="text-xl font-bold">Choose the room with {{$r_list->pax}} guest</h2>
                            <div class="text-sm mt-5"><span class="font-medium">New Check-in:</span> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $new_cin)->format('F j, Y') ?? 'None'}}</div>
                            <div class="text-sm mb-5"><span class="font-medium">New Check-out:</span> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $new_cout )->format('F j, Y') ?? 'None'}}</div>
                        </div>
                        <div class="space-y-3">
                            <div class="dropdown dropdown-left">
                                <label tabindex="0" class="btn btn-ghost"><i class="fa-solid fa-circle-info"></i></label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li>                    
                                        <div class="flex items-center space-x-2">
                                            <label class="h-8 w-8 rounded-full bg-red-600 shadow-sm" ></label>
                                            <p class="font-medium">Full</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center space-x-2">
                                            <label class="h-8 w-8 rounded-full bg-primary shadow-sm" ></label>
                                            <p class="font-medium">Available</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <label for="ckforce" class="flex items-center mb-5">
                        <input id="ckforce" type="checkbox" checked="checked" name="force" x-model="force" class="checkbox checkbox-primary" x-on:checked="force = true" x-effect="if(!force) rooms = {{$r_list->roomid ? '[' . implode(',', $r_list->roomid) .']' : '[]'}}" />
                        <span class="font-bold ml-3">Force Assign</span> 
                    </label>      
                    <div class="flex flex-wrap flex-auto justify-center justify-self-stretch gap-3 w-full">
                        @forelse ($rooms as $key => $item)
                            <div :id="!force ? '' : '{{in_array($item->id, $reserved) ? 'disabledAll' : ''}}' " x-data="{reserved{{$loop->index+1}}: {{in_array($item->id, $reserved) ? 'true' : 'false'}} }">
                                <input x-ref="RoomRef" x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); }); " type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" x-on:checked="rooms.includes({{$item->id}})" :disabled="!force && reserved{{$loop->index+1}}"  />
                                <label for="RoomNo{{$item->room_no}}">
                                    <div :class="!force ? '{{in_array($item->id, $reserved) ? 'border-red-600' : 'border-primary'}}' : 'border-primary' " class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 cursor-pointer">
                                        <span class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-center" :class="!force ? '{{in_array($item->id, $reserved) ? 'bg-red-600 h-full' : 'bg-primary h-3'}}' : 'bg-primary h-3'">
                                            <h3 :class="!force ? '{{in_array($item->id, $reserved) ? 'block' : 'hidden'}}' : 'hidden' " class="text-base-100 block font-medium w-full text-center">Full</h3>
                                            <h4 class="text-primary-content hidden font-medium w-full text-center" >Room No. {{$item->room_no}} Selected</h4> 
                                            <div x-data="{count: {{in_array($item->id, $r_list->roomid ?? []) ? (int)$item->customer[$r_list->id] : 1}}}" class="join hidden">
                                                <button  @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                                <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" max="{{$item->room->max_occupancy}}"  readonly/>
                                                <button  @click="count < {{$item->room->max_occupancy}} || force  ? count++ : count = {{$item->room->max_occupancy}}"  type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                            </div>
                                        </span>
                                        <div class="sm:flex sm:justify-between sm:gap-4">
                                            <div>
                                                <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                                <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                                <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->max_occupancy}} capacity</p>
                                                @if($item->getAllPax() === (int)$item->room->max_occupancy - 1)
                                                    <p x-show="!force" class="mt-1 text-xs font-bold text-error">There is only {{$item->getAllPax()}} guest</p>
                                                @else
                                                    <p x-show="!force" class="mt-1 text-sm font-medium ">{{$item->getAllPax() > 0 ? $item->getAllPax() . ' guest availed' : 'No guest'}} </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @empty
                            <p class="text-2xl font-semibold">No Room Found</p>
                        @endforelse
                    </div>
                </div>
            <x-passcode-modal title="Force Reschedule Confirmation" id="frshMdl" formId="force-resched" />        
        </form>   
        </article>
        <div class="flex justify-end space-x-1">
            <label for="frshMdl" class="btn btn-error btn-sm">Force Cancel</label>
        </div>
       </div>
    </x-system-content>
</x-system-layout>