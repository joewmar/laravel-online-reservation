@props(['link', 'icon', 'title', 'description'])

<a href="{{$link}}">
    <div class="card w-80 bg-base-100 shadow-xl hover:bg-primary hover:text-white">
        <figure class="px-10 pt-10">
            <i class="{{$icon}} text-7xl"></i>
        </figure>
        <div class="card-body items-center text-center">
        <h2 class="card-title">{{$title}}</h2>
        <p>{{$description}}</p>
        </div>
    </div>
</a>