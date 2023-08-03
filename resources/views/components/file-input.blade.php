@props(['id' => '', 'name' => '', 'value' => '', 'placeholder', 'imgID' => 'show_img', 'camera' => ''])
<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{$placeholder}}</span>
    </label>
    @if ($camera == 'image')
        <input type="file" id="{{$id}}" name="{{$name}}" class="file-input file-input-bordered file-input-primary file-input-sm w-full object-fill" value="{{$value ?? old($name)}}" capture="user" accept="image/*" />
    @else
        <input type="file" id="{{$id}}" name="{{$name}}" class="file-input file-input-bordered file-input-primary file-input-sm w-full object-fill" value="{{$value ?? old($name)}}" />
    @endif
    <label class="label">
        <span class="label-text-alt">
        @error($name)
            <span class="label-text-alt text-error">{{$message}}</span>
        @enderror
        </span>
    </label>
</div>
@push('scripts')
    <script>
        let img = document.getElementById('{{$imgID}}');
        let input = document.getElementById('{{$id}}');
        input.addEventListener("change", () => {
            img.src = URL.createObjectURL(input.files[0]);
        });
    </script>
@endpush
