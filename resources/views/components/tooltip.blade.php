@props(['title', 'color' => ''])
<div class="lg:tooltip tooltip-{{$color}}" data-tip="{{$title}}">
    {{$slot}}    
</div>