<x-landing-layout>
  <x-navbar :activeNav="$activeNav" type="plain" />
  <div class="hero min-h-screen bg-base-200">
    <div class="hero-content text-center">
      <div class="max-w-md">
        <h1 class="text-5xl font-bold">About Us</h1>
        <p class="py-6">Let's talk about Alvin and Angie Mt. Pinatubo Accommodation and Tours</p>
        <a href="#story" class="btn btn-primary">Get Started</a>
      </div>
    </div>
  </div>
  <x-full-content id="story">
    <article class="mx-auto prose-lg p-10 w-full md:w-[60%]" >
      <h2 class="text-center font-extrabold">Our Story</h2>
      <img src="{{asset('images/AlvinAngiePlace.jpg')}}" alt="Alvin and Angie Guest House Location" class="rounded-md">
      <p class="text-justify">
        In the heart of the breathtaking Mt. Pinatubo, a passion for hospitality gave birth to what is now known as "Alvin and Angie Mt. Pinatubo Accommodation and Tours." Our journey began with Mr. Alvin Bognot, a former driver and tourist guide at various accommodations. One memorable day, faced with tardy foreign customers arriving between 6:00 a.m. and 10:00 a.m., a time when tours to the mesmerizing Pinatubo are typically off-limits, Mr. Alvin Bognot found a solution.
      </p>
      <p class="text-justify">
        Spotting the lingering visitors the next day, he approached them with warmth and concern, offering his help and hospitality. A conversation with his co-owner and wife, Mrs. Angelita Bognot, led to a heartwarming decision – to share their two vacant rooms with these travelers in need. Despite the fact that their home was still under construction, lacking a ceiling, floor tiles, and paint, the Bognots opened their doors, ensuring the guests' comfort with the presence of attached bathrooms.
      </p>
      <p class="text-justify">
        News of their genuine kindness spread quickly, and soon, French tourists, in particular, were seeking out their accommodations, drawn by the tales of the initial two guests. The gesture sparked a chain reaction, as more and more visitors found their way to us, courtesy of recommendations from those who had already experienced our hospitality.
      </p>
      <p class="text-justify">
        Over time, our humble guest house expanded from its initial three rooms, with one reserved for the Bognot family, to the present-day fifteen rooms. This growth was a gradual journey, with each room being added over the course of years, not all at once. Additionally, during a particularly busy period, when the demand surpassed the availability, Mr. Alvin Bognot didn't hesitate to extend his warm welcome to guests, even accommodating them in a cozy local "kubo."
      </p>
      <p class="text-justify">
        Since its inception in December 2009, our establishment has been an unwavering source of comfort and connection for travelers seeking the beauty of Mt. Pinatubo. For over thirteen years and counting, we have continued to nurture the spirit of hospitality that defines us, inviting visitors from around the world to create unforgettable memories in this enchanting setting.
      </p>
    </article>
  </x-full-content>
  {{-- <x-full-content >
    <article class="mx-auto prose-lg p-10 w-full">
      <h2 class="text-center font-extrabold">Let' Introdunce</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="flex flex-col items-center p-10">
          <img src="{{asset('images/avatars/no-avatar.png')}}" class="w-32 rounded-full" />
          <h3 class="font-bold text-xl">Alvin Bognot</h3>
          <span class="text-lg">Founder</span>
        </div>
        <div class="flex flex-col items-center p-10">
          <img src="{{asset('images/avatars/no-avatar.png')}}" class="w-32 rounded-full" />
          <h3 class="font-bold text-xl">Angelita Bognot</h3>
          <span class="text-lg">Co-Founder</span>
        </div>
      </div>
    </article>
  </x-full-content> --}}
</x-landing-layout>