<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Ride Vechicles">
     
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href="#" class="btn btn-primary text-base-100">
                Add Rides
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>No</th>
                  <th>Ride Vechile Model</th>
                  <th>How many vechicle</th>
                  <th>Maximium of Guest</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                <tr>
                  <td>1</td>
                  <td>ATV</td>
                  <td>4</td>
                  <td>2 Guest</td>
                  <th>
                    <button class="link link-primary">Edit details</button>
                  </th>
                </tr>
                <!-- row 2 -->
                <tr>
                    <td>1</td>
                    <td>4x4 Jeep Wrangler</td>
                    <td>5</td>
                    <td>7 Guest</td>
                    <th>
                        <button class="link link-primary">Edit details</button>
                    </th>
                </tr>
    
                </tbody>
                
              </table>
              </div>
          </div>
    </x-system-content>
</x-system-layout>