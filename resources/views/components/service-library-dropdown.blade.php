@props(['services'])

<div x-data="serviceLibrary({{ json_encode($services) }})" class="relative">
    <button
        type="button"
        @click="showDropdown = !showDropdown"
        class="w-full flex items-center justify-between px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500"
    >
        <span>Add from Service Library</span>
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown -->
    <div 
        x-show="showDropdown"
        @click.away="showDropdown = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        class="absolute z-10 mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"
        style="display: none;"
    >
        <!-- Search -->
        <div class="p-2 border-b border-gray-200">
            <input 
                type="text" 
                x-model="searchQuery"
                @input="filterServices()"
                placeholder="Search services..."
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
            >
        </div>

        <!-- Service List -->
        <div class="py-1">
            <template x-for="service in filteredServices" :key="service.name">
                <button
                    type="button"
                    @click="addService(service)"
                    class="w-full text-left px-4 py-2 hover:bg-gray-50 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900" x-text="service.name"></span>
                        <span class="text-sm text-gray-600" x-text="'KES ' + formatNumber(service.price)"></span>
                    </div>
                </button>
            </template>
            
            <button
                type="button"
                @click="addCustomService()"
                class="w-full text-left px-4 py-2 hover:bg-gray-50 transition-colors border-t border-gray-200 mt-1"
            >
                <span class="text-sm font-medium text-emerald-600">+ Add Custom Service</span>
            </button>
        </div>
    </div>
</div>

<script>
function serviceLibrary(services) {
    return {
        services: Object.entries(services).map(([name, price]) => ({ name, price })),
        filteredServices: [],
        searchQuery: '',
        showDropdown: false,
        
        init() {
            this.filteredServices = this.services;
        },
        
        filterServices() {
            if (!this.searchQuery) {
                this.filteredServices = this.services;
                return;
            }
            
            const query = this.searchQuery.toLowerCase();
            this.filteredServices = this.services.filter(service => 
                service.name.toLowerCase().includes(query)
            );
        },
        
        addService(service) {
            this.$dispatch('service-selected', {
                description: service.name,
                unit_price: service.price,
                quantity: 1,
                vat_included: true
            });
            this.showDropdown = false;
            this.searchQuery = '';
        },
        
        addCustomService() {
            this.$dispatch('add-custom-item');
            this.showDropdown = false;
        },
        
        formatNumber(num) {
            return num.toLocaleString('en-KE');
        }
    }
}
</script>

