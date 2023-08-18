<x-system-layout :activeSb="$activeSb">
    @push('styles')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
    <x-system-content title="Analytics">
        <section>
            <div class="flex justify-between my-5 w-full">
                <h1 class="font-semibold text-xl md:text-2xl hidden md:block">Sales Report</h1>
                <div class="tabs tabs-boxed bg-transparent">
                    <a href="{{route('system.analytics.home')}}" class="{{request()->has('tab') ? 'tab' : 'tab tab-active'}}">Daily</a> 
                    <a href="{{route('system.analytics.home', 'tab=weekly')}}" class="{{request()->has('tab') && request('tab') === 'weekly' ? 'tab tab-active' : 'tab '}}">Weekly</a> 
                    <a href="{{route('system.analytics.home', 'tab=monthly')}}" class="{{request()->has('tab') && request('tab') === 'monthly' ? 'tab tab-active' : 'tab '}}">Monthly</a>
                    <a href="{{route('system.analytics.home', 'tab=yearly')}}" class="{{request()->has('tab') && request('tab') === 'yearly' ? 'tab tab-active' : 'tab '}}">Yearly</a>
                </div>
            </div>
            @if(request()->has('tab') && request('tab') === 'weekly')
                <div class="h-96">
                    <canvas id="weeklyChart"></canvas>
                    <script>
                        var ctx = document.getElementById('weeklyChart').getContext('2d');
                        var weeklyData = {!! json_encode($sales) !!};
                
                        var labels = weeklyData.map(item => item.formatted_date_range);
                        var amounts = weeklyData.map(item => item.total_amount);
                
                        var chart = new Chart(ctx, {
                            type: this.category, // Pumili ng tamang uri ng tsart (line, bar, etc.)
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Amount',
                                    data: amounts,
                                    backgroundColor: '#409122'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    </script>
                </div>
            @elseif(request()->has('tab') && request('tab') === 'monthly' )
                <div class="h-96">
                    <canvas id="monthlyChart"></canvas>
                    <script>
                        var monthAbbreviations = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                        var ctx = document.getElementById('monthlyChart').getContext('2d');
                        var monthlyData = {!! json_encode($sales) !!};
                

                        var labels = monthlyData.map(item => {
                            var monthIndex = item.month - 1; 
                            var monthAbbreviation = monthAbbreviations[monthIndex];
                            return monthAbbreviation + ' ' + item.year;
                        });                        
                        var amounts = monthlyData.map(item => item.total_amount);
                
                        var chart = new Chart(ctx, {
                            type: 'bar', 
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Amount',
                                    data: amounts,
                                    backgroundColor: '#409122'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    </script>
                </div>
            @elseif(request()->has('tab') && request('tab') === 'yearly')
                <div class="h-96">
                    <canvas id="yearlyChart"></canvas>
                    <script>
                        var ctx = document.getElementById('yearlyChart').getContext('2d');
                        var yearlyData = {!! json_encode($sales) !!};
                

                        var labels = yearlyData.map(item => 'Year of ' + item.year);
                        var amounts = yearlyData.map(item => item.total_amount);
                
                        var chart = new Chart(ctx, {
                            type: 'bar', // Pumili ng tamang uri ng tsart (line, bar, etc.)
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Amount',
                                    data: amounts,
                                    backgroundColor: '#409122'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    </script>
                </div>
            @else
                <div class="h-96">
                    <canvas id="dailyChart"></canvas>
                    <script>
                        var ctx = document.getElementById('dailyChart').getContext('2d');
                        var dailyData = {!! json_encode($sales) !!};
                
                        var labels = dailyData.map(item => item.formatted_date);
                        var amounts = dailyData.map(item => item.total_amount);
                
                        var chart = new Chart(ctx, {
                            type: 'bar', // Pumili ng tamang uri ng tsart (line, bar, etc.)
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Total Amount',
                                    data: amounts,
                                    backgroundColor: '#409122'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    </script>
                </div>
            @endif
            <div class="divider"></div>
            <div class="flex flex-col items-center justify-center">
                <h1 class="text-xl md:text-2xl font-semibold mb-5">Nationality</h1>
                <div class="w-full">
                    <canvas id="nationalityChart" width="400" height="400"></canvas>
                </div>
                <script>
                    var ctx = document.getElementById('nationalityChart').getContext('2d');
                    var nationalitiesData = {!! json_encode($nationalities) !!};
            
                    var labels = nationalitiesData.map(item => item.nationality);
                    var counts = nationalitiesData.map(item => item.count);
            
                    var chart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Customer',
                                data: counts,
                                backgroundColor: ["#409122","#e9e92f","#191a3e","#ffffff","#cae2e8","#dff2a1","#f7e488","#fb7185",],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        var dataset = data.datasets[tooltipItem.datasetIndex];
                                        var value = dataset.data[tooltipItem.index];
                                        var label = data.labels[tooltipItem.index];
                                        return label + ': ' + value;
                                    }
                                }
                            },
                        },
                    });
                </script>
            </div>
        </section>
    </x-system-content>
    @push('scripts')
        {{-- <script type="module" src='{{Vite::asset("resources/js/analytics-chart.js")}}'></script> --}}
    @endpush
</x-system-layout>
