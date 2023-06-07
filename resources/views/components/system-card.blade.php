@props(['icon' ,'title', 'description'])

<article class="flex items-center justify-between rounded-lg border border-gray-100 bg-white p-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
    <div class="flex items-center gap-4">
      <span class="hidden rounded-full bg-gray-100 p-2 text-gray-600 sm:block">
        <i class="{{$icon}}"></i>
      </span>

      <div>
        <p class="text-sm text-gray-500">{{$title}}</p>
        <p class="text-xl md:text-2xl font-medium text-neutral">{{$description}}</p>
      </div>
    </div>
  </article>