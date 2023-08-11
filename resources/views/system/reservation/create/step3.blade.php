@php
    $addons = isset(session('rinfo')['qty']) ? decrypt(session('rinfo')['qty']) : old('qty');
@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book" back=true>
    <section x-data="{loader: false}" class="my-10 p-5">
      <x-loader />
      <div>
        <h1 class="sr-only">Checkout</h1>
        <form id="reservation-form" action="{{ route('system.reservation.store.step.three')}}" method="POST">
          @csrf
          <h1 class="text-xl font-bold">Addons <sup class="text-error text-sm"> *Optional</sup></h1>
          <div class="overflow-x-auto">
              <table class="table">
              <!-- head -->
              <thead>
                  <tr>
                  <th></th>
                  <th>Addons</th>
                  <th>Price</th>
                  <th>Quantity</th>
                  </tr>
              </thead>
              <tbody x-data="{addons: {{$addons ? '[' . implode(',', array_keys($addons)) .']' : '[]'}}}">
                  @forelse ($add_ons as $key => $item)
                      <tr>
                          <th>
                          <label>
                              <input x-effect="addons = addons.map(function (x) { return parseInt(x, 10); });" x-model="addons" type="checkbox" class="checkbox checkbox-primary" value="{{$item->id}}" checked/>
                          </label>
                          </th>
                          <td>{{$item->title}}</td>
                          <td>{{$item->price}}</td>
                          <td>
                            <div x-data="{count: {{isset($addons[$item->id]) ? (int)$addons[$item->id] : 1}}}" class="join">
                              <button @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-primary btn-xs join-item rounded-l-full" :disabled="!addons.includes({{$item->id}})">-</button>
                              <input x-model="count" type="number" :name="addons.includes({{$item->id}}) ? 'qty[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-primary join-item" min="1" readonly :disabled="!addons.includes({{$item->id}})"/>
                              <button @click="count++" type="button" class="btn btn-primary btn-xs last:-item rounded-r-full" :disabled="!addons.includes({{$item->id}})">+</button>
                          </div>
                          </td>
                      </tr>
                  @empty
                      <tr colspan="4">
                          <td class="text-center font-bold">No Tour Found</td>
                      </tr>
                  @endforelse
              </tbody>

              </table>
          </div>
          <div class="flex justify-end">
            <button class="btn btn-primary" @click="loader = true">Next</button>
        </div>
      </form>
      </div>
    </section>
  </x-system-content>
</x-system-layout>
