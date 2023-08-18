<x-landing-layout>
    <section class="bg-gray-50">
        <div x-data="{loader = false}" class="flex items-center justify-center bg-primary h-screen" >
          <x-loader />
          <form action="{{route('reservation.feedback.store', $reservationID)}}" method="post">
            @csrf
            <div class="flex flex-col w-96 p-8 shadow-sm rounded-xl lg:p-12 bg-base-200">
                <div class="flex flex-col items-center w-full">
                    <h2 class="text-3xl font-semibold text-center">Story Time!</h2>
                    <div x-data="{words: ['Very Dissatisfied', 'Dissatisfied', 'Neutral', 'Satisfied', 'Very Satisfied'], rt: 3}" class="flex flex-col items-center py-6 space-y-3">
                        <span class="text-center">How was your experience?</span>
                        <div class="rating">
                            <input x-model="rt" type="radio" name="rating" class="mask mask-star-2 bg-orange-400" value="1" />
                            <input x-model="rt" type="radio" name="rating" class="mask mask-star-2 bg-orange-400" value="2" />
                            <input x-model="rt" type="radio" name="rating" class="mask mask-star-2 bg-orange-400"  value="3" checked/>
                            <input x-model="rt" type="radio" name="rating" class="mask mask-star-2 bg-orange-400"  value="4" />
                            <input x-model="rt" type="radio" name="rating" class="mask mask-star-2 bg-orange-400"  value="5" />
                        </div>
                        <div class="flex justify-center">
                            <p class="text-sm" x-text="words[rt-1]"></p>
                        </div>
                    </div>
                    <div class="flex flex-col w-full">
                        <textarea rows="3" placeholder="Message..." name="message" class="textarea textarea-primary"></textarea>
                        <button class="py-4 my-8 font-semibold rounded-md btn btn-primary"  @click="loader = true">Leave feedback</button>
                    </div>
                </div>
                <div class="flex items-center justify-center">
                    <a rel="noopener noreferrer" href="{{route('home')}}" class="text-sm text-neutral" @click="loader = true">Maybe later</a>
                </div>
            </div>
          </form>
        </div>
      </section>
</x-landing-layout>