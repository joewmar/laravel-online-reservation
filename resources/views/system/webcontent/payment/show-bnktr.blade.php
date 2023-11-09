<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Bank Transfer of {{$bankTransfer[$key]['name']}}" back="{{route('system.webcontent.home','#payment')}}">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="w-full md:w-96">
          <article class="prose">
            <p class="text-lg"><span class="font-bold">Account Number:</span> {{$bankTransfer[$key]['acc_no'] === null ? 'None': $bankTransfer[$key]['acc_no']}}</p>
            <p class="text-lg"><span class="font-bold">Contact Number:</span> {{$bankTransfer[$key]['contact'] === null ? 'None': $bankTransfer[$key]['contact']}}</p>
          </article>
           <div class="flex justify-between w-full space-x-3 my-5">
              <div class="w-full">
                <a href="{{ route('system.webcontent.edit.payment.bnktr', encrypt($key)) }}" class="btn btn-primary w-full">Edit</a>
              </div class="w-full">
              <div class="w-full">
                <label for="delete_modal" class="btn btn-outline btn-error w-full">Delete</label>
              </div>
           </div>
           <form id="delete-form" method="POST" action=" {{ route('system.webcontent.destroy.payment.bnktr', encrypt($key)) }}">
            @csrf
            @method('DELETE')
            <x-passcode-modal title="Do you want remove this reference: {{$bankTransfer[$key]['name']}}" id="delete_modal" formId="delete-form"  />
          </form>
        </div>
      </div>
    </x-system-content>
</x-system-layout>