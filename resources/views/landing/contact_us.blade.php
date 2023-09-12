<x-landing-layout>

  <x-navbar :activeNav="$activeNav" type="plain" />

  <section class="pt-24 p-6 {{!empty($contacts['other']) ? 'h-auto' : 'h-screen'}} w-full">
    <div class="grid max-w-6xl grid-cols-1 px-6 mx-auto lg:px-8 md:grid-cols-2">
      <div class="py-6 md:py-0 md:px-6">
        <h1 class="text-4xl font-bold py-5">Call Me</h1>
        <div class="space-y-4">
          <p class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 sm:mr-6">
              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
            </svg>
            <span>{{env('MAIN_ADDRESS')}}</span>
          </p>
          <p class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 sm:mr-6">
              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
            </svg>
            <span>{{$contacts['main'] ? $contacts['main']['contactno']  ?? 'None' : 'None'}}</span>
          </p>
          <p class="flex items-center">
            <svg viewBox="0 0 32 32" class="w-5 h-5 mr-2 sm:mr-6"><path d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z" fill-rule="evenodd"></path></svg>
            <span><a href="https://wa.me/{{$contacts['main'] ? $contacts['main']['whatsapp'] ?? '' : ''}}">{{$contacts['main'] ? $contacts['main']['whatsapp'] : 'None'}}</a></span>
          </p>
          <p class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 sm:mr-6">
              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
            </svg>
            <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{$contacts['main'] ? $contacts['main']['email']  ?? '' : ''}}">{{$contacts['main'] ? $contacts['main']['email']  ?? 'None' : 'None'}}</a></span>
          </p>
        </div>
      </div>

      <div class="flex flex-col py-6 space-y-6 md:py-0 md:px-6">
        <div class="w-full">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1923.9581619109745!2d120.42731809197846!3d15.326781878810142!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x339696709fcd03e7%3A0x8ec08060d59d299c!2sALVIN%20and%20ANGIE%20BOGNOT%20mt%20PINATUBO%20(%20Accommodation%20and%20Tours)%20Tripadvisor%20Alvin%20Guesthouse%40%20PINATUBO!5e0!3m2!1sen!2sph!4v1693218928986!5m2!1sen!2sph" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="w-full h-96 shadow-md border border-primary"></iframe>
        </div>
      </div>
    </div>

      @if(!empty($contacts['other']))
        <div class="divider"></div>
          <h1 class="text-2xl font-bold pt-5 px-6">Other Contact</h1>
          <div class="my-5 grid grid-cols-1 md:grid-cols-3 gap-3 px-6">
            @foreach ($contacts['other'] as $contact)
                <label class="w-96 h-auto shadow-lg rounded-md bg-base-100 text-neutral hover:bg-primary hover:text-primary-content pl-5 p-5" for="contact{{$loop->index}}">
                  <h1 class="text-xl font-bold">{{$contact['name']}}</h1>
                  <span class="text-xs">Click to More Details</span>
                </label>
                <x-modal id="contact{{$loop->index}}" title="{{$contact['name']}} Contacts">
                  <div class="flex space-x-3">
                    <p class="text-sm font-semibold">Contact Number:</p>
                    <ul class="text-sm font-normal">
                        @foreach ($contact['contactno'] as $contactno)
                            <li>{{$contactno}}</li>
                        @endforeach
                    </ul>
                  </div>
                  <div class="flex space-x-3">
                      <p class="text-sm font-semibold"> Contact Email:</p>
                      <ul class="text-sm font-normal">
                          @foreach ($contact['email'] as $email)
                              <li ><a class="link-hover" href="https://mail.google.com/mail/?view=cm&fs=1&to={{$email}}">{{$email}}</a></li>
                          @endforeach
                      </ul>
                  </div>
                  {{-- <div class="flex space-x-3">
                      <p class="text-sm font-semibold">Facebook:</p>
                      <ul class="text-sm font-normal">
                          @foreach ($contact['fbuser'] as $fbuser)
                              <li>{{$fbuser}}</li>
                          @endforeach
                      </ul>
                  </div> --}}
                  <div class="flex space-x-3">
                      <p class="text-sm font-semibold">WhatsApp:</p>
                      <ul class="text-sm font-normal">
                          @foreach ($contact['whatsapp'] as $whatsapp)
                              <li>{{$whatsapp}}</li>
                          @endforeach
                      </ul>
                  </div>
                </x-modal>
            @endforeach
          </div>
      @endif
  </section>

</x-landing-layout>