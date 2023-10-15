@props(['title', 'color' => '', 'position' => ''])
<div class="w-full tooltip tooltip-{{$color}} tooltip-{{$position}}" data-tip="{{$title}}">
    {{$slot}}
</div>