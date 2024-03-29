@php
    $tours = [];
    foreach ($menu as $key => $value) {
        $tours[$key] = $value;
    }
    foreach ($tour_addons as $key => $value) {
        $tours[$key] = $value;
    }

@endphp
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Digital Receipt</title>
		<link rel="stylesheet" href="style.css">
		<link rel="license" href="https://www.opensource.org/licenses/mit-license/">
		<script src="script.js"></script>
		<style>
			@font-face {
				font-family: DejaVu Sans;
			}
		/* reset */

*
{
	border: 0;
	box-sizing: content-box;
	color: inherit;
	font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
	font-size: inherit;
	font-style: inherit;
	font-weight: inherit;
	line-height: inherit;
	list-style: none;
	margin: 0;
	padding: 0;
	text-decoration: none;
	vertical-align: top;
}

/* content editable */

*[contenteditable] { border-radius: 0.25em; min-width: 1em; outline: 0; }

*[contenteditable] { cursor: pointer; }

*[contenteditable]:hover, *[contenteditable]:focus, td:hover *[contenteditable], td:focus *[contenteditable], img.hover { background: #DEF; box-shadow: 0 0 1em 0.5em #DEF; }

span[contenteditable] { display: inline-block; }

/* heading */

h1 { font: bold 100% sans-serif; letter-spacing: 0.5em; text-align: center; text-transform: uppercase; }

/* table */

table { font-size: 75%; table-layout: fixed; width: 100%; }
table { border-collapse: separate; border-spacing: 2px; }
th, td { border-width: 1px; padding: 0.5em; position: relative; text-align: left; }
th, td { border-radius: 0.25em; border-style: solid; }
th { background: #EEE; border-color: #BBB; }
td { border-color: #DDD; }

/* page */

html { font: 16px/1 'Open Sans', sans-serif; overflow: auto; padding: 0.5in; }
html { background: #999; cursor: default; }

body { box-sizing: border-box; margin: 0 auto; overflow: hidden; padding: 0.5in; font-family: DejaVu Sans; }
body { background: #FFF; border-radius: 1px; box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5); }

/* header */

header { margin: 0 0 3em; }
header:after { clear: both; content: ""; display: table; }

header h1 { background: #000; border-radius: 0.25em; color: #FFF; margin: 0 0 1em; padding: 0.5em 0; }
header address { float: left; font-size: 75%; font-style: normal; line-height: 1.25; margin: 0 1em 1em 0; }
header address p { margin: 0 0.25em; }
header span, header img { display: block; float: left; }
header span { margin: 0 0 1em 1em; max-height: 25%; max-width: 60%; position: relative; }
header img { max-height: 100%; max-width: 100%; }
header input { cursor: pointer; -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)"; height: 100%; left: 0; opacity: 0; position: absolute; top: 0; width: 100%; }

/* article */

article, article address, table.meta, table.inventory { margin: 0 0 3em; }
article:after { clear: both; content: ""; display: table; }
article h1 { clip: rect(0 0 0 0); position: absolute; }

article address { font-size: 125%; font-weight: bold; }
article address > span{ font-size: 60%; font-weight: normal; }

/* Details */
div.details {padding-top: 5%; margin-bottom: 3%;}
div.details h5 {font-weight: bold; font-size: 15px; margin-top: 2%;}
div.details h5 > span {font-weight: normal;}

/* table meta & balance */

table.meta, table.balance { float: right; width: 36%; }
table.meta:after, table.balance:after { clear: both; content: ""; display: table; }

/* table meta */

table.meta th { width: 40%; }
table.meta td { width: 60%; }

/* table items */

table.inventory { clear: both; width: 100%; }
table.inventory th { font-weight: bold; }

table.inventory td:nth-child(1) { width: 26%; }
table.inventory td:nth-child(2) { width: 38%; }
table.inventory td:nth-child(3) { text-align: right; width: 12%; }
table.inventory td:nth-child(4) { text-align: right; width: 12%; }
table.inventory td:nth-child(5) { text-align: right; width: 12%; }

/* table balance */

table.balance th, table.balance td { width: 50%; }
table.balance td { text-align: right; }

/* aside */

aside h1 { border: none; border-width: 0 0 1px; margin: 0 0 1em; }
aside h1 { border-color: #999; border-bottom-style: solid; }

/* javascript */

.add, .cut
{
	border-width: 1px;
	display: block;
	font-size: .8rem;
	padding: 0.25em 0.5em;	
	float: left;
	text-align: center;
	width: 0.6em;
}
.pageb {
    page-break-after: always;
}
.add, .cut
{
	background: #9AF;
	box-shadow: 0 1px 2px rgba(0,0,0,0.2);
	background-image: -moz-linear-gradient(#00ADEE 5%, #0078A5 100%);
	background-image: -webkit-linear-gradient(#00ADEE 5%, #0078A5 100%);
	border-radius: 0.5em;
	border-color: #0076A3;
	color: #FFF;
	cursor: pointer;
	font-weight: bold;
	text-shadow: 0 -1px 2px rgba(0,0,0,0.333);
}

.add { margin: -2.5em 0 0; }

.add:hover { background: #00ADEE; }

.cut { opacity: 0; position: absolute; top: 0; left: -1.5em; }
.cut { -webkit-transition: opacity 100ms ease-in; }

tr:hover .cut { opacity: 1; }

@media print {
	* { -webkit-print-color-adjust: exact; }
	html { background: none; padding: 0; }
	body { box-shadow: none; margin: 0; }
	span:empty { display: none; }
	.add, .cut { display: none; }
}

@page { margin: 0; }
		</style>
	
	</head>
	<body style="height: auto;">
		<header>
			<h1>Receipt</h1>
			<img src="{{asset('images/logo.png')}}" alt="Logo" style="width: 15%">
			<address>
				<p style="font-weight: bold;">{{ str_replace('_', ' ', config('app.name'))}}</p>
				<p>{{env('MAIN_ADDRESS')}} </p>
				<p>{{$contacts['contactno'] ?? 'None'}} </p>
				<p>{{$contacts['email'] ?? 'None'}} </p>
			</address>
		</header>
		<article>

			<table class="meta">
				<tr>
					<th><span >Invoice #</span></th>
					<td><span>{{str_replace('aar-','', $r_list->id)}}</span></td>
				</tr>
				<tr>
					<th><span >Date</span></th>
					<td><span >{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', ($r_list->transaction['receipt'] ?? $r_list->updated_at))->format('F j, Y') ?? 'None'}}</span></td>
				</tr>	
			</table>
			<address>
				<p>{{$r_list->userReservation->name()}}<br></p>
				<span>{{$r_list->age}} years old from {{$r_list->userReservation->country}}<br></span>
			</address>
			<div class="details">
				<h5>Guest: <span>{{$r_list->pax . ' guest' ?? 'None'}}</span></h5>
				@if($r_list->accommodation_type != 'Room Only')
					<h5>Guest going on tour: <span>{{$r_list->tour_pax . ' guest' ?? 'None'}}</span></h5>
				@endif
				<h5>Check-in: <span>{{\Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_in)->format('l F j, Y') ?? 'None'}}</span></h5>
				<h5>Check-out: <span>{{\Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_out)->format('l F j, Y') ?? 'None'}}</span></h5>
				<h5>Service Type: <span>{{$r_list->accommodation_type ?? 'None'}}</span></h5>
				<h5>Payment Method: <span>{{$r_list->payment_method ?? 'None'}}</span></h5>
				@if($r_list->countSenior() > 0)
					<h5>Senior / PWD: {{$r_list->countSenior() > 0}}</h5>
				@endif
			</div>
			@if (!empty($rooms))			
				<table class="inventory">
					<thead>
						<tr>
							<th><span >Room No</span></th>
							</tr>
					</thead>
					<tbody>
						@php $amount = 0; @endphp
						@foreach ($rooms as $key => $item)
							<tr>
								<td><span >Room No. {{$item['no']}} {{$item['name']}}</span></td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
			@if(isset($rate))
				<table class="inventory">
					<thead>
						<tr>
							<th><span >Room Type</span></th>
							<th><span >No. of days</span></th>
							<th><span >Amount</span></th>
							@if ($r_list->countSenior() > 0)
								<th><span >Discounted</span></th>
							@endif
						</tr>
					</thead>
					<tbody>
						<td><span>{{$rate['name']}} &#x20B1; {{ number_format($rate['price'], 2)}}</span></td> 
						<td><span>{{$r_list->getNoDays() > 1 ? $r_list->getNoDays() . ' days' : $r_list->getNoDays() . ' day'}}</span></td> 
						@if ($r_list->countSenior() > 0)
							<td><span data-prefix>&#x20B1; </span><span>{{ number_format($rate['orig_amount'], 2)}}</span></td>
						@endif
						<td><span data-prefix>&#x20B1; </span><span>{{ number_format($rate['amount'], 2)}}</span></td>
					</tbody>
				</table>
			@endif
			@if(!empty($tours))
				<table class="inventory">
					<thead>
						<tr>
							<th><span >Tour</span></th>
							<th><span >Quantity</span></th>
							<th><span >Price</span></th>
							<th><span >Amount</span></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($tours as $item)
							<tr>
								<td><span >{{$item['title']}}</span></td>
								<td><span>{{ $item['tpx'] }} guest</span></td>
								<td><span data-prefix>&#x20B1; </span><span>{{ number_format($item['price'], 2)}}</span></td>
								<td><span data-prefix>&#x20B1; </span><span>{{ number_format($item['amount'], 2)}}</span></td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
			@if(!empty($other_addons))
				<table class="inventory">
					<thead>
						<tr>
							<th><span >Addtional @if(count($other_addons) > 1) Items @else Item @endif</span></th>
							<th><span >Quantity</span></th>
							<th><span >Price</span></th>
							<th><span >Amount</span></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($other_addons as $key => $other)
							<tr>
								<td><span >{{$other['title']}}</span></td>
								<td><span data-prefix></span><span>{{$other['pcs'] ?? 0}} pcs</span></td>
								<td><span data-prefix>&#x20B1; </span><span>{{ number_format($other['price'], 2)}}</span></td>
								<td><span data-prefix>&#x20B1; </span><span>{{ number_format($other['amount'], 2)}}</span></td>
							</tr>
						@endforeach
					</tbody>
				</table>
			@endif
			
			<table class="balance">
				<tr>
					<th><span >{{$r_list->status < 3 ? 'Total' : 'Amount Paid'}}</span></th>
					<td><span data-prefix>&#x20B1; </span><span>{{number_format($r_list->getTotal(), 2)}}</span></td>
				</tr>
				@if($r_list->downpayment() > 0)

					<tr>
						<th><span >Downpayment</span></th>
						<td><span data-prefix>&#x20B1; </span><span>{{number_format($r_list->downpayment() ?? 0, 2)}}</span></td>
					</tr>
				@endif
				@if($r_list->checkInPayment() > 0)
					<tr>
						<th><span >Check-in Paid</span></th>
						<td><span data-prefix>&#x20B1; </span><span>{{number_format($r_list->checkInPayment() ?? 0, 2)}}</span></td>
					</tr>
				@endif
				@if($r_list->checkOutPayment() > 0)
					<tr>
						<th><span >Check-out Paid</span></th>
						<td><span data-prefix>&#x20B1; </span><span>{{number_format($r_list->checkOutPayment() ?? 0, 2)}}</span></td>
					</tr>
				@endif
			</table>
		</article>
	</body>
</html>