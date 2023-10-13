@props(['id', 'name', 'title' => null, 'fileValue' => null, 'noRequired' => false])
<div>
<div class="flex flex-col md:flex-row w-full my-3 justify-between items-center">
  <h3 class="text-md">{{$title}}</h3> @if(!$noRequired) <span class="text-error">*</span> @endif
</div>
  @error($name)
  <p class="mb-5 label-text-alt text-error">{{$message}}</p>
  @enderror
  <label for="{{$id}}" class="cursor-pointer mt-5" x-data="imageUploader{{$id}}()">
      <div
        class="border-2 border-dashed border-primary p-4 w-full mx-auto"
        x-on:dragover="dragOver{{$id}}($event)"
        x-on:dragleave="dragLeave{{$id}}()"
        x-on:drop="drop{{$id}}($event)"
        x-bind:class="{ 'bg-gray-100': isDragging }"
      >
        <div class="text-center">
          <div :class="imageData{{$id}} ? 'hidden' : '' ">
            <p class="text-lg font-semibold">Drag & Drop an Image Here</p>
            <p class="text-gray-500">Or click to select a file</p>
            <input type="file" name="{{$name}}" id="{{$id}}" class="hidden" accept="image/*" x-ref="fileInput{{$id}}" x-on:change="fileSelected{{$id}}($refs.fileInput{{$id}})">
          </div>
        </div>
        <div x-show="imageData{{$id}}" class="w-full flex flex-col items-center">
          <img id="previewImage{{$id}}" :src="imageData{{$id}}" alt="{{$title ?? ''}} Image" class="mx-auto w-72">
          <button type="button" class="mt-3 btn btn-circle btn-ghost btn-sm" x-on:click="clearImage{{$id}}">
            <i class="fa-solid fa-x"></i>
          </button>
        </div>
      </div>
    
  </label>
</div>
<script>
      function imageUploader{{$id}}() {
      return {
        isDragging: false,
        imageData{{$id}}: '{{$fileValue ?? ''}}',
        
        dragOver{{$id}}(event) {
          event.preventDefault();
          this.isDragging = true;
        },
        
        dragLeave{{$id}}() {
          this.isDragging = false;
        },
        
        drop{{$id}}(event) {
          event.preventDefault();
          this.isDragging = false;
          const file = event.dataTransfer.files[0];
          this.displayImage{{$id}}(file);
        },
        
        fileSelected{{$id}}(input) {
          const file = input.files[0];
          this.displayImage{{$id}}(file);
        },
        
        displayImage{{$id}}(file) {
          const reader = new FileReader();
          reader.onload = () => {
            this.imageData{{$id}} = reader.result;
          };
          reader.readAsDataURL(file);
        },
        
        clearImage{{$id}}() {
          this.imageData{{$id}} = '';
        },
      };
    }
</script>