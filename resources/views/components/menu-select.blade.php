@props(['name', 'id', 'placeholder'])
<div class="w-full">
    <input class="peer sr-only" id="{{$id}}" type="radio" tabindex="-1" name="{{$name}}"/>
    <label for="{{$id}}" class="block w-full rounded-lg border border-base-100 p-3 hover:border-primary peer-checked:border-primary peer-checked:bg-primary peer-checked:text-base-100" tabindex="0">
        <span class="text-sm font-medium"> {{$placeholder}} </span>
    </label>
</div>