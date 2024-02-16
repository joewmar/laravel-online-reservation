<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Access Control of {{$employee->name()}}" back="{{route('system.setting.accounts.edit', encrypt($employee->id))}}">
        <div class="overflow-x-auto mt-7">
            @foreach (accessLists() as $module => $subMod)
                <table class="table table-zebra table-row-group w-full">
                    <!-- head -->
                    <tbody>
                        <tr class="w-full flex justify-center items-center">
                            <td class="font-bold w-52">{{Str::title(str_replace('_', ' ', $module))}}</td>
                            @foreach ($subMod as $action)
                                <td class="flex justify-center items-center flex-col gap-2 mx-5 w-36">
                                    <h1 class="text-sm text-center">{{Str::title(str_replace('_', ' ', $action))}}</h1>
                                <input type="checkbox" name="access[]" value="{{$action}}({{$module}})" class="checkbox checkbox-primary" />
                                </td>
                            @endforeach
                        </tr>

                    </tbody>
                </table>
            @endforeach

          </div>
    </x-system-content>
</x-system-layout>