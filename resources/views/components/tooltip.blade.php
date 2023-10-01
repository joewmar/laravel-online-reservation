@props(['title', 'color' => '', 'position' => ''])
<div class="tooltip tooltip-{{$color}} tooltip-{{$position}}" data-tip="{{$title}}">
    {{$slot}}
</div>