<!-- resources/views/search-modal.blade.php -->
@props(['endpoint'])
<div x-data="searchRList">
    <div x-data="{ isOpen: false, query: '' }">
        <!-- Button to open the modal -->
        <button @click="isOpen = true" class="btn btn-circle btn-ghost hover:btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        
        <!-- Modal -->
        <div x-show="isOpen" @keydown.escape.window="isOpen = false" class="py-5 fixed inset-0 flex flex-col items-center justify-start z-[100] h-screen w-full bg-neutral bg-opacity-70">
            <input x-model="query" @input.debounce.300ms="search()" type="search" class="w-1/2 p-2 mb-4 input input-primary input-bordered" placeholder="Search Full Name">
            <template x-if="results.length !== 0" >                        
                <!-- Search results -->
                <ul class="overflow-y-auto menu bg-base-100 w-1/2 rounded">
                    <template x-for="result in results" :key="result.key">
                        <li>
                            <a :href="result.link" x-text="result.title"></a>
                        </li>
                    </template>
                </ul>
            </template>
            <!-- Close button -->
            <button @click="isOpen = false" class="absolute right-5 top-5 btn btn-ghost btn-circle text-base-100 hover:btn-primary">
                <i class="fa-solid fa-x"></i>            
            </button>
        </div>
    </div>
</div>
@push('scripts')
	<script>
		document.addEventListener('alpine:init', () => {
			Alpine.data('searchRList', () => ({
				isOpen: false,
				query: '',
				results: [],
				search() {
					axios.get('{{$endpoint}}', { params: { query: this.query } })
						.then(response => {
							this.results = response.data;
                            console.log(this.results);
						})
						.catch(error => {
							console.error(error);
						});
				},
			}));
		});

	</script>
@endpush