@props(['tourCategory', 'tourLists', 'tpx', 'atpermit' => 0])
<div class="w-full text-center">
    <span x-show="!document.querySelector('[x-cloak]')" class="loading loading-spinner loading-lg text-primary"></span>
</div>
<div x-data="{category: null}" x-cloak>
    @foreach ($tourCategory as $category)
      @if($loop->index === 0)
        <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}" />
        <label x-init="category = '{{Str::camel($category->category)}}'" :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
      @else
          <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}"/>
          <label :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
      @endif

    @endforeach

    @foreach ($tourCategory as $category)
        @php $category_count = $loop->index + 1; @endphp
        <div class="w-full my-5" x-cloak>
            <div x-data="{ price: '' }" :class="category == '{{Str::camel($category->category)}}' ? 'w-full grid grid-cols-1 md:grid-cols-2 gap-5 place-content-center' : 'hidden'">
              {{-- Card List Tour --}}
              @foreach ($tourLists as $list)
                @php $list_count = $loop->index + 1 ?? 1; @endphp

                @if ($category->category === $list->category)
                    <div class="{{$list->atpermit >= $atpermit ? '' : 'cursor-not-allowed'}}">
          
                      <div class="card h-full bg-base-100 shadow-xl border border-primary {{$list->atpermit >= $atpermit ? 'hover:border-primary hover:bg-primary hover:text-base-100' : ''}} ">
                        <label for="{{!($list->atpermit >= $atpermit) ? '' : Str::camel($list->title)}}" tabindex="0" class="{{!($list->atpermit >= $atpermit) ? 'opacity-60' : ''}}">
                            <div class="card-body">
                              <h2 x-ref="titleRef{{$list_count}}" class="card-title {{$list->atpermit >= $atpermit ? '' : 'cursor-not-allowed'}}">{{$list->title}} </h2> 
                              @if(!($list->atpermit >= $atpermit)) <p class="text-error text-sm {{$list->atpermit >= $atpermit ? '' : 'cursor-not-allowed'}}">Invalid Tour due does not allow in Day Tour</p> @endif
                            </div>
                        </label>
                        {{-- Modal Tour Details --}}                                  
                        <x-modal id="{{Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price" noBottom>
                          <article>
                            <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral">
                              <li><strong>Number of days: </strong> {{$list->no_day <= 1 ? $list->no_day . ' day' : $list->no_day . ' days' }}</li>
                              <li><strong>Price Plan</strong></li>
                            </ul>
                          </article>
                          <div class="grid gap-4 grid-cols-2 my-4">
                              @foreach ($list->tourMenuLists as $menu)
                                  @php 
                                    $menu_count = $loop->index + 1; 
                                  @endphp
                                    @if(count($list->tourMenuLists) != 1)
                                      @if($tpx > $menu->pax && $menu_count === count($list->tourMenuLists))
                                      <div class="w-full h-full">
                                        <input id="{{$tpx > $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price"/>
                                        <label for="{{$tpx > $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                      @else
                                      <div id="{{$tpx != $menu->pax ? 'disabledAll' : ''}}" class="w-full h-full">
                                        <input id="{{$tpx == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                        <label for="{{$tpx == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                      @endif
                                    @else
                                      <div class="w-full h-full">
                                        <input id="{{$tpx >= $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                        <label for="{{$tpx >= $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                    @endif
                                          <div class="flex items-center justify-between">
                                            <p class="text-neutral" x-ref="refType{{$menu->id}}">{{$menu->type}} ({{$menu->pax}} guest)</p>
                                            <i class="hidden text-primary fa-solid fa-square-check"></i>
                                          </div>
                                          <p class="mt-1 text-neutral" x-ref="priceRef{{$menu->id}}">P {{number_format($menu->price, 2)}}</p>
                                          @if(count($list->tourMenuLists) !== 1)
                                            @if($tpx > $menu->pax && $menu_count !== count($list->tourMenuLists))
                                                <p class="absolute text-error text-[7px] md:text-xs">Invalid guest count for this price.</p>
                                            @endif
                                          @endif
                                          @if(count($list->tourMenuLists) !== 1)
                                            @if($tpx < $menu->pax)
                                                <p class="absolute text-error text-[7px] md:text-xs">Invalid guest count for this price.</p>
                                            @endif
                                          @endif
                                        </label>
                                      </div>
                                @endforeach
                          </div>
                          @foreach ($list->tourMenuLists as $menu)

                              <template x-if="price == {{$menu->id}}">
                                <label for="{{Str::camel($list->title)}}" @click=" addToCart(price, $refs.titleRef{{$list_count}}.innerText, $refs.refType{{$menu->id}}.innerText, $refs.priceRef{{$menu->id}}.innerText)" class="btn btn-primary float-right">
                                  Add to Cart
                                </label>
                              </template>
                          @endforeach
                        </x-modal>
                      </div>
                    </div>
                @endif
              @endforeach
          </div>
        </div>
    @endforeach
</div>
  