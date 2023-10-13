@push('scripts')
    <template x-if="loader">
        <div class="flex justify-center item-center fixed top-0 left-0 w-full h-screen z-[100] bg-opacity-50 bg-base-200">
            <span class="loading loading-bars loading-lg  text-primary z-[100] opacity-100"></span>
        </div>
    </template>
@endpush