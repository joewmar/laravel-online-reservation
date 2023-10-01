<x-mail::message>
  # Dear {{$details['name']}}
  {{ $details['title'] }}<br>
  {{ $details['body'] }}<br>

  New Payment deadline {{ $details['payment_cutoff'] }}<br>
  
  @if(isset($details['link']))
    <x-mail::button :url="$details['link']">
        Send Again
    </x-mail::button>
  @endif

  <br>Thank you<br>
</x-mail::message>
