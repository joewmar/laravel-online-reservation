@section('content')
    <h1>Sales Report</h1>
    <canvas id="salesChart"></canvas>
    <script>
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesData = {!! json_encode($sales) !!};

        var labels = salesData.map(item => item.date);
        var amounts = salesData.map(item => item.amount);

        var chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales Amount',
                    data: amounts,
                    borderColor: 'blue',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
@endsection
