@props(['common' => [], 'name' => 'reason', 'otherName' => 'message', 'value' => ''])
<div x-data="{reason: '{{$value ?? old($name)}}'}">
    <div class="form-control w-full">
        <label for="disaprove" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
            <select x-model="reason" name="{{$name}}" id="{{$name}}ID" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                <option value="" disabled selected>Please select</option>
                @foreach ($common as $item)
                    <option value="{{$item}}">{{$item}}</option>
                @endforeach
                <option value="Other">Other</option>
            </select>        
            <span id="disaprove" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                Reason To Disapprove
            </span>
        </label>
        <label class="label">
            <span class="label-text-alt">
                @error($name)
                    <span class="label-text-alt text-error">{{$message}}</span>
                @enderror
            </span>
        </label>
    </div>
    <div x-show="reason == 'Other' " class="my-5">
        <span class="text-xl font-medium ">Other</span>
        <div class="mt-3">
            <x-textarea name="{{$otherName}}" id="{{$otherName}}MID" placeholder="Reason Message" value="{{$value}}" />
        </div>
    </div> 
</div> 