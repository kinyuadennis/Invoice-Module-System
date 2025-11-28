<div x-data="{ currentStep: 1 }" x-init="currentStep = $parent.currentStep || 1; $watch('$parent.currentStep', val => currentStep = val)" class="mb-8">
    <div class="flex items-center justify-between">
        <template x-for="i in 6" :key="i">
            <div class="flex items-center flex-1">
                <!-- Step Circle -->
                <div class="flex flex-col items-center flex-1">
                    <div 
                        :class="{
                            'bg-emerald-600 border-emerald-600 text-white': i < currentStep,
                            'bg-emerald-600 border-emerald-600 text-white ring-4 ring-emerald-100': i === currentStep,
                            'bg-white border-gray-300 text-gray-400': i > currentStep
                        }"
                        class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300"
                    >
                        <template x-if="i < currentStep">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </template>
                        <template x-if="i >= currentStep">
                            <span class="text-sm font-bold" x-text="i"></span>
                        </template>
                    </div>
                    <!-- Step Label (hidden on mobile) -->
                    <span 
                        :class="i <= currentStep ? 'text-emerald-600' : 'text-gray-400'"
                        class="mt-2 text-xs font-medium text-gray-600 hidden sm:block"
                    >
                        Step <span x-text="i"></span>
                    </span>
                </div>
                
                <!-- Connector Line -->
                <template x-if="i < 6">
                    <div 
                        :class="i < currentStep ? 'bg-emerald-600' : 'bg-gray-300'"
                        class="flex-1 h-0.5 mx-2 transition-colors duration-300"
                    ></div>
                </template>
            </div>
        </template>
    </div>
</div>

