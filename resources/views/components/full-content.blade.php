@props(['alternative' => false])
@if ($alternative)
    <div {{$attributes->merge(['class' => 'min-h-screen min-w-screen bg-base-200'])}} {{$attributes}}>
@else
    <div {{$attributes->merge(['class' => 'min-h-screen min-w-screen'])}} {{$attributes}}>
@endif 
    {{$slot}}
</div>