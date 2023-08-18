<x-mail::message>
  # Dear {{$details['name']}}
  {{ $details['title'] }}<br>
  {{ $details['body'] }}<br><br>
  @if(isset($details['link']))
    <x-mail::button :url="$details['link']">
        Click here for feedback
    </x-mail::button>
  @endif
  Thanks,<br>
  {{ config('app.name') }}
</x-mail::message>
