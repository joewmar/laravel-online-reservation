<x-mail::message>
  # Dear {{$details['name']}}
  {{ $details['title'] }}<br>
  {{ $details['body'] }}<br><br>
  Thanks,<br>
  {{ config('app.name') }}
</x-mail::message>
