<div x-data="invoiceSummary()" class="bg-slate-50 rounded-lg p-6 space-y-4">
    <h3 class="text-lg font-bold text-slate-900 mb-4">Invoice Summary</h3>
    
    <!-- Subtotal -->
    <div class="flex justify-between text-sm">
        <span class="text-slate-600">Subtotal</span>
        <span class="font-semibold text-slate-900" x-text="formatCurrency(subtotal)"></span>
    </div>

    <!-- VAT -->
    <div class="flex justify-between text-sm">
        <span class="text-slate-600">VAT (16%)</span>
        <span class="font-semibold text-slate-900" x-text="formatCurrency(vat)"></span>
    </div>

    <!-- Total Before Fee -->
    <div class="flex justify-between text-sm pt-2 border-t border-slate-200">
        <span class="text-slate-600">Total (Before Platform Fee)</span>
        <span class="font-semibold text-slate-900" x-text="formatCurrency(totalBeforeFee)"></span>
    </div>

    <!-- Platform Fee -->
    <div class="flex justify-between text-sm">
        <div class="flex items-center gap-1">
            <span class="text-slate-600">Platform Fee (3%)</span>
            <svg class="w-4 h-4 text-slate-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Platform fee supports service maintenance and payment processing">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <span class="font-semibold text-slate-900" x-text="formatCurrency(platformFee)"></span>
    </div>

    <!-- Grand Total -->
    <div class="flex justify-between pt-4 border-t-2 border-slate-300">
        <span class="text-lg font-bold text-slate-900">Grand Total</span>
        <span class="text-2xl font-bold text-blue-600" x-text="formatCurrency(grandTotal)"></span>
    </div>
</div>

<script>
function invoiceSummary() {
    return {
        items: [],
        subtotal: 0,
        vat: 0,
        totalBeforeFee: 0,
        platformFee: 0,
        grandTotal: 0,
        platformFeeRate: 0.03, // 3%
        vatRate: 0.16, // 16%
        
        init() {
            // Listen for items updates
            window.addEventListener('items-updated', (e) => {
                this.items = e.detail;
                this.calculateTotals();
            });
            
            // Initial calculation
            this.calculateTotals();
        },
        
        calculateTotals() {
            // Calculate base subtotal and VAT separately based on each item's VAT status
            let baseSubtotal = 0;
            let totalVat = 0;
            
            this.items.forEach((item) => {
                const itemTotal = item.total_price || 0;
                
                if (item.vat_included) {
                    // VAT is included in total_price
                    // Extract base price: total_price / (1 + vatRate)
                    const basePrice = itemTotal / (1 + this.vatRate);
                    const itemVat = itemTotal - basePrice;
                    
                    baseSubtotal += basePrice;
                    totalVat += itemVat;
                } else {
                    // VAT is excluded - total_price = base + VAT (already calculated in line-items-editor)
                    // Extract base: total_price / (1 + vatRate)
                    const basePrice = itemTotal / (1 + this.vatRate);
                    const itemVat = itemTotal - basePrice;
                    
                    baseSubtotal += basePrice;
                    totalVat += itemVat;
                }
            });
            
            // Subtotal is the sum of base prices (before VAT)
            this.subtotal = baseSubtotal;
            
            // VAT is the calculated VAT amount
            this.vat = totalVat;
            
            // Total before platform fee (base + VAT)
            this.totalBeforeFee = this.subtotal + this.vat;
            
            // Platform fee (3% of total before fee)
            this.platformFee = this.totalBeforeFee * this.platformFeeRate;
            
            // Grand total
            this.grandTotal = this.totalBeforeFee + this.platformFee;
            
            // Dispatch totals update
            this.$dispatch('totals-updated', {
                subtotal: this.subtotal,
                vat: this.vat,
                platformFee: this.platformFee,
                grandTotal: this.grandTotal
            });
        },
        
        formatCurrency(amount) {
            return 'KES ' + new Intl.NumberFormat('en-KE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount || 0);
        }
    }
}
</script>

