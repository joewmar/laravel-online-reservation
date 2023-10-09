<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="pt-24 w-full h-screen gap-5">
            <div class="p-14">
                <div class="flex justify-end">
                    <a href="{{route('user.notifications.mark-as-read')}}" class="btn btn-error btn-xs md:btn-sm {{empty($myNotif) ? 'btn-disabled' : ''}}">Mark All as Read</a>
                </div>
                <div class="overflow-x-auto">
                  <table class="table">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($myNotif as $notif)
                          <tr>
                              <th>{{$loop->index+1}}</th>
                              <th>{{Str::limit($notif->data['message'], 30, '...')}}</th>
                              <td>{{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $notif->created_at,)->setTimezone('Asia/Manila')->format('M j, Y g:ia')}}</td>
                              <td>
                                  <label for="Notif{{$loop->index+1}}" class="btn btn-ghost btn-circle btn-sm">
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
              @foreach ($myNotif ?? [] as $notif)
                  <x-modal id="Notif{{$loop->index+1}}" title="{{$notif->data['message']}}" >
                      <p class="py-4 whitespace-pre-line">{{$notif->data['message']}}</p>
                      <form action="{{route('user.notifications.destroy', encrypt($notif->id))}}" method="post">
                          @csrf
                          @method('DELETE')
                          <div class="modal-action">
                              <button class="btn btn-error btn-sm">Mark as Read</button>
                          </div>
                      </form>
                  </x-modal>
              @endforeach
            </div>
        </section>
    </x-full-content>
    @push('scripts')
      <script>
      @if(isset($from) && isset($to))
          const mop = {
              from: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $from)->format('Y-m-d')}}',
              to: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $to)->format('Y-m-d')}}'
          };
      @else
          const mop = '2001-15-30';
      @endif
      const md = '{{Carbon\Carbon::now()->addDays(2)->format('Y-m-d')}}';
      </script>
      <script type="module" src="{{Vite::asset('resources/js/flatpickr.js')}}"></script>
  @endpush
</x-landing-layout>
