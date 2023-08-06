
<html>
	<head>
		<meta charset="utf-8">
		<title>Digital Receipt</title>
		<link rel="stylesheet" href="style.css">
		<link rel="license" href="https://www.opensource.org/licenses/mit-license/">
		<script src="script.js"></script>
		<style>
		/* reset */

*
{
	border: 0;
	box-sizing: content-box;
	color: inherit;
	font-family: inherit;
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

body { box-sizing: border-box; height: 11in; margin: 0 auto; overflow: hidden; padding: 0.5in; width: 8.5in; }
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
article address > span{ font-size: 60%; font-weight: 500; }

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
table.inventory th { font-weight: bold; text-align: center; }

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
			<address style="margin-top: 17px">
				<p>{{ str_replace('_', ' ', config('app.name'))}}</p>
				<p>Sta. Juliana, Capas Tarlac, Philippines.</p>
				<p>09123456789</p>
			</address>
			{{-- <span><img alt="" src="{{asset('images/logo.png')}}"></span> --}}
		</header>
		<article>

			<table class="meta">
				<tr>
					<th><span >Invoice #</span></th>
					<td><span >{{$r_list->id}}</span></td>
				</tr>
				<tr>
					<th><span >Date</span></th>
					<td><span >{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $r_list->updated_at)->format('F j, Y') ?? 'None'}}</span></td>
				</tr>	
			</table>
			<h1>Recipient</h1>
			<address >
				<p>{{$r_list->userReservation->name()}}<br></p>
				<span>{{$r_list->age}} years old from {{$r_list->userReservation->country}}<br></span>
				<div>

				</div>
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
			</div>
			<table class="inventory">
				<thead>
					<tr>
						<th><span >Room No</span></th>
						<th><span >Room Name</span></th>
					</tr>
				</thead>
				<tbody>
					@php $amount = 0; @endphp
					@foreach ($rooms as $key => $item)
						<tr>
							<td><span >Room No. {{$item['no']}}</span></td>
							<td><span >{{$item['name']}} Room</span></td>
						</tr>
					@endforeach
				</tbody>
			</table>
			<table class="inventory">
				<thead>
					<tr>
						<th><span >Room Type</span></th>
						<th><span >No. of days</span></th>
						<th><span >Rate</span></th>
						<th><span >Amount</span></th>
					</tr>
				</thead>
				<tbody>
					<td><span>{{$rate->name}}</span></td> 
					<td><span>{{checkDiffDates($r_list->check_in, $r_list->check_out) > 1 ? checkDiffDates($r_list->check_in, $r_list->check_out) . ' days' : checkDiffDates($r_list->check_in, $r_list->check_out) . ' day'}}</span></td> 
					<td><span data-prefix>₱ </span><span>{{ number_format($rate->price, 2)}}</span></td>
					<td><span data-prefix>₱ </span><span>{{ number_format(($rate->price * (int) checkDiffDates($r_list->check_in, $r_list->check_out)), 2)}}</span></td>
				</tbody>
			</table>
			@if($r_list->accommodation_type != 'Room Only')
			<table class="inventory">
				<thead>
					<tr>
						<th><span >Tour</span></th>
						<th><span >Type</span></th>
						<th><span >Price</span></th>
						<th><span >Amount</span></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($menu as $item)
						<tr>
							<td><span >{{$item['title']}}</span></td>
							<td><span>{{$item['type']}} - {{$item['pax']}} guest</span></td> 
							<td><span data-prefix>₱ </span><span>{{ number_format($item['price'], 2)}}</span></td>
							@php $amount = (double)$item['price'] * (int)$item['pax']; @endphp
							<td><span data-prefix>₱ </span><span>{{ number_format($amount, 2)}}</span></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif

			@if(!empty($add_menu))
				{{-- <table class="inventory">
					<thead>
						<tr>
							<th><span >Room</span></th>
							<th><span >Room Type</span></th>
							<th><span >Rate</span></th>
						</tr>
					</thead>
					<tbody>
						@foreach ($rooms as $key => $item)
							<tr>
								<td><span >Room No. {{$item->room_no}} ({{$item->room->name}})</span></td>
								<td><span>{{$rate->name}}</span></td> 
								<td><span data-prefix>₱ </span><span>{{ number_format($rate->price, 2)}}</span></td>
							</tr>
						@endforeach
					</tbody>
				</table> --}}
			@endif
			
			<table class="balance">
				<tr>
					<th><span >{{$r_list->status < 3 ? 'Total' : 'Amount Paid'}}</span></th>
					<td><span data-prefix>₱ </span><span>{{number_format($r_list->total, 2)}}</span></td>
				</tr>
				@if($r_list->status < 3)
					<tr>
						<th><span >Downpayment</span></th>
						<td><span data-prefix>₱ </span><span>{{number_format($r_list->downpayment ?? 0, 2)}}</span></td>
					</tr>
					<tr>
						<th><span >Balance Due</span></th>
						@php
							$balance = (double) abs($r_list->total - $r_list->downpayment);
						@endphp
						<td><span data-prefix>₱ </span><span>{{number_format($balance ?? 0, 2)}}</span></td>
					</tr>
				@endif
			</table>
		</article>
		@if($r_list->status < 3)
			<div class="details">
				<h5 style="color:red">Note: <span>The total amount to be paid will still depend on the situation, such as add-ons. It will find out at the check-out how much it will really be.</span></h5>
			</div>
		@endif
		<aside>
			<h1><span >Contact us</span></h1>
			<div >
				<p align="center">Email :- info@sunrise.com || Web :- www.sunrise.com || Phone :- +94 65 222 44 55 </p>
			</div>
		</aside>
	</body>
</html>