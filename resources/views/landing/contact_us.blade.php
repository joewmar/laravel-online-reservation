<x-landing-layout>

  <x-navbar :activeNav="$activeNav" type="plain" />

  <section class="pt-24 p-6 {{!empty($contacts) ? 'h-auto' : 'h-screen'}} w-full">
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
            <span>{{env('MAIN_CONTACT_NUMBER')}}</span>
          </p>
          <p class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2 sm:mr-6">
              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
            </svg>
            <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{env('MAIN_CONTACT_EMAIL', '#')}}">{{env('MAIN_CONTACT_EMAIL', 'None')}}</a></span>
          </p>
        </div>
      </div>

      <div class="flex flex-col py-6 space-y-6 md:py-0 md:px-6">
        <div class="w-full">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1923.9581619109745!2d120.42731809197846!3d15.326781878810142!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x339696709fcd03e7%3A0x8ec08060d59d299c!2sALVIN%20and%20ANGIE%20BOGNOT%20mt%20PINATUBO%20(%20Accommodation%20and%20Tours)%20Tripadvisor%20Alvin%20Guesthouse%40%20PINATUBO!5e0!3m2!1sen!2sph!4v1693218928986!5m2!1sen!2sph" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="w-full h-96 shadow-md border border-primary"></iframe>
        </div>
      </div>
    </div>

      @if(!empty($contacts))
        <div class="divider"></div>
          <h1 class="text-2xl font-bold pt-5 px-6">Other Contact</h1>
          <div class="my-5 grid grid-cols-1 md:grid-cols-3 gap-3 px-6">
            @foreach ($contacts as $contact)
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