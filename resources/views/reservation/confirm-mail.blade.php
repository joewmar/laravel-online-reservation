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
  Payment Method: {{ $details['payment_method'] }}<br>
  Room Assign: {{ $details['room_no'] }}<br>
  Room Rate: {{$details['room_type']}} : ₱ {{ number_format($details['room_rate'], 2) }} (Philippine Currency)<br>
  
  @php
    if(isset($details['menu'])){
      echo "Tour Services:";
      echo "<ul>";
      foreach ($details['menu'] as $item) {
        echo "<li> ". $item['title'] . " : ₱ ". number_format($item['price'], 2). " = ₱ ". number_format($item['amount'], 2) . "</li>";
      }
      echo "</ul>";
    }
  @endphp

  Total: ₱{{ number_format($details['total'], 2) }}<br>
  @php
    if(!empty($details['payment_steps'])){
      echo "Pay Downpayment: Before proceeding with the down payment, you must complete these steps<br><br>";
      foreach ($details['payment_steps'] as $item) echo $item . "<br>";
      echo "<br>";
    }
    else{
      echo "Pay Downpayment:";
    }
  @endphp
  Payment Deadline: {{$details['payment_cutoff'] ?? ''}}<br>
  @if(isset($details['payment_link']))
    <x-mail::button :url="$details['payment_link']">
        Send Now
    </x-mail::button>
  @endif

  Note: You need to pay a downpayment for the reservation fee. If you fail to make the payment or , your reservation will be automatically canceled. <br><br>
  Thank you<br>
</x-mail::message>