<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Feedback">
        <div class="grid grid-flow-row grid-row-4 gap-4 my-5">
            @forelse ($feedbacks as $item)
                <x-feedback-card id="{{$item->reservation_id}}" name="{{$item->feedback->userReservation->name()}}" rating="{{$item->rating}}" comment="{{$item->message}}" />
            @empty
                <article class="rounded-lg border border-neutral p-4 shadow-sm transition hover:shadow-lg sm:p-6" >
                    <h3 class="mt-0.5 text-lg font-medium text-gray-900">
                        No Feedback Record
                    </h3>
                </article>
            @endforelse
        </div>
    </x-system-content>
</x-system-layout>