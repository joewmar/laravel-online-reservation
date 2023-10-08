@php
  $link = "/";
  if(auth('system')->check()) $link = route('system.home');
@endphp

<x-landing-layout noFooter>
    <section class="bg-gray-50">
        <div x-data="{loader = false}" class="mx-auto max-w-screen-xl px-4 py-32 lg:flex lg:h-screen lg:items-center" >
          <x-loader />
          <div class="mx-auto max-w-xl text-center">
            <h1 class="my-5 text-4xl font-black sm:text-8xl">
              404
            </h1>
            <p class="sm:text-xl/relaxed font-medium">
              Sorry, we couldn't find this page.
            </p>
            <p class="mt-4 sm:text-md/relaxed">But dont worry, you can find plenty of other things on our homepage.</p>

            <p class="mt-4">
              <div class="flex flex-wrap justify-center gap-4">
                <a class="btn btn-primary" href="{{$link}}">
                  Back to homepage
                </a>
              </div>
            </p>
          </div>
        </div>
      </section>
</x-landing-layout>