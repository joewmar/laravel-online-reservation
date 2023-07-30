@php
	$arrAction = [
		"pending" => "Pending",
		"confirmed" => "Confirmed",
		"checkin" => "Check-in",
		"checkout" => "Check-out",
		"previous" => "Previous",
		"cancelation" => "Cancelation",
		"disaprove" => "Disaprove",
	];
@endphp
<div class="join">
	<div>
	  <div>
		<input class="input input-sm input-primary join-item" name="search" placeholder="Search..." value="{{request('search') ?? ""}}" />
	  </div>
	</div>
	<select name="action" class="select select-sm select-primary join-item">
	  <option value="" selected>All</option>
	  @foreach($arrAction as $key => $item)
			@if (request('tab') == $key)
				<option value="{{$key}}" selected>{{$item}}</option>

			@else
				<option value="{{$key}}" >{{$item}}</option>
			@endif
	  @endforeach
	</select>
	<button class="btn btn-sm join-item btn-primary">Search</button>
  </div>