@props(['id' => 'camera', 'name' => '', 'xRef' => ''])
<x-modal title="Camera" id="{{$id}}" >
    <div x-data="webcamApp()" class="min-h-screen flex flex-col items-center justify-center">
        <video x-ref="video" autoplay playsinline></video>
        <canvas x-ref="canvas" style="display: none;"></canvas>
        <button @click="captureImage" class="btn btn-primary">Capture Webcam</button>
        <div class="divider">Or</div>
        <input type="file" @change="handleFileInput" name="{{$name}}" x-ref="xRef" style="display: none;">
        {{-- <img x-bind:src="image" alt="Captured Image" x-show="image"> --}}
    </div>

</x-modal>
@push('scripts')
    <script src="{{Vite::asset('resources/js/camera.js')}}"></script>
@endpush