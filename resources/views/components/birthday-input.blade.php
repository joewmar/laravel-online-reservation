@props(['name' => 'birthday', 'id' => 'birthday', 'disabled' => false, 'value' => old('birthday')])
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    <label for="{{$id}}" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror">
        <div x-data="{ months: [], days: [], years: [], selectedMonth: '{{!empty($value) ? explode('-',$value)[1] : ''}}', selectedDay: '{{!empty($value) ? explode('-',$value)[2] : ''}}', selectedYear: '{{!empty($value) ? explode('-',$value)[0] : ''}}', bdy: '{{$value}}' }" 
        x-init=" 
            months = {
            '1': 'January', 
            '2': 'February', 
            '3': 'March', 
            '4': 'April', 
            '5': 'May', 
            '6': 'June',
            '7': 'July',
            '8': 'August', 
            '9': 'September', 
            '10': 'October', 
            '11': 'November', 
            '12': 'December'
        };
        selectedbdy = bdy.split('-');
        days = Array.from({ length: 31 }, (_, i) => i + 1);
        years = Array.from({ length: 174 }, (_, i) => 2023 - i);" 
        class="grid grid-cols-3 gap-1 w-full">
        <select x-model="selectedMonth" class="border-transparent focus:border-transparent focus:ring-0 font-normal rounded-md select text-xs md:text-sm" {{$disabled ? 'disabled' : ''}}>
            <option value="" disabled selected>Month</option>
            <template x-for="(monthName, monthValue) in months" :key="monthValue">
                <option x-bind:value="monthValue > 10 ? monthValue : 0 + monthValue " x-text="monthName" :selected="(monthValue > 10 ? monthValue : 0 + monthValue ) === selectedbdy[1]"></option>
            </template>
        </select>
    
        <select x-model="selectedDay" class="border-transparent focus:border-transparent focus:ring-0 font-normal rounded-md select text-xs md:text-sm" {{$disabled ? 'disabled' : ''}}>
            <option value="" disabled selected>Day</option>
            <template x-for="(day, index) in days" :key="index">
                <option x-bind:value="day" x-text="day" :selected="day == selectedbdy[2]"></option>
            </template>
        </select>
    
        <select x-model="selectedYear" class="border-transparent focus:border-transparent focus:ring-0 font-normal rounded-md select text-xs md:text-sm" {{$disabled ? 'disabled' : ''}}>
            <option value="" disabled selected>Year</option>
            <template x-for="(year, index) in years" :key="index">
                <option x-bind:value="year" x-text="year" :selected="year == selectedbdy[0]"></option>
            </template>
        </select>
        <input type="hidden" name="{{$name}}" id="{{$id}}" :value="`${selectedYear}-${selectedMonth}-${selectedDay}`">
    </div>
        <span id="{{$id}}" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
            Birthday
        </span>
    </label>
    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="mb-5 label-text-alt text-error">{{$message}}</span>
            @enderror
        </span>
    </label>
</div>
