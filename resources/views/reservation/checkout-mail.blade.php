<x-mail::message>
  # Dear {{$details['name']}}
  {{ $details['title'] }}<br>
  {{ $details['body'] }}<br><br>

  Check Receipt
  <x-mail::button :url="$details['receipt_link']">
      View Receipt
  </x-mail::button>

  If you have time, you can feedback your experience
  <x-mail::button :url="$details['feedback_link']">
      Comment Here
  </x-mail::button>

  Thank you<br>
</x-mail::message>
