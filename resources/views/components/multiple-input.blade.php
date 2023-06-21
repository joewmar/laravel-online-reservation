@props(['type' => '', 'value' => '', 'id' => '', 'name' => '', 'placeholder' => '', 'value' => ''])
<div class="form-control w-full">
    <label class="label">
      <span class="label-text">{{$placeholder}}s</span>
      <span class="label-text-alt">
      </span>
    </label>
    <div class="flex justify-evenly">
        <x-input type="{{$type}}" id="{{$name}}1" name="{{$name}}1" placeholder="{{$placeholder}} 1"/>
        <input type="hidden" name="{{$name}}" id="{{$id}}">
        <button type="button" id="{{$id}}Add" class="btn btn-ghost">
            <i class="fa-solid fa-plus text-primary"></i>                
        </button>
    </div>
    <div id="{{$id}}-inputs">
    </div>
</div> 

<script>
    let {{$id}}Add = document.getElementById("{{$id}}Add");
    let {{$id}}_inputs = document.getElementById("{{$id}}-inputs");
    let count = 2;
    let {{$id}}_txt = "";

    {{$id}}Add.addEventListener('click', function () {
        {{$id}}_txt += 
        `
            <div id="{{$id}}_input${count}" class="flex justify-evenly" >
                <x-input type="{{$type}}" id="{{$id}}_${count}" name="{{$id}}${count}" placeholder="{{$placeholder}} ${count}" />
                <button onclick="deletePrices(${count})" type="button" class="btn btn-ghost">
                    <i class="fa-solid fa-xmark"></i>                
                </button>
            </div>   
        `;
        {{$id}}_inputs.innerHTML = {{$id}}_txt;
        count++;


    });

    function deletePrices(id) {
        const {{$id}}_input =  document.getElementById(`{{$id}}_input${id}`);
        while ({{$id}}_input.hasChildNodes()) {
            {{$id}}_input.removeChild({{$id}}_input.firstChild);
        }
        count = count; 

    };

</script>