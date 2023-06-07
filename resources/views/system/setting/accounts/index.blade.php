<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Accounts">
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href="#" class="btn btn-primary text-base-100">
                Add Account
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>Id</th>
                  <th>Full Name</th>
                  <th>Role</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                <tr>
                  <td>20235687</td>
                  <td>Juan Dela Cruz</td>
                  <td>Front Desk</td>
                  <th>
                  <button class="link link-primary">More details</button>
                  </th>
                </tr>
                <!-- row 2 -->
                <tr>
                    <td>1897871</td>
                    <td>Mark Lito Basco</td>
                    <td>Front Desk</td>
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