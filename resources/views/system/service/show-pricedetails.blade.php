<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Do you want add more price details?">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <article class="prose flex justify-evenly items-start space-x-10">
          <form action="{{route('system.menu.replace', Arr::query(['rpid' => request('rpid')]))}}" method="post">
            @csrf
            <button class="btn btn-primary w-24">Yes</button>
            <a href="{{route('system.menu.home')}}" class="btn btn-ghost w-24">No</a>
          </form>
        </article>
      </div>
    </x-system-content>
  </x-system-layout>