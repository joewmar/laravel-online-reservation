<x-system-layout :activeSb="$activeSb">
    <x-system-content title="{{preg_replace('/(\D)(\d)/', '$1 $2', Str::title(str_replace('_', ' ', $key)))}}" back="{{route('system.webcontent.home', '#hero')}}">
        <section class="p-6 flex justify-center">
            <div class="w-96">
                <form action="{{route('system.webcontent.image.hero.update', encrypt($key))}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <x-drag-drop name="hero_one" id="hero_one" fileValue="{{asset('storage/'. $hero[$key])}}" noRequired />
                    <div class="flex justify-end space-x-3 mt-5">
                        <button class="btn btn-secondary">Change</button>
                        <label for="remove_modal" class="btn btn-error">Remove</label>
                    </div>
                </form>

            </div>
        </section>
        <form id="updmdlfrm" action="{{route('system.webcontent.image.hero.destroy.one', encrypt($key))}}" method="post">
            @csrf
            @method('DELETE')
            <x-modal id="remove_modal" title="Do you want remove this photo?" type="YesNo" formID="updmdlfrm" loader noBottom>
            </x-modal>
        </form>

    </x-system-content>
</x-system-layout>