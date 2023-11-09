<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="icon" href="{{asset('images/logo.png')}}" type="image/x-icon"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite('resources/css/app.css')
    <title>AA - Activity Report</title>
    <style>
      body {
        color: inherit;
        font-family:  DejaVu Sans, Arial, Helvetica, sans-serif;
        font-size: 14px;
        font-style: inherit;
        font-weight: inherit;
        line-height: inherit;
        background-color: #fff;
      }
      /* .p-10 {
          padding: 2.5rem;
      } */
      .w-full {
          width: 100%;
      }
      .flex {
          display: flex;
      }
      .flex-col {
          flex-direction: column;
      }
      .justify-center {
          justify-content: center;
      }
      .gap-5 {
          gap: 1.25rem;
      }
      .text-sm {
          font-size: 0.80rem;
      }
      .text-lg {
          font-size: 1.125rem;
      }
      .font-bold {
          font-weight: 700;
      }
      .font-normal {
          font-weight: 400;
      }
      .overflow-x-auto {
          overflow-x: auto;
      }
      table {
          table-layout: auto;
          border-collapse: collapse;
          border-spacing: 0;
          width: 100%;
      }
      th,
      td {
          text-align: left;
          padding: 0.75rem;
          border: 1px solid #e2e8f0;
      }
      .hover:hover {
          background-color: #f9fafb;
      }
      .text-center {
          text-align: center;
      }
  </style>
  @php
      $roleDef = [];
      foreach (request('roles') ?? [] as $value) {
        $roleDef[] =  $roles[$value];
      }
  @endphp
  
</head>
<body class="bg-white">
  @stack('top')
    <section class="p-10 w-full flex flex-col justify-center gap-5">
        <h1 style="margin-bottom: 3%" class="w-full text-center">Activity Log</h1>

        {{-- <div class="text-lg font-bold">Name: </div> --}}
        <div style="margin-bottom: 20px">
            @if(request()->has('name'))
                <div class="text-sm font-bold" style="margin-bottom: 3px">Name: <span class="font-normal">{{request('name')}}</span> </div>
            @endif
            @if(request()->has('roles'))
                <div class="text-sm font-bold" style="margin-bottom: 3px">{{$roleDef > 1 ? 'Roles:' : 'Role:'}} <span class="font-normal">{{implode(', ', $roleDef)}}</span> </div>
            @endif
            @isset($dateRange) 
                @if(isset($dateRange['now']))
                  <div class="text-sm font-bold">Date: <span class="font-normal">{{ $dateRange['now']}}</span> </div>
                @elseif(isset($dateRange['start_date']) && isset($dateRange['end_date']))
                  <div class="text-sm font-bold">Date: <span class="font-normal">{{ $dateRange['start_date']}} to {{$dateRange['end_date']}}</span> </div>
                @endif
            @endisset 
        </div>
        <div class="overflow-x-auto w-full">
            <table class="table w-full ">
              <!-- head -->
              <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Action</th>
                <th>Module</th>
                <th>Date</th>
              </tr>
              </thead>
              <tbody>
              <!-- row  -->
              @forelse ($activities as $item)
                <tr class="hover">
                  <td>{{(isset($item->name) ? $item->name : $item->employee->name() ) ?? 'None'}}</td>
                  <td>{{$item->role()}}</td>
                  <td>{{$item->action ?? 'None'}}</td>
                  <td>{{$item->module ?? 'None'}}</td>
                  <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->format('F j, Y g:ia') ?? 'None'}}</td>
                  {{-- <th>
                    <a href="{{ route('system.setting.rooms.show', encrypt($item->id)) }}" class="link link-primary">More details</a>
                  </th> --}}
                </tr>
              @empty
                  <th colspan="5" class="text-center">No Record Found</th>
              @endforelse 
              </tbody>
            </table>
        </div>
    </section>
  @vite('resources/js/app.js')
  @stack('scripts')

</body>
</html>