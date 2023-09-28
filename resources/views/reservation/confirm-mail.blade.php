<x-mail::message>
  # {{ $details['body'] }}
  Name: {{ $details['name'] }}<br>
  Age: {{ $details['age'] }}<br>
  Nationality: {{ $details['nationality'] }}<br>
  Country: {{ $details['country'] }}<br>
  Type: {{ $details['accommodation_type'] }}<br>
  Check-in: {{ $details['check_in'] }}<br>
  Check-out: {{ $details['check_out'] }}<br>
  Guest: {{ $details['pax'] }}<br>
  Guest going on tour: {{ $details['tour_pax'] }}<br>
  Room Assign: {{ $details['room_no'] }}<br>
  Room Type: {{ $details['room_type'] }} (Philippine Currency)<br>
  Room Rate: {{ $details['room_rate'] }} (Philippine Currency)<br>
  Total: {{ $details['total'] }}<br>
  Payment Method: {{ $details['payment_method'] }}<br>
  Tour Services:
  @if(isset($details['menu']))
    <ul>
        @foreach ($details['menu'] as $item)
            <li>{{ $item['title'] }} : {{ $item['price'] }} = {{$item['amount']}}</li>
        @endforeach
    </ul>
  @else
    No Tour Services<br>
  @endif
  Pay Downpayment:
  @if (!empty($details['payment_steps']))
     Before proceeding with the down payment, you must complete these steps<br><br>
    @foreach ($details['payment_steps'] as $item)
        {{$item}}<br>
    @endforeach
  @endif

  @if(isset($details['payment_link']))
    <x-mail::button :url="$details['payment_link']">
        Send Now
    </x-mail::button>
  @endif
  Payment Deadline: {{$details['payment_cutoff'] ?? ''}}<br>
  Note: You need to pay a downpayment for the reservation fee. If you fail to make the payment or , your reservation will be automatically canceled. <br><br>

  Thank you<br>
</x-mail::message>