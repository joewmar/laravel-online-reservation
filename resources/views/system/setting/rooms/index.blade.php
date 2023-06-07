<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Room">
      
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href="#" class="btn btn-primary text-base-100">
                Add Room Type
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>No</th>
                  <th>Room Type</th>
                  <th>How many rooms</th>
                  <th>Maximium of Guest</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                <tr>
                  <td>1</td>
                  <td>Charlet</td>
                  <td>5 Rooms</td>
                  <td>4 Guest</td>
                  <th>
                  <button class="link link-primary">More details</button>
                  </th>
                </tr>
                <!-- row 2 -->
                <tr>
                    <td>1</td>
                    <td>Big House</td>
                    <td>3 Rooms</td>
                    <td>5 Guest</td>
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