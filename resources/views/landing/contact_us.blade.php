<x-landing-layout>

  <x-navbar :activeNav="$activeNav" type="plain" />

  <section class="pt-24 p-6 {{!empty($contacts['other']) ? 'h-full' : 'h-full md:h-screen'}} w-full">
    <div class="grid max-w-6xl grid-cols-1 px-6 mx-auto lg:px-8 md:grid-cols-2">
      <div class="py-6 md:py-0 md:px-6">
        <h1 class="text-4xl font-bold py-5">Call Me</h1>
        <div class="space-y-4">
          <p class="flex items-center">
            <svg class="w-5 h-5 mr-2 sm:mr-6" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M408 120c0 54.6-73.1 151.9-105.2 192c-7.7 9.6-22 9.6-29.6 0C241.1 271.9 168 174.6 168 120C168 53.7 221.7 0 288 0s120 53.7 120 120zm8 80.4c3.5-6.9 6.7-13.8 9.6-20.6c.5-1.2 1-2.5 1.5-3.7l116-46.4C558.9 123.4 576 135 576 152V422.8c0 9.8-6 18.6-15.1 22.3L416 503V200.4zM137.6 138.3c2.4 14.1 7.2 28.3 12.8 41.5c2.9 6.8 6.1 13.7 9.6 20.6V451.8L32.9 502.7C17.1 509 0 497.4 0 480.4V209.6c0-9.8 6-18.6 15.1-22.3l122.6-49zM327.8 332c13.9-17.4 35.7-45.7 56.2-77V504.3L192 449.4V255c20.5 31.3 42.3 59.6 56.2 77c20.5 25.6 59.1 25.6 79.6 0zM288 152a40 40 0 1 0 0-80 40 40 0 1 0 0 80z"/></svg>
            <span>{{env('MAIN_ADDRESS')}}</span>
          </p>
          <p class="flex items-center">
            <svg class="w-5 h-5 mr-2 sm:mr-6" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"/></svg>
            <span>{{$contacts['main'] ? ($contacts['main']['contactno']  ?? 'None') : 'None'}}</span>
          </p>
          <p class="flex items-center">
            <svg class="w-5 h-5 mr-2 sm:mr-6" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>
            <span><a class="link link-hover" href="https://wa.me/{{$contacts['main'] ? ($contacts['main']['whatsapp'] ?? 'None') : 'None'}}" target="_blank" rel="noopener noreferrer">{{$contacts['main'] ? $contacts['main']['whatsapp'] : 'None'}}</a></span>
          </p>
          <p class="flex items-center">
            <svg class="w-5 h-5 mr-2 sm:mr-6" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M64 112c-8.8 0-16 7.2-16 16v22.1L220.5 291.7c20.7 17 50.4 17 71.1 0L464 150.1V128c0-8.8-7.2-16-16-16H64zM48 212.2V384c0 8.8 7.2 16 16 16H448c8.8 0 16-7.2 16-16V212.2L322 328.8c-38.4 31.5-93.7 31.5-132 0L48 212.2zM0 128C0 92.7 28.7 64 64 64H448c35.3 0 64 28.7 64 64V384c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128z"/></svg>
            <span><a class="link link-hover" href="https://mail.google.com/mail/?view=cm&fs=1&to={{$contacts['main'] ? ($contacts['main']['email']  ?? 'None') : 'None'}}" target="_blank" rel="noopener noreferrer">{{$contacts['main'] ? $contacts['main']['email']  ?? 'None' : 'None'}}</a></span>
          </p>
          <p class="flex items-center">
            <svg class="w-5 h-5 mr-2 sm:mr-6" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512">
              <path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/>
            </svg>
            <span><a class="link link-hover" href="{{$contacts['main'] ? ($contacts['main']['fbuser']  ?? 'None') : 'None'}}" target="_blank" rel="noopener noreferrer">Facebook Page</a></span>
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