<x-system-layout :activeSb="$activeSb">
    <x-system-content title="News">
        <section x-data="{loader: false}">
          <div class="flex justify-between items-center">
            <div class="tabs tabs-boxed bg-transparent my-5">
              <a @click="loader = true" href="{{route('system.news.home')}}" class="{{request()->has('tab') && request('tab') === 'announcement' ? 'tab' : 'tab tab-active'}}">News</a> 
              <a @click="loader = true" href="{{route('system.news.home', 'tab=announcement')}}" class="{{request()->has('tab') && request('tab') == 'announcement' ? 'tab tab-active' : 'tab'}}">Annoucement</a> 
            </div>
            @if(!request()->has('tab') && !(request('tab') === 'announcement'))
              <div class="flex justify-end space-x-4">
                <a href="{{route('system.news.create')}}" class="btn btn-primary">Create News</a>
              </div>
            @endif
            @if(request()->has('tab') && request('tab') === 'announcement')
              <div class="flex justify-end space-x-4">
                <a href="{{route('system.news.announcement.create')}}" class="btn btn-primary">Create Announcement</a>
              </div>
            @endif
          </div>
          <x-loader />
          <div class="overflow-x-auto w-full">
              <table class="table w-full">
                <!-- head -->
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Title</th>
                    <th>Deadline</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                @forelse ($news as $new)
                  <tr>
                    <td>{{$new->id}}</td>
                    <td>{{$new->title}}</td>
                    @if(isset($new->from) && isset($new->to))
                        <td>{{ Carbon\Carbon::createFromFormat('Y-m-d', $new->from)->format('M j, Y')}} - {{ Carbon\Carbon::createFromFormat('Y-m-d', $new->to)->format('M j, Y')}}</td>
                    @else
                        <td>Forever</td>
                    @endif
                    @if(request()->has('tab') && request('tab') === 'announcement')
                      <th><a href="{{route('system.news.announcement.show', encrypt($new->id))}}" class="link link-primary">More details</a></th>
                    @else
                      <th><a href="{{route('system.news.show', encrypt($new->id))}}" class="link link-primary">More details</a></th>
                    @endif
                  </tr>
                @empty
                    @if(request()->has('tab') && request('tab') === 'announcement')
                      <tr>
                        <td colspan="4" class="font-bold text-center">No Annoucement Record</td>
                      </tr>
                    @else
                      <tr>
                        <td colspan="4" class="font-bold text-center">No News Record</td>
                      </tr>
                    @endif
                @endforelse
                </tbody>
              </table>
          </div>
        </section>
    </x-system-content>
</x-system-layout>