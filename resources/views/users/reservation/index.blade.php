<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="pt-24 flex flex-col justify-center items-center w-full h-screen gap-5">
            <div class="tabs tabs-boxed bg-transparent flex justify-center md:justify-start">
                <a href="{{route('user.reservation.home')}}" class="tab {{!request()->has('tab') ? 'tab-active' : ''}}">Pending</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'confirmed']))}}" class="tab {{request('tab') == 'confirmed' ? 'tab-active' : ''}}">Confirmed</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'cin']))}}" class="tab {{request('tab') == 'cin' ? 'tab-active' : ''}}">Check-in</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'cout']))}}" class="tab {{request('tab') == 'cout' ? 'tab-active' : ''}}">Check-out</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'cancel']))}}" class="tab {{request('tab') == 'cancel' ? 'tab-active' : ''}}">Cancel</a> 
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'reshedule']))}}" class="tab {{request('tab') == 'reshedule' ? 'tab-active' : ''}}">Reschedule</a>
                <a href="{{route('user.reservation.home', Arr::query(['tab' => 'all']))}}" class="tab {{request('tab') == 'all' ? 'tab-active' : ''}}">All</a>
              </div>
            <div class="card bg-base-100 rounded-box place-items-center w-5/6 ">
                <div class="overflow-x-auto w-full shadow-xl">
                  <table class="table table-xs md:table-md">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Pax</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- row 1 -->
                        @forelse ($reservation as $list)
                          <tr>
                            <td>{{str_replace('aar-', '', $list->id)}}</td>
                            <td>{{$list->pax}} guest</td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $list->check_in )->format('M j, Y') ?? 'None'}} - {{ \Carbon\Carbon::createFromFormat('Y-m-d', $list->check_out )->format('M j, Y') ?? 'None'}}</td>
                            <td>{{$list->status()}} </td>
                            <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $list->created_at)->format('M j, Y') ?? 'None'}}</td>
                            <th class="max-w-md flex items-center">
                              <div class="join join-vertical md:join-horizontal">
                                @if ($list->status !== 5 || $list->status !== 4)
                                  @if (!($list->downpayment() >= 1000))
                                    <label class="btn btn-error btn-xs join-item" disabled>Cancel</label>
                                    <label class="btn btn-warning btn-xs join-item" disabled>Reschedule</label>
                                  @else
                                    <label for="{{isset($list->message['cancel']) || isset($list->message['reschedule']) || (!($list->status >= 0 && $list->status < 2)) ? '' : 'cancel_modal'}}" class="btn btn-error btn-xs join-item" {{isset($list->message['cancel']) || isset($list->message['reschedule']) || (!($list->status >= 0 && $list->status < 2)) ? 'disabled' : ''}}>Cancel</label>
                                    <label for="{{isset($list->message['cancel']) || isset($list->message['reschedule']) || (!($list->status >= 0 && $list->status < 2)) ? '' : 'reschedule_modal'}}" class="btn btn-warning btn-xs join-item" {{!empty($list->message['cancel']) || !empty($list->message['reschedule']) || (!($list->status >= 1 && $list->status < 2)) ? 'disabled' : ''}}>Reschedule</label>
                                  @endif
                                @endif
                                <a href="{{route('user.reservation.show', encrypt($list->id))}}" class="btn btn-info btn-xs join-item" >Details</a>
                              </div>
                              @if ($list->downpayment() >= 1000 && ($list->status !== 5 || $list->status !== 4 ))
                                <x-modal id="{{isset($list->message['cancel']) ? 'disabledALl' : 'cancel_modal'}}" title="Why do you want to cancel?"> 
                                  <form id="cancelfrm" action="{{route('user.reservation.cancel', encrypt($list->id))}}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <x-textarea name="cancel_message" id="cancel_message" placeholder="Reason" />
                                    <div class="modal-action">
                                      <label for="cnclmdl" class="btn btn-error">Send Cancel</label>
                                    </div>

                                  </form>
                                </x-modal> 
                                
                                <x-modal id="cnclmdl" title="Do you want to Cancel?" type='YesNo' formID="cancelfrm">
                                </x-modal>

                                <x-modal id="reschedule_modal" title="Why do you want to reschedule?"> 
                                  <form id="rcshmdlfrm" action="{{route('user.reservation.reschedule', encrypt($list->id))}}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <p class="my-5">
                                      <span class="font-medium">Type: {{$list->accommodation_type}}</span>
                                    </p>
                                    <x-textarea name="reschedule_message" id="reschedule_message" placeholder="Reason to Reschedule Reservation"  />
                                    <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$list->check_in  ?? ''}}"/>
                                    <div x-show="({{$list->accommodation_type == 'Day Tour' ? 'false' : 'true'}})" x-transition>
                                      <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" value="{{$list->check_out  ?? ''}}" />
                                    </div>
                                    <div class="modal-action">
                                      <label for="rcshmdl" class="btn btn-warning">Send Reschedule</label>
                                    </div>

                                  </form>
                                </x-modal> 
                                
                                <x-modal id="rcshmdl" title="Do you want to change your schedule?" type='YesNo' formID="rcshmdlfrm">
                                </x-modal>
                              @endif
                            </th>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="6" class="text-center font-bold">No Record</td>
                          </tr>
                        @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="w-full">
                  {!! $reservation->links() !!}

                </div>
                @if(!request()->has('tab') ||(request()->has('tab') && request('tab') == 'confirmed'))
                  <div class="mt-5 w-full">
                    <div class="text-error">Note: You cannot cancel or reschedule if making the downpayment.</div>
                  </div>
                @endif
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
