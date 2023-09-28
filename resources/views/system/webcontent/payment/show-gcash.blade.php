<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Gcash of {{$gcash[$key]['name']}}">
      <div class="mt-8 w-full flex flex-col md:flex-row justify-evenly space-y-10 items-center">
        <div class="md:w-96 flex flex-col justify-center items-start">
          <div class="avatar">
            <div class="w-full p-3 border-2 border-dashed rounded-md border-primary text-neutral">
                <img src="{{isset($gcash[$key]['qrcode']) ? route('private.image', ['folder' => explode('/', $gcash[$key]['qrcode'])[0], 'filename' => explode('/', $gcash[$key]['qrcode'])[1]]) : asset('images/logo.png')}}" alt="QR Code of {{$gcash[$key]['name']}}">
            </div>
          </div>
        </div>
        <div class="w-full md:w-96">
          <article class="prose">
            <p class="text-lg"><span class="font-bold">Gcash Number:</span> {{$gcash[$key]['number'] === null ? 'None': $gcash[$key]['number']}}</p>
          </article>
           <div class="flex justify-between w-full space-x-3 my-5">
              <div class="w-full">
                <a href="{{ route('system.webcontent.edit.payment.gcash', encrypt($key)) }}" class="btn btn-primary w-full">Edit</a>
              </div class="w-full">
              <div class="w-full">
                <label for="delete_modal" class="btn btn-outline btn-error w-full">Delete</label>
              </div>
           </div>
           <form id="delete-form" method="POST" action=" {{ route('system.webcontent.destroy.payment.gcash', encrypt($key)) }}">
            @csrf
            @method('DELETE')
            <x-passcode-modal title="Do you want remove this reference: {{$gcash[$key]['name']}}" id="delete_modal" formId="delete-form"  />
          </form>
        </div>
      </div>
    </x-system-content>
</x-system-layout>