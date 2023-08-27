@php
    $arrTl = [
      'title' => '',
      'category' => '',
      'inclusion' => '',
      'tour_type' => '',
      'no_day' => '',
      'hrs' => '',
    ];
    if(request()->has(['tl', 'rpl'])){
      $arrTl = [
        'id' => $tl['id'],
        'title' => $tl['title'],
        'category' => $tl['category'],
        'inclusion' => $tl['inclusion'],
        'tour_type' => $tl['tour_type'],
        'no_day' => $tl['no_day'],
        'hrs' => $tl['hrs'],
      ];
    }
    $arrTrType = ['All', 'Day Tour', 'Overnight'];

@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Tour Menu" back=true>
    <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
      <div class="w-full md:w-96">
        <div class="{{empty($service_menus) ? 'hidden': 'flex'}}">
          <form class="{{empty($service_menus) ? 'hidden': ''}}" id="replace-form" action="{{route('system.menu.replace')}}" method="POST">
            @csrf
            <div class="flex justify-between w-full h-full">
              @if(request()->has(['tl', 'rpl']))
                <x-select id="replace" name="replace" placeholder="What list do you want to replace" :value="$service_menus->pluck('id')" :title="$service_menus->pluck('title')" selected="{{$arrTl['title'] ?? ''}}" disabled=true/>
                <a href="{{route('system.menu.create')}}" class="btn btn-ghost">X</a>
              @else
                <x-select id="replace" name="replace" placeholder="What list do you want to replace" :value="$service_menus->pluck('id')" :title="$service_menus->pluck('title')" selected="{{$arrTl['title'] ?? ''}}" />
                <button onclick="event.preventDefault(); document.getElementById('replace-form').submit();" class="btn btn-primary">Replace</button>
              @endif
            </div>
          </form>
        </div>
        @if (request()->has(['tl', 'rpl']))
            <form id="add-form" action=" {{ route('system.menu.store', Arr::query(['tlid' => encrypt($arrTl['id'] ?? '') ] )) }}" method="post">
        @else
            <form id="add-form" action=" {{ route('system.menu.store') }}" method="post">
        @endif
          @csrf
          @if(request()->has('rpl'))
              <x-input type="text" id="title" placeholder="Title" value="{{$arrTl['title'] ?? ''}}" disabled=true/>
              <x-datalist-input id="category"  placeholder="Category" :lists="$category" value="{{$arrTl['category'] ?? ''}}" disabled=true/>
              <x-select id="tour_type" name="tour_type"  placeholder="Tour Type" :value="$arrTrType" :title="$arrTrType" selected="{{$arrTl['tour_type'] ?? ''}}" disabled=true/>
              <x-input type="number" id="no_day" placeholder="Number of days" min="0.0" value="{{$arrTl['no_day'] ?? ''}}" disabled=true/>
              <x-textarea id="inclusion"  placeholder="Inclusion (Item 1, Item 2)" value="{{$arrTl['inclusion'] ?? ''}}" disabled=true/>
              <input type="hidden" name="menu_id" value="{{$arrTl['id']}}">
              <div x-init="$el.scrollIntoView();" class="border border-primary mb-8 rounded-md shadow-md p-8">
          @else
              <x-input type="text" id="title" name="title" placeholder="Title"  />
              <x-datalist-input id="category" name="category"  placeholder="Category" :lists="$category" />
              <x-select id="tour_type" name="tour_type" placeholder="Tour Type" :value="$arrTrType" :title="$arrTrType" selected="{{$arrTl['tour_type'] ?? old('tour_type')}}" />
              <x-input type="number" id="no_day" name="no_day" placeholder="Number of days" min="0.0" />
              <x-textarea id="inclusion" name="inclusion" placeholder="Inclusion (Item 1, Item 2)" />
              <div class="border border-primary mb-8 rounded-md shadow-md p-8">
          @endif
            <h3 class="text-xl font-medium mb-4">Price Details</h3>
            <x-input type="text" id="type" name="type" placeholder="What title of this price?" min="1" />
            <x-input type="number" id="pax" name="pax" placeholder="Number of Guest" min="1" />
            <x-input type="number" id="price" name="price" placeholder="Price" min="1" />
          </div>
          <label for="add_modal" class="btn btn-primary w-full">Add Menu</label>
          <x-passcode-modal title="Add Menu Confirmation" id="add_modal" formId="add-form" />        
        </form>
      </div>
    </div>
  </x-system-content>
</x-system-layout>
