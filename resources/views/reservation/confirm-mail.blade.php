<x-landing-layout>
  <x-full-content>
    <div class="flex justify-center items-center w-full h-screen bg-primary">
      <article class="prose shadow-2xl bg-base-200 w-96 h-72 p-10">
          <h3 class="font-bold">Dear {{$details['name']}}</h3>
          <h1>{{ $details['title'] }}</h1>
          <p>{{ $details['body'] }}</p>
          <p><strong>Name:</strong> {{ $details['name'] }}</p>
          <p><strong>Age:</strong>  {{ $details['age'] }}</p>
          <p><strong>Nationality:</strong>  {{ $details['nationality'] }}</p>
          <p><strong>Country:</strong>  {{ $details['country'] }}</p>
          <p><strong>Check-in:</strong>  {{ $details['check_in'] }}</p>
          <p><strong>Check-out:</strong>  {{ $details['check_out'] }}</p>
          <p><strong>Type:</strong>  {{ $details['accommodation_type'] }}</p>
          <p><strong>Room No:</strong>  {{ $details['room_no'] }}</p>
          <p><strong>Room Type:</strong>  {{ $details['room_type'] }}</p>
          <h3>Menu</h3>
          @if($details['menu'] != null)
            <ul>
                @foreach ($details['menu'] as $item)
                    <li>{{ $item['title'] }} ({{ $item['type'] }} {{ $item['pax'] }} pax) - {{ $item['price'] }}</li>
                @endforeach
            </ul>
          @else
            <ul>
                <li>No Tour menu</li>
            </ul>
          @endif
          <p><strong>Room Rate: </strong>  {{ $details['room_rate'] }}</p>
          <p><strong>Total: </strong>  {{ $details['total'] }}</p>
          <p><strong>Payment Method: </strong>  {{ $details['payment_method'] }}</p>
          <p>If you want downpayment: <a href="{{$details['payment_link'] ?? '#'}}">Click here<a></p>
          <p>Payment Deadline: {{$details['payment_cutoff'] ?? ''}}</p>
          <p>Note: You need to pay a downpayment for the reservation fee, if you fail to make the payment, your reservation will be automatically canceled.</p>
          <p>Thank you</p>
      </article>
    </div>
  </x-full-content>
</x-landing-layout>