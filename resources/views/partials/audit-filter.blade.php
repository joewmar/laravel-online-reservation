@props(['roles' => []])
<x-modal id="srchmdl" noBottom>
    <form x-data="{gnrt: false}" action="{{route('system.setting.audit.search')}}" method="post">
        @csrf
        <div x-data="{roles: [], now: false, name: '{{request('name') ?? ''}}', dateStart: '', dateEnd: '', time: ''}" class="space-y-2">
            <details class="overflow-hidden rounded border border-gray-300 [&_summary::-webkit-details-marker]:hidden" >
                <summary class="flex cursor-pointer items-center justify-between gap-2 bg-white p-4 text-gray-900 transition" >
                    <span class="text-sm font-medium"> Name{{request('name') ? ': '. request('name') : ''}} </span>
            
                    <span class="transition group-open:-rotate-180">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-4 w-4"
                    >
                        <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                    </span>
                </summary>
            
                <div class="border-t border-gray-200 bg-white">
                    <div class="border-t border-gray-200 p-4">
                        <label for="FilterSearch" class="flex items-center gap-2">
                            <input
                                type="search"
                                :name="name != '' ? 'name' : '' "
                                id="FilterSearch"
                                x-model="name"
                                placeholder="Search Full Name"
                                class="w-full rounded-md border-gray-200 shadow-sm sm:text-sm"
                            />
                        </label>
            
                    </div>
                </div>
            </details>
            
            <details class="overflow-hidden rounded border border-gray-300 [&_summary::-webkit-details-marker]:hidden" >
                <summary class="flex cursor-pointer items-center justify-between gap-2 bg-white p-4 text-gray-900 transition" >
                    <span class="text-sm font-medium"> Roles </span>
            
                    <span class="transition group-open:-rotate-180">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-4 w-4"
                    >
                        <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                    </span>
                </summary>
            
                <div class="border-t border-gray-200 bg-white">
                    <header class="flex items-center justify-between p-4">
                    <span class="text-sm text-gray-700" x-text="roles.length + ' Selected'"></span>
            
                    <button
                        type="button"
                        @click="roles = []"
                        class="text-sm text-gray-900 underline underline-offset-4"
                    >
                        Reset
                    </button>
                    </header>
            
                    <ul class="space-y-1 border-t border-gray-200 p-4">
                        @foreach ($roles ?? [] as $key => $item)
                            @php $value = encrypt($key); @endphp
                            <li>
                                <label for="{{Str::lower($item)}}" class="inline-flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="{{Str::lower($item)}}"
                                    x-model="roles"
                                    :name="roles.length != 0 ? 'roles[]' : '' "
                                    class="h-5 w-5 rounded checkbox checkbox-primary"
                                    @if(in_array($key, request('roles') ?? []))
                                        x-init="roles.push('{{$value}}')"
                                    @endif
                                    value="{{$value}}"
                                />
                    
                                <span class="text-sm font-medium text-gray-700">
                                    {{$item}}
                                </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </details>
            
            <details class="overflow-hidden rounded border border-gray-300 [&_summary::-webkit-details-marker]:hidden" >
                <summary class="flex cursor-pointer items-center justify-between gap-2 bg-white p-4 text-gray-900 transition" >
                    <span class="text-sm font-medium"> 
                        @if (request('now') ?? false)
                            Choose Date: Today 
                        @elseif (request('start') ?? false && request('end') ?? false)
                            Choose Date: Range 
                        @else
                            Choose Date 
                        @endif
                    
                    </span>
            
                    <span class="transition group-open:-rotate-180">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-4 w-4"
                    >
                        <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                        />
                    </svg>
                    </span>
                </summary>
            
                <div class="border-t border-gray-200 bg-white">
                    <header class="flex items-center justify-between p-4">
                    <span class="text-sm text-gray-700"> Date </span>
            
                    <button
                        type="button"
                        @click="dateStart = ''; dateEnd = ''; now = false; time = ''"
                        class="text-sm text-gray-900 underline underline-offset-4"
                    >
                        Reset
                    </button>
                    </header>
            
                    <div class="border-t border-gray-200 p-4">
                        <label for="{{Str::lower($item)}}" class="inline-flex items-center gap-2 mb-5">
                            <input
                                type="checkbox"
                                x-model="now"
                                name="now"
                                x-on:checked="dateStart = ''; dateEnd = '' "
                                class="h-5 w-5 rounded checkbox checkbox-primary"
                            />
                
                            <span class="text-sm font-medium text-gray-700">
                                Today
                            </span>
                        </label>
                        <div x-show="!now" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label for="FilterDateFrom" class="flex items-center gap-2">        
                                <input
                                    type="text"
                                    id="FilterDateFrom"
                                    placeholder="Start"
                                    x-model="dateStart"
                                    :name="!now ? 'start' : '' "
                                    class="w-full rounded-md border-gray-200 shadow-sm sm:text-sm flatpickr-reservation-one"
                                />
                            </label>
                
                            <label for="FilterDateTo" class="flex items-center gap-2">
                                <input
                                    type="text"
                                    id="FilterDateTo"
                                    placeholder="End"
                                    x-model="dateEnd"
                                    :name="!now ? 'end' : '' "
                                    class="w-full rounded-md border-gray-200 shadow-sm sm:text-sm flatpickr-reservation-one"
                                />
                            </label>
                        </div>
                    </div>
                </div>
            </details>
        </div>
        <div class="modal-action">
            <button class="btn btn-ghost" @click="gnrt = true">Generate</button>
            <button class="btn btn-primary">Go</button>
        </div>
        <input type="hidden" name="generate" :value="gnrt ? 'true' : 'false' ">

    </form>
</x-modal >
