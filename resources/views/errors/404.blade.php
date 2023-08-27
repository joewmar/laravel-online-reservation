<x-landing-layout noFooter>
    <section class="bg-gray-50">
        <div x-data="{loader = false}" class="mx-auto max-w-screen-xl px-4 py-32 lg:flex lg:h-screen lg:items-center" >
          <x-loader />
          <div class="mx-auto max-w-xl text-center">
            <h1 class="text-3xl font-extrabold sm:text-5xl">
              404😔
            </h1>
            <p class="mt-4 sm:text-xl/relaxed">
              Page Not Found🚫
            </p>
            <p class="mt-4">
              <div class="flex flex-wrap justify-center gap-4">
                <a @click="loader = true" class="btn btn-primary" href="{{URL::previous()}}">
                  Go Home
                </a>
              </div>
            </p>
          </div>
        </div>
      </section>
</x-landing-layout>