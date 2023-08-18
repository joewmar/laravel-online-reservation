<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Your Profile">
        <div class="grid grid-flow-row md:grid-cols-3 gap-2 mt-11">
            <x-setting-card link="{{route('system.profile.edit')}}" icon="fa-solid fa-users" title="Edit Profile" description="Update your profile" />
            <x-setting-card link="system/profile/link" icon="fa-solid fa-briefcase" title="Business Social Link" description="Manage your social to communicate" />
            <x-setting-card link="{{route('system.profile.password')}}" icon="fa-solid fa-lock" title="Access Code" description="Manage your password and passcode" />
        </div>
    </x-system-content>
</x-system-layout>