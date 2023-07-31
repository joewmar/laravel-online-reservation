<x-landing-layout>
    <x-navbar :activeNav="$activeNav" type="plain"/>

    <x-full-content>
        <section class="pt-24 flex flex-col justify-center items-center h-screen">
            <div class="tabs tabs-boxed">
                <a href="{{route('user.reservation.home')}}" class="tab {{!request()->has('tab') ? 'tab-active' : ''}}">Pending</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'confirmed']))}}" class="tab {{request('tab') == 'confirmed' ? 'tab-active' : ''}}">Confirmed</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'cin']))}}" class="tab {{request('tab') == 'cin' ? 'tab-active' : ''}}">Check-in</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'cout']))}}" class="tab {{request('tab') == 'cout' ? 'tab-active' : ''}}">Check-out</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'canceled']))}}" class="tab {{request('tab') == 'canceled' ? 'tab-active' : ''}}">Canceled</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'reshedule']))}}" class="tab {{request('tab') == 'reshedule' ? 'tab-active' : ''}}">Reschedule</a>
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'previous']))}}" class="tab {{request('tab') == 'previous' ? 'tab-active' : ''}}">Previous</a>
              </div>
            <div class="grid card bg-base-100 rounded-box place-items-center h-full w-5/6 ">
                <div class="overflow-x-auto w-full">
                  <table class="table w-full">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Pax</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                        @php
                          if(request('tab') == 'previous' || request('tab') == 'canceled'){
                            $lists = $archives;
                          }
                          else{
                            $lists = $reservation;
                          }
                        @endphp
                        @forelse ($lists as $list)
                        <tr>
                          <td>{{$list->id}}</td>
                          <td>{{$list->pax}} guest</td>
                          <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $list->check_in )->format('l, F j, Y') ?? 'None'}}</td>
                          <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $list->check_out )->format('l, F j, Y') ?? 'None'}}</td>
                          <td>{{$list->status()}} </td>
                          <th>
                            <button class="btn btn-error btn-xs">Cancel</button>
                            <button class="btn btn-warning btn-xs">Reschedule</button>
                          </th>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="7" class="text-center font-bold">No Record</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
          </section>
    </x-full-content>
</x-landing-layout>
