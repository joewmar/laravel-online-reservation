<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation">
        {{-- Calendar  --}}
        <button class="btn btn-primary float-right gap-2">
            <i class="fa fa-user" aria-hidden="true"></i>
            Add Book
        </button>
        <div class="my-20 ">
            <div id='calendar' class=""></div>

        </div>   
    
        {{-- Table  --}}
        <div class="my-20">
            <x-search />
            <div class="overflow-x-auto w-full">
                <table class="table w-full">
                    <!-- head -->
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Nationality</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- row 1 -->
                        <tr>
                            <td>
                            <div class="flex items-center space-x-3">
                                <div class="avatar">
                                <div class="mask mask-squircle w-12 h-12">
                                    <img src="/tailwind-css-component-profile-2@56w.png" alt="Avatar Tailwind CSS Component" />
                                </div>
                                </div>
            
                            </div>
                            </td>
                            <td>
                            <div>
                                <div class="font-bold">Hart Hagerty</div>
                                <div class="text-sm opacity-50">United States</div>
                            </div>
                            </td>
                            <td>22 years old</td>
                            <td>American</td>
                            <td>Pending</td>
                            <th>
                            <button class="link link-primary">Details</button>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>    

    </x-system-content>
    @push('scripts')
        <script type="module" src='{{Vite::asset("resources/js/reservation-calendar.js")}}'></script>
    @endpush
</x-system-layout>
