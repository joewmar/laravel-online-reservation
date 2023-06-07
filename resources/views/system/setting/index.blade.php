<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Setting">
        <div class="grid grid-flow-row md:grid-cols-3 gap-2 mt-11">
            <x-setting-card link="/setting/accounts" icon="fa-solid fa-users" title="Account" description="Manage your Employee Account" />
            <x-setting-card link="/setting/rooms" icon="fa-solid fa-users" title="Rooms" description="Manage the number of rooms" />
            <x-setting-card link="/setting/rides" icon="fa-solid fa-users" title="Account" description="Manage the number of vechicles" />
        </div>
    </x-system-content>
</x-system-layout>