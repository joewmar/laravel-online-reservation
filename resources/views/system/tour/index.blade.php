<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Tour Management">
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href=" {{ route('system.tour.create') }}" class="btn btn-primary text-base-100">
                Add Tour Package
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>No</th>
                  <th>Packages Name</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                <tr>
                  <td>1</td>
                  <td>Atv Combo trail Without Pinatubo Crater Hike (Lahar Canyons/Tambo Lake/Aeta Village/Bulacan trail)</td>
                  <th>
                  <button class="link link-primary">More details</button>
                  </th>
                </tr>
                <!-- row 2 -->
                <tr>
                  <td>2</td>
                  <td>1PAX Mt. Pinatubo 4x4 Rate Day Tour</td>
                  <th>
                    <button class="link link-primary">More details</button>
                  </th>
                </tr>
    
                </tbody>
                
              </table>
              </div>
          </div>
    </x-system-content>
</x-system-layout>