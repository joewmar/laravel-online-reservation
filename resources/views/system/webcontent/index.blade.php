<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Website Content">
        <div class="grid grid-flow-row grid-row-4 gap-4">
            <section class="p-6 shadow-lg bg-base-100">
              <form novalidate="" action="" class="container flex flex-col mx-auto space-y-12 ng-untouched ng-pristine ng-valid">
                <fieldset class="grid grid-cols-4 gap-6 p-6 rounded-md shadow-sm">
                  <div class="space-y-2 col-span-full lg:col-span-1">
                    <p class="font-bold text-xl">Main Hero Photos</p>
                  </div>
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 col-span-full lg:col-span-3">
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 1</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 2</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 3</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 4</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 5</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
    
                  </div>
                </fieldset>
    
              </form>
            </section>
            <section class="p-6 shadow-lg bg-base-100">
              <form novalidate="" action="" class="container flex flex-col mx-auto space-y-12 ng-untouched ng-pristine ng-valid">
                <fieldset class="grid grid-cols-4 gap-6 p-6 rounded-md shadow-sm">
                  <div class="space-y-2 col-span-full lg:col-span-1">
                    <p class="font-bold text-xl">Gallery Photo</p>
                    <div class="form-control w-full float-right">
                      <label class="label">
                        <span class="label-text">Number of photo</span>
                      </label>
                      <input type="number" placeholder="Type here" class="input input-bordered w-full max-w-xs" value="1" min="1"/>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 col-span-full lg:col-span-3">
    
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 1</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 2</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 3</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 4</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
                      <fieldset class="w-full space-y-1">
                        <label for="files" class="block text-lg font-medium">Image 5</label>
                        <div class="flex">
                          <input type="file" name="files" id="files" class="px-8 py-12 border-2 border-dashed border-primary rounded-md">
                        </div>
                      </fieldset>
    
                  </div>
                </fieldset>
    
              </form>
            </section>
    
            <section class="p-6 shadow-lg bg-base-100">
              <form novalidate="" action="" class="container flex flex-col mx-auto space-y-12 ng-untouched ng-pristine ng-valid">
                <fieldset class="grid grid-cols-4 gap-6 p-6 rounded-md shadow-sm">
                  <div class="space-y-2 col-span-full lg:col-span-1">
                    <p class="font-bold text-xl">Reservation Services</p>
                    <div class="form-control w-96">
                      <label class="label">
                        <span class="label-text ">Shut down Reservaton Operation?</span>
                        <input type="checkbox" class="ml-10 toggle toggle-lg toggle-primary" checked />
                      </label>
                      <label class="label">
                        <span class="label-text">Date to shutdown?</span>
                        <input type="date" class="ml-10 input input-primary"/>
                      </label>
                    </div>
                  </div>
                </fieldset>

              </form>
            </section>
          </div>
    </x-system-content>
</x-system-layout>