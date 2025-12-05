@props(['templates', 'selectedTemplate', 'company'])

<div class="space-y-3" 
     x-data="invoiceTemplateSelector({{ $selectedTemplate ? $selectedTemplate->id : 'null' }})">
    
    @foreach($templates as $template)
        <div 
            class="group relative border-2 rounded-lg p-3 cursor-pointer transition-all duration-200 hover:shadow-md"
            :class="selectedTemplateId === {{ $template->id }} 
                ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 shadow-md ring-1 ring-blue-200' 
                : 'border-gray-200 hover:border-blue-300 bg-white'"
            @click="selectTemplate({{ $template->id }}, '{{ route('user.company.update-invoice-template') }}')"
        >
            <!-- Selected Badge -->
            <div 
                x-show="selectedTemplateId === {{ $template->id }}"
                class="absolute top-2 right-2 bg-blue-600 text-white text-xs font-semibold px-2 py-0.5 rounded-full shadow-sm"
                x-cloak
            >
                ✓
            </div>

            <!-- Template Info - Compact Layout -->
            <div class="flex items-start gap-3">
                <!-- Preview Image - Smaller -->
                <div class="flex-shrink-0">
                    @if($template->preview_image_url)
                        <img 
                            src="{{ $template->preview_image_url }}" 
                            alt="{{ $template->name }}"
                            class="w-16 h-16 object-cover rounded border border-gray-200"
                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'64\'%3E%3Crect fill=\'%23f3f4f6\' width=\'64\' height=\'64\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'10\'%3ENo Preview%3C/text%3E%3C/svg%3E'"
                        >
                    @else
                        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-200 rounded border border-gray-200 flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Template Details -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <h3 class="font-semibold text-gray-900 text-sm group-hover:text-blue-600 transition-colors truncate">
                            {{ $template->name }}
                        </h3>
                        @if($template->is_default)
                            <span class="text-xs bg-green-100 text-green-800 px-1.5 py-0.5 rounded flex-shrink-0">
                                Default
                            </span>
                        @endif
                    </div>
                    @if($template->description)
                        <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                            {{ Str::limit($template->description, 60) }}
                        </p>
                    @endif
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                        </svg>
                        <span>Prefix: <strong class="text-gray-700">{{ $template->prefix }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Loading Indicator -->
    <div x-show="processing" class="text-center py-3 bg-blue-50 rounded-lg border border-blue-200" x-cloak>
        <div class="inline-flex items-center gap-2 text-blue-600 text-sm">
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Updating...</span>
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

