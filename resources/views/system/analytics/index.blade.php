<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Analytics">
        <div x-data="{category: ''}">
            <div class="tabs tabs-boxed bg-transparent">
                <a class="tab ">Bar</a> 
                <a class="tab tab-active">Line</a> 
                {{-- <a class="tab">Tab 3</a> --}}
              </div>
            <div class="mt-8 w-full">
                <canvas id="chartAnalytics" class="text-black w-full h-screen"></canvas>
            </div> 
        </div>
    </x-system-content>
    @push('scripts')
        <script type="module" src='{{Vite::asset("resources/js/analytics-chart.js")}}'></script>
    @endpush
</x-system-layout>
