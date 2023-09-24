@props(['title', 'color' => '', 'position' => ''])
<div class="tooltip tooltip-{{$color}} tooltip-{{$position}} w-full" data-tip="{{$title}}">
    {{$slot}}
</div>