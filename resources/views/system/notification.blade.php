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
                            <label for="Notif{{$loop->index+1}}" class="btn btn-primary btn-sm">
                              <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M8 256a56 56 0 1 1 112 0A56 56 0 1 1 8 256zm160 0a56 56 0 1 1 112 0 56 56 0 1 1 -112 0zm216-56a56 56 0 1 1 0 112 56 56 0 1 1 0-112z"/></svg>
                            </label>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="4" class="text-center">No Notifications</th>
                    </tr>
                @endforelse

            
              </tbody>
            </table>
          </div>
        @foreach ($notifs ?? [] as $notif)
            <x-modal id="Notif{{$loop->index+1}}" title="{{$notif->data['title']}}" >
                <form action="{{route('system.notifications.delete', encrypt($notif->id))}}" method="post">
                  @csrf
                  @method('DELETE')
                  <p class="py-4 whitespace-pre-line">{{$notif->data['message']}}</p>
                  <div class="modal-action">
                      <button class="btn btn-error btn-sm">Mark as Read</button>
                  </div>
              </form>
            </x-modal>
        @endforeach 

      </x-system-content>
</x-system-layout>
