<x-landing-layout noFooter>
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
                        <div class="flex space-x-3">
                            <input x-model="rt" id="rating1" type="radio" name="rating" class="peer hidden" value="1" />
                            <label for="rating1" :title="words[0]" :aria-label="words[0]" class="cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-10 h-10" :class="rt >= 1 ? 'text-orange-500' : 'text-gray-600' ">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </label>
                            <input x-model="rt" id="rating2" type="radio" name="rating" class="peer hidden" value="2" />
                            <label for="rating2" :title="words[1]" :aria-label="words[1]" class="cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-10 h-10 " :class="rt >= 2 ? 'text-orange-500' : 'text-gray-600' ">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </label>
                            <input x-model="rt" id="rating3" type="radio" name="rating" class="peer hidden"  value="3" checked/>
                            <label for="rating3" :title="words[2]" :aria-label="words[2]" class="cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-10 h-10 " :class="rt >= 3 ? 'text-orange-500' : 'text-gray-600' ">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </label>
                            <input x-model="rt" id="rating4" type="radio" name="rating" class="peer hidden"  value="4" />
                            <label for="rating4" :title="words[3]" :aria-label="words[3]" class="cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-10 h-10 " :class="rt >= 4 ? 'text-orange-500' : 'text-gray-600' ">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </label>
                            <input x-model="rt" id="rating5" type="radio" name="rating" class="peer hidden"  value="5" />
                            <label for="rating5" :title="words[4]" :aria-label="words[4]" class="cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-10 h-10 " :class="rt >= 5 ? 'text-orange-500' : 'text-gray-600' ">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            </label>
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