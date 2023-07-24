<!DOCTYPE html>
<html>
<head>
    <title>Digital Receipt</title>
    <style>
        /* Add some CSS styling for the receipt */
        body {
            font-family: Arial, sans-serif;
        }
        .receipt {
            width: 300px;
            margin: 20px auto;
            border: 2px solid #000;
            padding: 10px;
        }
        .item {
            margin-bottom: 5px;
        }
        .total {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>Receipt</h2>
        <p>Date: {{ $date }}</p>
        <p>Receipt Number: {{ $receiptNumber }}</p>
        <hr>
        <div class="items">
            @foreach ($items as $item)
                <div class="item">
                    <span>{{ $item['name'] }}</span>
                    <span>{{ $item['price'] }}</span>
                </div>
            @endforeach
        </div>
        <hr>
        <div class="total">
            Total: {{ $totalAmount }}
        </div>
    </div>
</body>
</html>
