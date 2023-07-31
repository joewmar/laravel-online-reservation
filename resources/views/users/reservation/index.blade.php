<x-landing-layout>
    <x-navbar :activeNav="$activeNav" type="plain"/>

    <x-full-content>
        <section class="pt-24 flex flex-col justify-center items-center h-screen">
            <div class="tabs tabs-boxed">
                <a class="tab tab-active">Pending</a> 
                <a class="tab">Canceled</a> 
                <a class="tab">Reschedule</a>
                <a class="tab">Previous</a>
              </div>
            <div class="grid card bg-base-100 rounded-box place-items-center h-full w-5/6 ">
                <div class="overflow-x-auto w-full">
                  <table class="table w-full">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Number of guest</th>
                        <th>Packages</th>
                        <th>Accommodation</th>
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
                        <td>2 guest</td>
                        <td>Package 2</td>
                        <td>Double Charlet Room</td>
                        <th>
                          <button class="btn btn-error btn-xs">Cancel</button>
                          <button class="btn btn-warning btn-xs">Reschedule</button>
                        </th>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
          </section>
    </x-full-content>
</x-landing-layout>
