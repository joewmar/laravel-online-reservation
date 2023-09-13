<x-system-layout :activeSb="$activeSb">
    @push('styles')
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
      {{-- Content  --}}
      <x-system-content title="Notification">
        <div class="overflow-x-auto">
            <table class="table">
              <!-- head -->
              <thead>
                <tr>
                  <th>                    
                    <a href="{{route('system.notifications.mark-as-read')}}" class="btn btn-error btn-sm">Mark All as Read</a>
                  </th>
                  <th>Title</th>
                  <th>Date</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse ($notifs as $notif)
                    <tr>
                        <th>{{$loop->index+1}}</th>
                        <th>{{$notif->data['title']}}</th>
                        <td>{{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $notif->created_at,)->setTimezone('Asia/Manila')->format('M j, Y g:ia')}}</td>
                        <td>
                            <label for="Notif{{$loop->index+1}}" class="btn btn-primary btn-sm">See More</label>
                        </td>
                    </tr>
                @empty
                    <tr colspan="4">
                        <th class="">No Notifications</th>
                    </tr>
                @endforelse

            
              </tbody>
            </table>
          </div>
        @foreach ($notifs ?? [] as $notif)
            <x-modal id="Notif{{$loop->index+1}}" title="{{$notif->data['title']}}" >
                <p class="py-4 whitespace-pre-line">{{$notif->data['message']}}</p>
                <div class="modal-action">
                    <button class="btn btn-error btn-sm">Mark as Read</button>
                </div>
            </x-modal>
        @endforeach 

      </x-system-content>
</x-system-layout>
