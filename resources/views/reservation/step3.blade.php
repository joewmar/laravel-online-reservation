
<x-landing-layout>
  <section class="m-5">
  <div class="flex justify-center item- pb-10 text-center ">
    <ul class="w-full steps steps-horizontal">
      <li data-content="✓" class="step step-primary">Dates</li>
      <li data-content="✓" class="step step-primary">Tour Menu</li>
      <li class="step step-primary">Your Details</li>
      <li class="step">Confirmation</li>
    </ul>
  </div>

  <div>
    <div class="grid grid-cols-1 gap-x-16 gap-y-8 lg:grid-cols-5 m-10">
      <div class="lg:col-span-2 lg:py-12">
        <h1 class="max-w-xl font-bold uppercase text-3xl">
          Fill the Personal information
        </h1>
        <div class="mt-8">
            Note: Don't worry this information will confidentials!
        </div>
      </div>
  
      <div class="rounded-lg bg-white p-8 shadow-xl lg:col-span-3 lg:p-12">
        <form action="{{ route('reservation.details.store')}}" method="POST" class="space-y-4">
          @csrf
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 my-3">
            <x-input type="text" id="first_name" name="first_name" placeholder="First Name" value="{{auth('web')->user()->first_name ?? ''}}" /> 
            <x-input type="text" id="last_name" name="last_name" placeholder="Last Name" value="{{auth('web')->user()->last_name ?? ''}}" /> 
            <x-input type="text" id="age" name="age" placeholder="Your Age" value="{{auth('web')->user()->age() ?? ''}}" /> 
            <x-select id="country" name="country" placeholder="Your Country" :value="$countries" :title="$countries" selected="{{auth('web')->user()->country ?? ''}}" />
            <x-select id="nationality" name="nationality" placeholder="Your Nationality" :value="$nationality" :title="$nationality" selected="{{auth('web')->user()->nationality ?? ''}}" />
            <x-input type="number" id="contact" name="contact" placeholder="Your Phone Number" value="{{auth('web')->user()->contact ?? ''}}" /> 
            <div class="col-span-2">
              <x-input type="email" id="email" name="email" placeholder="Your Email Address" value="{{auth('web')->user()->email ?? ''}}" /> 

            </div>
            <br>
            <div class="mt-4 flex justify-end gap-5">
              <a href="{{URL::previous()}}" class=" btn btn-ghost gap-2">
                <i class="fa-solid fa-arrow-left"></i>            
                Back
              </a>
              <button type="submit" class="btn btn-primary gap-2">
                Next
                <i class="fa-solid fa-arrow-right"></i>            
              </button>
            </div>
  
        </form>
      </div>
    </div>
  </div>
</section>

</x-landing-layout>