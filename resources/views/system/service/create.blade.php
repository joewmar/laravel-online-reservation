<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Tour Menu">
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="md:w-96">
        <div class="flex">
          <form id="replace-form" action="{{route('system.menu.replace')}}" method="POST">
            @csrf
            <div class="flex justify-between w-full h-full">
              <x-select id="replace" name="replace" placeholder="What list do you want to replace" :value="$service_menus->pluck('id')" :title="$service_menus->pluck('title')" />
              <button onclick="event.preventDefault(); document.getElementById('replace-form').submit();" class="btn btn-primary">Replace Now</button>
            </div>
          </form>
        </div>
        <form id="add-form" action=" {{ route('system.menu.store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
          @csrf
          <x-input type="text" id="title" name="title" placeholder="Title"/>
          <x-datalist-input id="category" name="category" placeholder="Category"/>
          <x-textarea id="inclusion" name="inclusion" placeholder="Inclusion (Item 1, Item 2)"/>
          <x-input type="number" id="no_day" name="no_day" placeholder="Number of days" min="0.0"/>
          <x-input type="number" id="hrs" name="hrs" placeholder="Number of hours" min="1" />
          <div x-data="{pricePlans: []}" class="mb-8">
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold">Price Plan</div>
                <button type="button" class="btn btn-ghost btn-circle text-2xl" @click="pricePlans.push({ id: new Date().getTime()})">+</button>
            </div>
            <template x-for="(item, index) in pricePlans" :key="index" x-id="['type', 'price' ,'pax']">
            <div class="border border-primary rounded-md p-4 shadow-md mb-8">
              <div class="flex justify-between items-center">
                  <div class="text-sm" x-text=" 'Price Plan ' + (index + 1)"></div>
                  <button type="button" class="btn btn-ghost btn-circle" @click="pricePlans.splice(index, 1)" >X</button>
              </div>
              <div class="overflow-x-auto">
                <table class="table table-xs">
                  <thead>
                    <tr>
                      <th>Price Title</th>
                      <th>Pax</th>
                      <th>Price</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <input type="text" :name="$id('type')" :id="$id('type')" class="input input-bordered input-primary input-sm w-full" />
                      </td>
                      <td>
                        <input type="number" :name="$id('pax')" :id="$id('pax')" class="input input-bordered input-primary input-sm w-full" min="1.00" />
                        
                      </td>
                      <td>
                        <input type="number" :name="$id('price')" :id="$id('price')" class="input input-bordered input-primary input-sm w-full" min="" />
                        
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            </template>
          </div>
          <label for="add_modal" class="btn btn-primary w-full">Add Menu</label>
          <x-passcode-modal title="Add Menu Confirmation" id="add_modal" formId="add-form" />        
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
