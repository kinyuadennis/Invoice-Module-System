@props(['clients', 'selectedClient' => null])

<div x-data="clientSelector({{ json_encode($clients) }})">
    <div class="space-y-4">
        <!-- Search/Filter -->
        <div>
            <label class="block text-sm font-semibold text-gray-900 mb-2">Select Client</label>
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery"
                    @input="filterClients()"
                    placeholder="Search clients..."
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                >
                <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <!-- Client List -->
        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
            <template x-if="filteredClients.length === 0">
                <div class="p-4 text-center text-gray-500 text-sm">
                    No clients found. <button @click="$dispatch('open-client-modal')" class="text-emerald-600 font-semibold hover:text-emerald-700">Add a new client</button>
                </div>
            </template>
            
            <template x-for="client in filteredClients" :key="client.id">
                <button
                    type="button"
                    @click="selectClient(client)"
                    :class="selectedClientId === client.id ? 'bg-emerald-50 border-emerald-500' : 'bg-white border-gray-200 hover:bg-gray-50'"
                    class="w-full text-left p-4 border-l-4 transition-colors"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900" x-text="client.name"></p>
                            <p class="text-sm text-gray-600 mt-1" x-text="client.email || 'No email'"></p>
                            <p class="text-xs text-gray-500 mt-1" x-text="client.phone || ''"></p>
                        </div>
                        <svg 
                            x-show="selectedClientId === client.id"
                            class="w-5 h-5 text-emerald-600" 
                            fill="currentColor" 
                            viewBox="0 0 20 20"
                        >
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>
            </template>
        </div>

        <!-- Add New Client Button -->
        <button
            type="button"
            @click="$dispatch('open-client-modal')"
            class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-700 hover:border-emerald-500 hover:text-emerald-600 transition-colors"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New Client
        </button>

        <!-- Selected Client Display -->
        <div x-show="selectedClient" class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
            <p class="text-sm font-semibold text-emerald-900">Selected Client:</p>
            <p class="text-sm text-emerald-700 mt-1" x-text="selectedClient?.name"></p>
        </div>
    </div>
</div>

<script>
function clientSelector(clients) {
    return {
        clients: clients,
        filteredClients: clients,
        searchQuery: '',
        selectedClientId: null,
        selectedClient: null,
        
        init() {
            this.filteredClients = this.clients;
        },
        
        filterClients() {
            if (!this.searchQuery) {
                this.filteredClients = this.clients;
                return;
            }
            
            const query = this.searchQuery.toLowerCase();
            this.filteredClients = this.clients.filter(client => 
                client.name.toLowerCase().includes(query) ||
                (client.email && client.email.toLowerCase().includes(query)) ||
                (client.phone && client.phone.includes(query))
            );
        },
        
        selectClient(client) {
            this.selectedClientId = client.id;
            this.selectedClient = client;
            this.$dispatch('client-selected', client);
        }
    }
}
</script>

