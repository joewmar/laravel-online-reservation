<x-mail::message>
  # Dear {{$details['name']}}
  {{ $details['title'] }}<br>
  {{ $details['body'] }}<br>

  @if (isset($details['note']))
  Note: {{ $details['note'] }}<br>
  @endif
  @if (isset($details['payment_cutoff']))
  New Payment deadline: {{ $details['payment_cutoff'] }}<br>
  @endif
  @if(isset($details['link']) && !empty($details['link']))
  <x-mail::button :url="$details['link']">
      Send Again
  </x-mail::button>
  @endif
  Thank you<br>
</x-mail::message>
