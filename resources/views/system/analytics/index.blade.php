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
                    <a href="{{route('system.analytics.home', ['type=sales', 'tab=weekly'])}}" class="{{(request()->has('type') && request('type') === 'sales') && request()->has('tab') && request('tab') === 'weekly' ? 'tab tab-active' : 'tab '}}">Weekly</a> 
                    <a href="{{route('system.analytics.home', ['type=sales', 'tab=monthly'])}}" class="{{(request()->has('type') && request('type') === 'sales') && request()->has('tab') && request('tab') === 'monthly' ? 'tab tab-active' : 'tab '}}">Monthly</a>
                    <a href="{{route('system.analytics.home', ['type=sales', 'tab=yearly'])}}" class="{{(request()->has('type') && request('type') === 'sales') && request()->has('tab') && request('tab') === 'yearly' ? 'tab tab-active' : 'tab '}}">Yearly</a>
                </div>
            </div>
            @if((request()->has('type') && request('type') === 'sales') && request()->has('tab') && request('tab') === 'weekly')
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
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        min: 1000,     // I-set ang minimum value sa 0
                                        ticks: {
                                            stepSize: 2,  // Ang step size para sa mga ticks
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            @elseif((request()->has('type') && request('type') === 'sales') && request()->has('tab') && request('tab') === 'monthly' )
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
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        min: 1000,     // I-set ang minimum value sa 0
                                        ticks: {
                                            stepSize: 2,  // Ang step size para sa mga ticks
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            @elseif((request()->has('type') && request('type') === 'sales') && request()->has('tab') && request('tab') === 'yearly')
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
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        min: 1000,     // I-set ang minimum value sa 0
                                        ticks: {
                                            stepSize: 2,  // Ang step size para sa mga ticks
                                        }
                                    }
                                }
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
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        min: 1000,     // I-set ang minimum value sa 0
                                        ticks: {
                                            stepSize: 1000,  // Ang step size para sa mga ticks
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            @endif
            <div class="divider"></div>
            <h1 class="text-xl md:text-2xl font-semibold mb-5">Total of Customer: Nationality</h1>
            <div class="flex flex-col items-center justify-center">
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
            <div class="flex justify-between my-5 w-full">
                <h1 class="text-xl md:text-2xl font-semibold mb-5">Total of Customer: Today</h1>
                <div class="tabs tabs-boxed bg-transparent">
                    <a href="{{route('system.analytics.home')}}" class="{{ request()->has('type') && request('type') === 'customer' && request()->has('tab') ? 'tab' : 'tab tab-active'}}">Daily</a> 
                    <a href="{{route('system.analytics.home', ['type=customer', 'tab=weekly'])}}" class="{{ (request()->has('type') && request('type') === 'customer') && (request()->has('tab') && request('tab') === 'weekly') ? 'tab tab-active' : 'tab'}}">Weekly</a> 
                    <a href="{{route('system.analytics.home', ['type=customer', 'tab=monthly'])}}" class="{{ (request()->has('type') && request('type') === 'customer') && (request()->has('tab') && request('tab')) === 'monthly' ? 'tab tab-active' : 'tab'}}">Monthly</a>
                    <a href="{{route('system.analytics.home', ['type=customer', 'tab=yearly'])}}" class="{{ (request()->has('type') && request('type') === 'customer') && (request()->has('tab') && request('tab'))=== 'yearly' ? 'tab tab-active' : 'tab'}}">Yearly</a>
                </div>
            </div>
            <div class="h-96">
                <canvas id="dailyChartCount"></canvas>
                <script>
                    var ctx = document.getElementById('dailyChartCount').getContext('2d');
                    var dailyData = {!! json_encode($customerCount) !!};
                    
                    var labels = dailyData.map(item => item.formatted_date);
                    var count = dailyData.map(item => item.customer_count);
            
                    var chart = new Chart(ctx, {
                        type: 'bar', // Pumili ng tamang uri ng tsart (line, bar, etc.)
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Customer',
                                data: count,
                                backgroundColor: '#409122'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    min: 0,     // I-set ang minimum value sa 0
                                    ticks: {
                                        stepSize: 1,  // Ang step size para sa mga ticks
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>
        </section>
    </x-system-content>
    @push('scripts')
        {{-- <script type="module" src='{{Vite::asset("resources/js/analytics-chart.js")}}'></script> --}}
    @endpush
</x-system-layout>
