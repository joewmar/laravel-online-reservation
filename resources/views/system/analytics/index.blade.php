<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Analytics">
        <div class="mt-8 w-full">
            <canvas id="chartAnalytics" class="text-black w-full h-screen"></canvas>
    
        </div> 
    </x-system-content>
    <script type="module" src='{{Vite::asset("resources/js/analytics-chart.js")}}'></script>
</x-system-layout>