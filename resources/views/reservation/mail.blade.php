<x-landing-layout>
  <x-full-content>
    <div class="flex justify-center items-center w-full h-screen bg-primary">
      <article class="prose shadow-2xl bg-base-200 w-96 h-72 p-10">
          <h3 class="font-bold">Dear {{$details['name']}}</h3>
          <p class="font-medium">
            {{$details['body']}}
          </p>
          <p>Thank you</p>
      </article>
    </div>
  </x-full-content>
</x-landing-layout>