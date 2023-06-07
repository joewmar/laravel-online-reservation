<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit Profile">
          <section class="pt-24 p-6 text-neutral">
            <form novalidate="" action="" class="container flex flex-col mx-auto space-y-12 ng-untouched ng-pristine ng-valid">
                <fieldset class="grid grid-cols-4  gap-6 p-6 rounded-md shadow-sm">
                    <div class="grid grid-cols-1 gap-4 col-span-full lg:col-span-3">
                        <div>
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text">Full Name</span>
                                </label>
                                <input type="text" class="input input-bordered input-md input-primary w-full" />
                                <label class="label">
                                    <span class="label-text-alt">Bottom Left label</span>
                                    <span class="label-text-alt">Bottom Right label</span>
                                </label>
                            </div>
                        </div>
                <div>
                  <div class="form-control w-full">
                    <label class="label">
                      <span class="label-text">Username</span>
                    </label>
                    <input type="text" placeholder="Type here" class="input input-bordered input-md input-primary w-full" />
                    <label class="label">
                      <span class="label-text-alt">Bottom Left label</span>
                      <span class="label-text-alt">Bottom Right label</span>
                    </label>
                  </div>
                        </div>
                <div>
                  <div class="form-control w-full">
                    <label class="label">
                      <span class="label-text">Passowrd</span>
                    </label>
                    <input type="password" placeholder="Type here" class="input input-bordered input-md input-primary w-full" />
                    <label class="label">
                      <span class="label-text-alt">Bottom Left label</span>
                      <span class="label-text-alt link link-primary">Change password</span>
                    </label>
                  </div>
                    </div>
                </fieldset>
            </form>
        </section>

        </div>    
    </section>
  </x-system-content>
</x-system-layout>
