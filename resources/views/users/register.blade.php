@include('partials.header')
<section class="bg-white">
  <div class="lg:grid lg:min-h-screen lg:grid-cols-12">
    <section
      class="relative flex h-32 items-end bg-gray-900 lg:col-span-5 lg:h-full xl:col-span-6"
    >
    <a href="/" title="Home">
      <img alt="hero" src="{{asset('images/main-hero3.jpg')}}" class="absolute inset-0 h-full w-full object-cover opacity-80"/>
    </a>
    </section>

    <main
      aria-label="Main"
      class="flex items-center justify-center px-8 py-8 sm:px-12 lg:col-span-7 lg:py-12 lg:px-16 xl:col-span-6"
    >
      <div class="max-w-xl lg:max-w-3xl">
        <div class="relative -mt-16 block lg:hidden">
          <a class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-white text-primary sm:h-20 sm:w-20" href="/">
            <span class="sr-only">Home</span>
            <div>LOGO</div>
          </a>
        </div>
        <h1 class="mt-6 text-2xl font-bold text-gray-900 sm:text-3xl md:text-4xl">
        Register
      </h1>

      <p class="mt-4 leading-relaxed text-gray-500">
        Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eligendi nam
        dolorum aliquam, quibusdam aperiam voluptatum.
      </p>

        <form action="#" class="mt-8 grid grid-cols-6 gap-6">
          <div class="col-span-6 sm:col-span-3">
            <label
              for="FirstName"
              class="block text-sm font-medium text-gray-700"
            >
              First Name
            </label>

            <input
              type="text"
              id="FirstName"
              name="first_name"
              class="mt-1 w-full rounded-md border-gray-200 bg-white text-sm text-gray-700 shadow-sm"
            />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <label
              for="LastName"
              class="block text-sm font-medium text-gray-700"
            >
              Last Name
            </label>

            <input
              type="text"
              id="LastName"
              name="last_name"
              class="mt-1 w-full rounded-md border-gray-200 bg-white text-sm text-gray-700 shadow-sm"
            />
          </div>

          <div class="col-span-6">
            <label for="Email" class="block text-sm font-medium text-gray-700">
              Email
            </label>

            <input
              type="email"
              id="Email"
              name="email"
              class="mt-1 w-full rounded-md border-gray-200 bg-white text-sm text-gray-700 shadow-sm"
            />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <label
              for="Password"
              class="block text-sm font-medium text-gray-700"
            >
              Password
            </label>

            <input
              type="password"
              id="Password"
              name="password"
              class="mt-1 w-full rounded-md border-gray-200 bg-white text-sm text-gray-700 shadow-sm"
            />
          </div>

          <div class="col-span-6 sm:col-span-3">
            <label
              for="PasswordConfirmation"
              class="block text-sm font-medium text-gray-700"
            >
              Password Confirmation
            </label>

            <input
              type="password"
              id="PasswordConfirmation"
              name="password_confirmation"
              class="mt-1 w-full rounded-md border-gray-200 bg-white text-sm text-gray-700 shadow-sm"
            />
          </div>

          <div class="col-span-6">
            <label for="MarketingAccept" class="flex gap-4">
              <input
                type="checkbox"
                id="MarketingAccept"
                name="marketing_accept"
                class="h-5 w-5 rounded-md border-gray-200 bg-white shadow-sm"
              />

              <span class="text-sm text-gray-700">
                <p class="text-sm text-gray-500">
                  By creating an account, you agree to our
                  <a href="#" class="text-gray-700 underline">
                    terms and conditions
                  </a>
                  and
                  <a href="#" class="text-gray-700 underline">privacy policy</a>.
                </p>
              </span>
            </label>
          </div>

          <div class="col-span-6 sm:flex sm:items-center sm:gap-4">
            <button
              class="inline-block shrink-0 rounded-md border border-primary w-full md:w-56 bg-primary px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-primary focus:outline-none focus:ring active:text-primary-focus"
            >
              Create an account
            </button>

            <p class="mt-4 text-sm text-gray-500 sm:mt-0">
              Already have an account?
              <a href="/login" class="text-gray-700 underline">Log in</a>.
            </p>
          </div>
        </form>
        <div class="divider">OR</div>
        <div class="grid h-20 card rounded-box place-items-center gap-5 my-5">
          <button class="inline-block w-full shrink-0 rounded-md border border-red-500 bg-red-500 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-red-500 focus:outline-none focus:ring active:text-red-700">
            Connect with Google
          </button>
          <button class="inline-block w-full shrink-0 rounded-md border border-blue-500 bg-blue-500 px-12 py-3 text-sm font-medium text-white transition hover:bg-transparent hover:text-blue-500 focus:outline-none focus:ring active:text-blue-700">
            Connect with Facebook
          </button>

        </div>
      </div>
    </main>
  </div>
</section>

@include('partials.footer')
