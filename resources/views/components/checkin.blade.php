@props(['id' => 'checkin', 'name', 'datas' => ''])
<x-modal id="{{$id}}" title="Check-in for {{$name}}">
    @if( \Carbon\Carbon::now()->format('Y-m-d') == $datas['check_in'])
        <article>
            <h1>Sorry, </h1>
        </article>
    @else
        <article>
            <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral">
                <li><strong>Type: </strong> {{$datas['accommodation_type'] ?? 'None' }} </li>
                <li><strong>Payment Method: </strong> {{$datas['payment_method'] ?? 'None'}}</li>
                <li><strong>Number of Guest: </strong> {{$datas['pax'] ?? 'None'}}</li>
                <li><strong>Room No: </strong> {{\App\Models\Room::findOrFail($datas['room_id'])->room_no ?? 'None'}} ({{ \App\Models\Room::findOrFail($datas['room_id'])->room->name}} Room)</li>
                <li><strong>Check-in: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_in'])->format('l, F j, Y') ?? 'None'}}</li>
                <li><strong>Check-in: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_out'])->format('l, F j, Y') ?? 'None'}}</li>
                <li><strong>Present in this day: </strong> </li>
            </ul>
        </article>

    @endif

</x-modal>