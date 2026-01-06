@props(['templates', 'selectedTemplate', 'company'])

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" 
         x-data="invoiceTemplateSelector({{ $selectedTemplate ? $selectedTemplate->id : 'null' }})">
        
        @foreach($templates as $template)
            <div 
                class="group relative border-2 rounded-xl p-5 cursor-pointer transition-all duration-200 hover:shadow-xl hover:-translate-y-1 bg-white"
                :class="selectedTemplateId === {{ $template->id }} 
                    ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 shadow-lg ring-2 ring-blue-200' 
                    : 'border-gray-200 hover:border-blue-300'"
                @click="selectTemplate({{ $template->id }}, '{{ route('user.company.update-invoice-template') }}')"
            >
                <!-- Selected Badge -->
                <div 
                    x-show="selectedTemplateId === {{ $template->id }}"
                    class="absolute top-3 right-3 bg-blue-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full shadow-md flex items-center gap-1.5 z-10"
                    x-cloak
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Selected
                </div>

                <!-- Preview Image -->
                <div class="mb-4">
                    @if($template->preview_image_url)
                        <img 
                            src="{{ $template->preview_image_url }}" 
                            alt="{{ $template->name }}"
                            class="w-full h-40 object-cover rounded-lg border border-gray-200 shadow-sm group-hover:shadow-md transition-shadow"
                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'200\'%3E%3Crect fill=\'%23f3f4f6\' width=\'300\' height=\'200\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'14\'%3ENo Preview%3C/text%3E%3C/svg%3E'"
                        >
                    @else
                        <div class="w-full h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg border border-gray-200 flex items-center justify-center group-hover:from-gray-200 group-hover:to-gray-300 transition-colors">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                                </svg>
                                <span class="text-gray-500 text-sm font-medium">No Preview</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Template Info -->
                <div>
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            {{ $template->name }}
                        </h3>
                        @if($template->is_default)
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-md font-semibold flex items-center gap-1 flex-shrink-0">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Default
                            </span>
                        @endif
                    </div>
                    @if($template->description)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                            {{ Str::limit($template->description, 100) }}
                        </p>
                    @endif
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                            <span class="text-xs text-gray-500">
                                Prefix: <strong class="text-gray-700 dark:text-gray-200 font-semibold">{{ $template->prefix }}</strong>
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 group-hover:text-blue-600 transition-colors">
                            Click to select →
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Loading Indicator -->
    <div x-show="processing" class="text-center py-6 bg-blue-50 rounded-xl border-2 border-blue-200" x-cloak>
        <div class="inline-flex items-center gap-3 text-blue-600">
            <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="font-medium">Updating template...</span>
        </div>
    </div>
</div>

<script>
function invoiceTemplateSelector(initialTemplateId) {
    return {
        selectedTemplateId: initialTemplateId,
        processing: false,

        async selectTemplate(templateId, updateUrl) {
            if (this.processing || this.selectedTemplateId === templateId) {
                // If same template, just show preview
                this.loadPreview(templateId);
                return;
            }

            // Validate URL
            if (!updateUrl || updateUrl === '' || updateUrl === 'undefined') {
                console.error('Update URL is missing or invalid:', updateUrl);
                alert('Error: Unable to update template. Please refresh the page and try again.');
                return;
            }

            this.processing = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                if (!csrfToken) {
                    throw new Error('CSRF token not found. Please refresh the page.');
                }

                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        invoice_template_id: templateId,
                    }),
                });

                if (!response.ok) {
                    let errorData;
                    try {
                        const errorText = await response.text();
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { error: `Server error: ${response.status} ${response.statusText}` };
                    }
                    throw new Error(errorData.error || errorData.message || `Server error: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.selectedTemplateId = templateId;
                    
                    // Load preview after successful update
                    this.loadPreview(templateId);
                    
                    // Show success message
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('notify', {
                            detail: {
                                type: 'success',
                                message: data.message || 'Template updated successfully!',
                            }
                        }));
                    }
                } else {
                    throw new Error(data.error || data.message || 'Failed to update template');
                }
            } catch (error) {
                console.error('Error updating template:', error);
                alert(error.message || 'Failed to update template. Please try again.');
            } finally {
                this.processing = false;
            }
        },

        async loadPreview(templateId) {
            // Dispatch event to load preview
            if (window.dispatchEvent) {
                window.dispatchEvent(new CustomEvent('load-template-preview', {
                    detail: { templateId: templateId }
                }));
            }
        },
    };
}
</script>
