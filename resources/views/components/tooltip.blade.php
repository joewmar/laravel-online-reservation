@props(['title', 'color' => '', 'position' => '', 'width' => 'w-full'])
<div class="{{$width}} tooltip tooltip-{{$color}} tooltip-{{$position}}" data-tip="{{$title}}">
    {{$slot}}
</div>