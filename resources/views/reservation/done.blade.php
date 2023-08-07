<x-landing-layout>
    <section class="bg-gray-50">
        <div x-data="{loader = false}" class="mx-auto max-w-screen-xl px-4 py-32 lg:flex lg:h-screen lg:items-center" >
          <x-loader />
          <div class="mx-auto max-w-xl text-center">
            <form action="{{route('reservation.done.message.store', ['id' => $id ?? abort(404)])}}" method="post">
              @csrf
              <h1 class="text-3xl font-extrabold sm:text-5xl">
                Done!!👏
              </h1>
        
              <p class="mt-4 sm:text-xl/relaxed">
                Just wait the process of your reservation okay👍
              </p>
              <p class="mt-4 sm:text-xl/relaxed">
                Do you want some request?
                <x-textarea name="message" id="message" />
                <div class="flex flex-wrap justify-center gap-4">
                  <a @click="loader = true" class="btn btn-ghost" href="{{route('home')}}">
                    Skip
                  </a>
                  <button @click="loader = true" class="btn btn-primary">
                    Send Request
                  </button>
                </div>
              </p>
      

            </form>
          </div>
        </div>
      </section>
</x-landing-layout>