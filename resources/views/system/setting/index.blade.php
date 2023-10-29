<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Setting">
        <div class="grid grid-flow-row md:grid-cols-2 place-items-center gap-2 mt-11">
            <x-setting-card link="{{route('system.setting.accounts.home')}}" icon="fa-solid fa-users" title="Account" description="Manage your Employee Account" />
            <x-setting-card link="{{route('system.setting.audit.home')}}" icon="fa-solid fa-address-book" title="Activity Log" description="Manage your Business Activity" />
            {{-- <x-setting-card link="{{ route('system.setting.rooms.home') }}" icon="fa-solid fa-hotel" title="Rooms" description="Manage the number of rooms" /> --}}
            {{-- <x-setting-card link="{{ route('system.setting.rides.home') }}" icon="fa-solid fa-truck-pickup" title="Rides" description="Manage the number of vechicles" /> --}}
            {{-- <x-setting-card link="{{ route('system.setting.tour.home') }}" icon="fa-solid fa-location-dot" title="Tour" description="Manage the tour destination" /> --}}
        </div>
    </x-system-content>
</x-system-layout>