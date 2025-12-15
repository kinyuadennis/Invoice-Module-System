@php
    // PDF settings pre-resolved in controller (no DB queries here)
    // Controller passes show_software_credit in invoice['company']['show_software_credit']
    $showSoftwareCredit = $invoice['company']['show_software_credit'] ?? true;
    
    // Convert to string for PHP script
    $showCredit = $showSoftwareCredit ? '1' : '0';
    
    // Prepare footer data (all pre-resolved, no DB queries)
    $companyWebsite = addslashes($invoice['company']['website'] ?? '');
    $companyEmail = addslashes($invoice['company']['email'] ?? '');
    $companyAddress = addslashes($invoice['company']['address'] ?? '');
@endphp

<script type="text/php">
    if (isset($pdf)) {
        // Footer dimensions - 130px height for proper spacing
        $footerHeight = 130;
        // A4 portrait dimensions in points: 595.28 x 841.89
        $pageWidth = 595.28;
        $pageHeight = 841.89;
        $footerY = $pageHeight - $footerHeight;
        
        // Get fonts - use exact font names to avoid errors
        try {
            $fontNormal = $fontMetrics->get_font("DejaVu Sans", "normal");
            $fontBold = $fontMetrics->get_font("DejaVu Sans", "bold");
            $fontSmall = $fontMetrics->get_font("DejaVu Sans", "normal");
        } catch (Exception $e) {
            // Fallback to default fonts if DejaVu Sans fails
            $fontNormal = $fontMetrics->get_font("Helvetica", "normal");
            $fontBold = $fontMetrics->get_font("Helvetica", "bold");
            $fontSmall = $fontMetrics->get_font("Helvetica", "normal");
        }
        
        // Draw footer border line at the top
        $pdf->line(43, $footerY, $pageWidth - 43, $footerY, array(0.88, 0.88, 0.88), 1);
        
        $currentY = $footerY + 15; // Start content 15px from top of footer
        $centerX = $pageWidth / 2;
        
        // "Thank you for doing business with us." (12px, #888)
        $thankYouText = "Thank you for doing business with us.";
        $thankYouWidth = $fontMetrics->get_text_width($thankYouText, $fontNormal, 12);
        $pdf->text($centerX - ($thankYouWidth / 2), $currentY, $thankYouText, $fontNormal, 12, array(0.53, 0.53, 0.53));
        $currentY += 18;
        
        // Contact info (email, location) - small text (9px, #888)
        $contactInfo = [];
        $companyWebsite = "{{ $companyWebsite }}";
        $companyEmail = "{{ $companyEmail }}";
        $companyAddress = "{{ $companyAddress }}";
        
        if (!empty($companyEmail)) {
            $contactInfo[] = $companyEmail;
        }
        if (!empty($companyAddress)) {
            $contactInfo[] = $companyAddress;
        }
        
        if (!empty($contactInfo)) {
            $contactText = implode(" | ", $contactInfo);
            $contactWidth = $fontMetrics->get_text_width($contactText, $fontSmall, 9);
            $pdf->text($centerX - ($contactWidth / 2), $currentY, $contactText, $fontSmall, 9, array(0.53, 0.53, 0.53));
            $currentY += 15;
        }
        
        // Software credit (if enabled) - 9px, #888, with brand in blue
        $showCredit = {{ $showCredit }};
        if ($showCredit) {
            $beforeBrand = "Invoice prepared with ";
            $brandText = "InvoiceHub";
            $afterBrand = " — Professional invoicing for Kenyan businesses.";
            
            // Calculate widths for proper positioning
            $beforeWidth = $fontMetrics->get_text_width($beforeBrand, $fontSmall, 9);
            $brandWidth = $fontMetrics->get_text_width($brandText, $fontBold, 9);
            $afterWidth = $fontMetrics->get_text_width($afterBrand, $fontSmall, 9);
            $totalWidth = $beforeWidth + $brandWidth + $afterWidth;
            
            // Center the entire text block
            $startX = $centerX - ($totalWidth / 2);
            
            // Draw "Invoice prepared with " in gray
            $pdf->text($startX, $currentY, $beforeBrand, $fontSmall, 9, array(0.53, 0.53, 0.53));
            
            // Draw "InvoiceHub" in blue
            $brandX = $startX + $beforeWidth;
            $pdf->text($brandX, $currentY, $brandText, $fontBold, 9, array(0.10, 0.45, 0.91)); // #1A73E8
            
            // Draw " — Professional invoicing for Kenyan businesses." in gray
            $afterX = $brandX + $brandWidth;
            $pdf->text($afterX, $currentY, $afterBrand, $fontSmall, 9, array(0.53, 0.53, 0.53));
            
            $currentY += 15;
        }
        
        // Page numbers - bottom center (9px, #AAA)
        $pageText = "Page " . $PAGE_NUM . " of " . $PAGE_COUNT;
        $pageWidth_text = $fontMetrics->get_text_width($pageText, $fontSmall, 9);
        $pdf->text($centerX - ($pageWidth_text / 2), $currentY, $pageText, $fontSmall, 9, array(0.67, 0.67, 0.67));
    }
</script>

<!-- Footer for preview mode (only shown in browser preview) -->
@if(isset($invoice['is_preview']) && $invoice['is_preview'])
<div style="display: block; margin-top: 30px; padding: 20px 43px; border-top: 1px solid #E0E0E0; text-align: center;">
    <div style="font-size: 12px; color: #888; margin-bottom: 12px;">Thank you for doing business with us.</div>
    @php
        $contactInfo = [];
        if (isset($invoice['company']['email']) && $invoice['company']['email']) {
            $contactInfo[] = $invoice['company']['email'];
        }
        if (isset($invoice['company']['address']) && $invoice['company']['address']) {
            $contactInfo[] = $invoice['company']['address'];
        }
    @endphp
    @if(!empty($contactInfo))
        <div style="font-size: 9px; color: #888; margin-bottom: 12px;">{{ implode(' | ', $contactInfo) }}</div>
    @endif
    @if($showSoftwareCredit)
        <p style="font-size: 9px; color: #888; margin: 0 0 12px 0;">
            Invoice prepared with <span style="color: #1A73E8; font-weight: 600;">InvoiceHub</span> — Professional invoicing for Kenyan businesses.
        </p>
    @endif
    <div style="font-size: 9px; color: #AAA;">Page 1 of 1</div>
</div>
@endif
