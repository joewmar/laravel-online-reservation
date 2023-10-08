@php
    $tours = [];
    foreach ($tour_menu as $key => $value) {
        $tours[$key] = $value;
    }
    foreach ($tour_addons as $key => $value) {
        $tours[$key] = $value;
    }
    // array_push($tour_menu, $tour_addons);
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="{{$r_list->userReservation->name()}}'s Tour " back=true>
        <section x-data="{tab: 'tour'}">
            <div class="flex justify-between items-center my-5">
                <div class="tabs">
                    <a class="tab tab-lifted" :class="tab == 'tour' ? 'tab-active' : '' " @click="tab = 'tour' ">Tour</a> 
                    <a class="tab tab-lifted" :class="tab == 'addons' ? 'tab-active' : '' " @click="tab = 'addons' ">Addons</a> 
                </div>
                <a href="{{route('system.reservation.show.addons', [encrypt($r_list->id), 'tab=TA'])}}" x-show="tab == 'tour'" class="btn btn-primary btn-sm">Add Tour</a>
                <a href="{{route('system.reservation.show.addons', encrypt($r_list->id))}}" x-show="tab == 'addons'" class="btn btn-primary btn-sm">Add Addons</a>
            </div>
            <form x-show="tab == 'tour'" id="edit-form" method="POST" action="{{route('system.reservation.edit.tour.update', encrypt($r_list->id))}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <section class="w-full">
                    <div class="overflow-x-auto">
                        <table x-data="{tourMenu: []}" class="table">
                            <!-- head -->
                            <thead>
                                <th class="flex justify-start items-center">
                                    <button class="btn btn-error btn-xs" type="button" x-show="!(tourMenu.length === 0)" x-transition>Remove</button>
                                </th>
                                <th>Tour</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                            </thead>
                            <tbody>
                                @forelse ($tours ?? [] as $key => $item)
                                    <tr>
                                        <th>
                                            <label >
                                                <input x-model="tourMenu" type="checkbox" name="tour_menu[]" class="checkbox checkbox-error" value="{{encrypt($item['id'])}}" />
                                            </label>
                                        </th>
                                        <td>{{$item['title']}}</td>
                                        <td>{{$item['tpx']}} guest</td>
                                        <td>₱ {{number_format($item['price'], 2)}}</td>
                                        <td>₱ {{number_format($item['amount'], 2)}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold">No Tour Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </form>
            <form x-show="tab == 'addons'" id="edit-form" method="POST" action="{{route('system.reservation.edit.tour.update', encrypt($r_list->id))}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <section class="w-full">
                    <div class="overflow-x-auto">
                        <table x-data="{tourMenu: []}" class="table">
                            <!-- head -->
                            <thead>
                                <th class="flex justify-start items-center">
                                    <button class="btn btn-error btn-xs" type="button" x-show="!(tourMenu.length === 0)" x-transition>Remove</button>
                                </th>
                                <th>Addons</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Amount</th>
                            </thead>
                            <tbody>
                                @forelse ($other_addons ?? [] as $key => $item)
                                    <tr>
                                        <th>
                                            <label >
                                                <input x-model="tourMenu" type="checkbox" name="tour_menu[]" class="checkbox checkbox-error" value="{{encrypt($item['id'])}}" />
                                            </label>
                                        </th>
                                        <td>{{$item['title']}}</td>
                                        <td>{{$item['pcs']}}</td>
                                        <td>₱ {{number_format($item['price'], 2)}}</td>
                                        <td>₱ {{number_format($item['amount'], 2)}}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold">No Tour Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </form>
        </section>
    </x-system-content>
</x-system-layout>