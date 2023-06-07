<x-system-layout :activeSb="$activeSb">
    <x-system-content title="News">
        <div class="overflow-x-auto w-full">
            <table class="table w-full">
              <!-- head -->
              <thead>
              <tr>
                <th>No</th>
                <th>Announcement</th>
                <th>Dates</th>
                <th></th>
              </tr>
              </thead>
              <tbody>
              <!-- row 1 -->
              <tr>
                <td>
                <div class="flex items-center space-x-3">
                  <div>
                  <div class="font-bold">Hart Hagerty</div>
                  <div class="text-sm opacity-50">United States</div>
                  </div>
                </div>
                </td>
                <td>
                Zemlak, Daniel and Leannon
                <br/>
                <span class="badge badge-ghost badge-sm">Desktop Support Technician</span>
                </td>
                <td>Purple</td>
                <th>
                <button class="btn btn-ghost btn-xs">details</button>
                </th>
              </tr>
              <!-- row 2 -->
              <tr>
                <td>
                <div class="flex items-center space-x-3">
                  <div>
                  <div class="font-bold">Brice Swyre</div>
                  <div class="text-sm opacity-50">China</div>
                  </div>
                </div>
                </td>
                <td>
                Carroll Group
                <br/>
                <span class="badge badge-ghost badge-sm">Tax Accountant</span>
                </td>
                <td>Red</td>
                <th>
                <button class="btn btn-ghost btn-xs">details</button>
                </th>
              </tr>
              <!-- row 3 -->
              <tr>
                <td>
                <div class="flex items-center space-x-3">
                  <div>
                  <div class="font-bold">Marjy Ferencz</div>
                  <div class="text-sm opacity-50">Russia</div>
                  </div>
                </div>
                </td>
                <td>
                Rowe-Schoen
                <br/>
                <span class="badge badge-ghost badge-sm">Office Assistant I</span>
                </td>
                <td>Crimson</td>
                <th>
                <button class="btn btn-ghost btn-xs">details</button>
                </th>
              </tr>
              <!-- row 4 -->
              <tr>
                <td>
                <div class="flex items-center space-x-3">
                  <div>
                  <div class="font-bold">Yancy Tear</div>
                  <div class="text-sm opacity-50">Brazil</div>
                  </div>
                </div>
                </td>
                <td>
                Wyman-Ledner
                <br/>
                <span class="badge badge-ghost badge-sm">Community Outreach Specialist</span>
                </td>
                <td>Indigo</td>
                <th>
                <button class="btn btn-ghost btn-xs">details</button>
                </th>
              </tr>
              </tbody>
              
            </table>
            </div>
          <div class="flex justify-end space-x-4">
            <button type="button" class="btn btn-primary">
                <span class="sr-only sm:not-sr-only">Create Annoucement</span>
            </button>
          </div>
    </x-system-content>
</x-system-layout>