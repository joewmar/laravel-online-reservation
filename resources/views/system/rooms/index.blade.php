<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Rooms">
        <fieldset class="w-full space-y-1 text-neutral">
            <label for="Search" class="hidden">Search Room</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-2 ">
                <button type="button" title="search" class="p-1 focus:outline-none focus:ring">
                  <i class="fa-solid fa-magnifying-glass text-neutral"></i>
                </button>
              </span>
              <input type="search" name="Search" placeholder="Search Room..." class="w-52 py-2 pl-10 text-sm rounded-md sm:w-auto focus:outline-none focus:border-violet-400 ">
            </div>
          </fieldset>
          <div class="mt-8 grid grid-flow-row md:grid-cols-3 gap-8">
            <a
              class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-zinc-500 hover:shadow-zinc-700"
            >
              <h2 class="mt-4 text-xl font-bold text-neutral">Room No. 1</h2>
      
              <p class="mt-1 text-sm text-neutral-600">

              </p>
            </a>
      
            <label
              class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-zinc-500 hover:shadow-zinc-700"
              for="my-modal-6"
            >
              <h2 class="mt-4 text-xl font-bold text-neutral">Room No. 2</h2>
    
              <p class="mt-1 text-sm text-neutral-600">

              </p>
            </label>
    
          <a
            class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-zinc-500 hover:shadow-zinc-700"
            href="/services/digital-campaigns"
          >
            <h2 class="mt-4 text-xl font-bold text-neutral">Room No. 3</h2>
    
            <p class="mt-1 text-sm text-neutral-600">

            </p>
          </a>
    
          <a
            class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-zinc-500 hover:shadow-zinc-700"
            href="/services/digital-campaigns"
          >
            <h2 class="mt-4 text-xl font-bold text-neutral">Room No. 4</h2>
    
            <p class="mt-1 text-sm text-neutral-600">

            </p>
          </a>
    
          <a
            class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-zinc-500 hover:shadow-zinc-700"
            href="/services/digital-campaigns"
          >
            <h2 class="mt-4 text-xl font-bold text-neutral">Room No. 5</h2>
    
            <p class="mt-1 text-sm text-neutral-600">

            </p>
          </a>
    
          <a
            class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-zinc-500 hover:shadow-zinc-700"
            href="/services/digital-campaigns"
          >
            <h2 class="mt-4 text-xl font-bold text-neutral">Room No. 6</h2>
    
            <p class="mt-1 text-sm text-neutral-600">

            </p>
          </a>
          </div>
    </x-system-content>
</x-system-layout>