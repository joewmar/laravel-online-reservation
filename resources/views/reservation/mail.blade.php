<x-landing-layout>
    <h1>{{ $details['title'] }}</h1>
    <p>{{ $details['body'] }}</p>
    @if (isset($details['list']))
        <div class="overflow-x-auto">
        <table class="table">
          <!-- head -->
          <thead>
            <tr>
                <th>Tour</th>
                <th>Type</th>
                <th>Pax</th>
                <th>Price</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($collection as $item)
                <tr>
                    <td>{{$item['title']}}</td> 
                    <td>{{$item['type']}}</td> 
                    <td>{{$item['pax']}} pax</td> 
                    <td>{{number_format($item['price'], 2)}}</td> 
                </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
    <p>Thank you</p>
</x-landing-layout>