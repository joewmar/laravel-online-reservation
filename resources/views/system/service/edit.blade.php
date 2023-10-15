@php
    $arrTrType = ['All', 'Day Tour'];
@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit {{$service_menu->title}}" back=true>
    <form x-data="listEditor()" id="update-form" action=" {{ route('system.menu.update', encrypt($service_menu->id)) }}" method="post" autocomplete="off">
      @csrf
      @method('PUT')
      <div x-init="init" class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="w-full md:w-96">
          <x-input type="text" id="title" name="title" placeholder="Title" value="{{$service_menu->title}}" noRequired/>
            <x-datalist-input id="category" name="category"  placeholder="Category" :lists="$category" value="{{$service_menu->category}}" noRequired />
            <x-select name="atpermit" id="atpermit" placeholder="Accommodation Type to Allow" :value="array_keys($arrTrType)" :title="$arrTrType" selected="{{$arrTrType[$service_menu->atpermit]}}" noRequired />

            <div class="form-control w-full pb-5">
              <textarea id="listTextarea" class="w-full rounded-lg border-gray-300 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary p-3 text-sm" rows="2" x-model="inputText" x-on:keydown.enter.prevent="addListItem" placeholder="Inclusion (Use Enter Key to Add Item)"></textarea>
              <div id="listOutput" class="w-full mt-2 py-2 border-t" :class="listItems.length == 0 ? 'hidden' : 'block' ">
                  <template x-for="(item, index) in listItems" :key="index">
                      <div class="flex items-center justify-between">
                          <div x-text="item"></div>
                          <input type="hidden" name="inclusion[]" :value="item">
                          <button type="button" @click="removeListItem(index)" class="text-error cursor-pointer">
                            <i class="fa-solid fa-trash"></i>
                          </button>
                      </div>
                      <div class="divider"></div>
                  </template>
              </div>
          </div>
          <label for="add_modal" class="btn btn-primary w-full">Save</label>
          <x-passcode-modal title="Edit Menu Confirmation" id="add_modal" formId="update-form" />        
        </div>
      </div>
  </form>
  </x-system-content>
  @push('scripts')
    <script>
        function listEditor(){
          return {
            inputText: '',
            listItems: [],

            init() {
                this.inputText = '';
                @if(isset($service_menu->inclusion))
                    this.listItems = [
                        @foreach(explode("(..)", $service_menu->inclusion) as $item)
                          '{{$item}}',
                        @endforeach
                    ];
                @else
                  this.listItems = [];
                @endif
            },

            addListItem() {
                if (this.inputText.trim() !== '') {
                    this.listItems.push(this.inputText);
                    this.inputText = '';
                }
            },

            removeListItem(index) {
                this.listItems.splice(index, 1);
            }
          }
        }
    </script>
  @endpush
</x-system-layout>
