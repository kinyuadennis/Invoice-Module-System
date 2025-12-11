@php
    // Get logo URL - handle both preview (web) and PDF (file system) contexts
    $logoUrl = null;
    $logoPath = null;
    if (isset($invoice['is_preview']) && $invoice['is_preview']) {
        // Browser preview - use web URL
        if (isset($invoice['company']['logo']) && $invoice['company']['logo']) {
            $logoUrl = $invoice['company']['logo'];
        }
    } else {
        // PDF generation - use pre-validated absolute file path from controller
        // This avoids blocking file_exists() calls in the PHP script
        $logoPath = $invoice['company']['logo_path'] ?? null;
        // Skip remote URLs to prevent timeouts
        if ($logoPath && (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://'))) {
            $logoPath = null;
        }
    }
    
    // Prepare header data for PHP script
    $companyName = addslashes($invoice['company']['name'] ?? 'InvoiceHub');
    $companyAddress = addslashes($invoice['company']['address'] ?? '');
    $companyPhone = addslashes($invoice['company']['phone'] ?? '');
    $companyEmail = addslashes($invoice['company']['email'] ?? '');
    $companyWebsite = addslashes($invoice['company']['website'] ?? '');
    $invoiceNumber = addslashes($invoice['invoice_number'] ?? 'INV-' . ($invoice['id'] ?? ''));
    $issueDate = addslashes($invoice['issue_date'] ?? $invoice['date'] ?? date('Y-m-d'));
    $dueDate = addslashes($invoice['due_date'] ?? '');
    $status = addslashes($invoice['status'] ?? 'draft');
    $currency = addslashes($invoice['company']['currency'] ?? 'KES');
    // Only pass logo path if it's a valid local file (pre-validated in controller)
    $logoPathForScript = ($logoPath && !isset($invoice['is_preview'])) ? addslashes($logoPath) : '';
@endphp

<script type="text/php">
    if (isset($pdf)) {
        // Header dimensions - 160px height for proper spacing
        $headerHeight = 160;
        // A4 portrait dimensions in points: 595.28 x 841.89
        $pageWidth = 595.28;
        $pageHeight = 841.89;
        
        // Get fonts - use exact font names to avoid errors
        try {
            $fontBold = $fontMetrics->get_font("DejaVu Sans", "bold");
            $fontNormal = $fontMetrics->get_font("DejaVu Sans", "normal");
        } catch (Exception $e) {
            // Fallback to default fonts if DejaVu Sans fails
            $fontBold = $fontMetrics->get_font("Helvetica", "bold");
            $fontNormal = $fontMetrics->get_font("Helvetica", "normal");
        }
        
        // Column positions
        $leftColX = 43; // 15mm = ~43 points
        $rightColX = $pageWidth - 43; // 15mm from right edge
        $currentY = 30; // Start 30px from top
        
        // LEFT COLUMN - Company Branding
        $logoX = $leftColX;
        $logoY = $currentY;
        $logoWidth = 0;
        $logoHeight = 0;
        $logoSpacing = 0;
        
        // Logo (if available) - use pre-validated path to avoid blocking file_exists()
        $logoPath = "{{ $logoPathForScript }}";
        if (!empty($logoPath)) {
            try {
                // Only try to load if path is provided (already validated in controller)
                $logoWidth = 60;
                $logoHeight = 60;
                $pdf->image($logoPath, $logoX, $logoY, $logoWidth, $logoHeight);
                $logoSpacing = 15;
            } catch (Exception $e) {
                // Silently fail if image can't be loaded
                $logoWidth = 0;
                $logoSpacing = 0;
            }
        }
        
        // Company name (next to or below logo)
        $textX = $leftColX + $logoWidth + $logoSpacing;
        $companyName = "{{ $companyName }}";
        $pdf->text($textX, $currentY + 12, $companyName, $fontBold, 22, array(0.1, 0.1, 0.1));
        
        $lineY = $currentY + 35; // Start company details below logo/name
        
        // Company address
        $companyAddress = "{{ $companyAddress }}";
        if (!empty($companyAddress)) {
            $pdf->text($textX, $lineY, $companyAddress, $fontNormal, 12, array(0.2, 0.2, 0.2));
            $lineY += 16;
        }
        
        // Company phone
        $companyPhone = "{{ $companyPhone }}";
        if (!empty($companyPhone)) {
            $pdf->text($textX, $lineY, $companyPhone, $fontNormal, 12, array(0.2, 0.2, 0.2));
            $lineY += 16;
        }
        
        // Company email
        $companyEmail = "{{ $companyEmail }}";
        if (!empty($companyEmail)) {
            $pdf->text($textX, $lineY, $companyEmail, $fontNormal, 12, array(0.2, 0.2, 0.2));
        }
        
        // RIGHT COLUMN - Invoice Meta (right-aligned)
        $rightY = $currentY + 10;
        
        // Invoice Number (24px, bold, blue)
        $invoiceNumber = "{{ $invoiceNumber }}";
        $numberWidth = $fontMetrics->get_text_width($invoiceNumber, $fontBold, 24);
        $pdf->text($rightColX - $numberWidth, $rightY, $invoiceNumber, $fontBold, 24, array(0.10, 0.45, 0.91)); // #1A73E8
        
        $rightY += 35;
        
        // Status Badge
        $status = "{{ $status }}";
        $statusText = ucfirst($status);
        $statusLower = strtolower($status);
        if ($statusLower === 'paid') {
            $statusColor = array(0.16, 0.65, 0.27); // Green
        } elseif ($statusLower === 'pending') {
            $statusColor = array(0.99, 0.49, 0.08); // Orange
        } elseif ($statusLower === 'overdue') {
            $statusColor = array(0.86, 0.21, 0.27); // Red
        } elseif ($statusLower === 'sent') {
            $statusColor = array(0.10, 0.45, 0.91); // Blue
        } else {
            $statusColor = array(0.53, 0.53, 0.53); // Gray
        }
        $statusWidth = $fontMetrics->get_text_width($statusText, $fontBold, 12);
        $pdf->text($rightColX - $statusWidth, $rightY, $statusText, $fontBold, 12, $statusColor);
        $rightY += 20;
        
        // Invoice Date
        $issueDate = "{{ $issueDate }}";
        if (!empty($issueDate)) {
            $dateLabel = "Invoice Date: ";
            $dateText = $issueDate;
            $labelWidth = $fontMetrics->get_text_width($dateLabel, $fontNormal, 12);
            $textWidth = $fontMetrics->get_text_width($dateText, $fontNormal, 12);
            $totalWidth = $labelWidth + $textWidth;
            $pdf->text($rightColX - $totalWidth, $rightY, $dateLabel, $fontNormal, 12, array(0.53, 0.53, 0.53));
            $pdf->text($rightColX - $textWidth, $rightY, $dateText, $fontNormal, 12, array(0.2, 0.2, 0.2));
            $rightY += 16;
        }
        
        // Due Date
        $dueDate = "{{ $dueDate }}";
        if (!empty($dueDate)) {
            $dueLabel = "Due Date: ";
            $dueText = $dueDate;
            $labelWidth = $fontMetrics->get_text_width($dueLabel, $fontNormal, 12);
            $textWidth = $fontMetrics->get_text_width($dueText, $fontNormal, 12);
            $totalWidth = $labelWidth + $textWidth;
            $pdf->text($rightColX - $totalWidth, $rightY, $dueLabel, $fontNormal, 12, array(0.53, 0.53, 0.53));
            $pdf->text($rightColX - $textWidth, $rightY, $dueText, $fontNormal, 12, array(0.2, 0.2, 0.2));
            $rightY += 16;
        }
        
        // Currency
        $currency = "{{ $currency }}";
        if (!empty($currency)) {
            $currencyWidth = $fontMetrics->get_text_width($currency, $fontNormal, 10);
            $pdf->text($rightColX - $currencyWidth, $rightY, $currency, $fontNormal, 10, array(0.53, 0.53, 0.53));
        }
        
        // Draw header border line at the bottom
        $pdf->line(43, $headerHeight, $pageWidth - 43, $headerHeight, array(0.88, 0.88, 0.88), 1);
    }
</script>

<!-- Preview header for browser preview only (completely hidden in PDF) -->
@if(isset($invoice['is_preview']) && $invoice['is_preview'])
<div style="display: block; margin-bottom: 30px; padding: 30px 43px; border-bottom: 1px solid #E0E0E0;">
    <div style="display: table; width: 100%; table-layout: fixed;">
        <!-- Left Column - Company Branding -->
        <div style="display: table-cell; width: 50%; vertical-align: top; padding-right: 30px;">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $invoice['company']['name'] ?? 'InvoiceHub' }}" style="max-width: 60px; max-height: 60px; margin-bottom: 10px;">
            @endif
            <div style="font-size: 22px; font-weight: bold; margin-bottom: 12px; color: #333;">{{ $invoice['company']['name'] ?? 'InvoiceHub' }}</div>
            @if(isset($invoice['company']['address']) && $invoice['company']['address'])
                <div style="font-size: 12px; color: #333; margin-bottom: 8px;">{{ $invoice['company']['address'] }}</div>
            @endif
            @if(isset($invoice['company']['phone']) && $invoice['company']['phone'])
                <div style="font-size: 12px; color: #333; margin-bottom: 8px;">{{ $invoice['company']['phone'] }}</div>
            @endif
            @if(isset($invoice['company']['email']) && $invoice['company']['email'])
                <div style="font-size: 12px; color: #333;">{{ $invoice['company']['email'] }}</div>
            @endif
        </div>
        
        <!-- Right Column - Invoice Meta -->
        <div style="display: table-cell; width: 50%; vertical-align: top; text-align: right;">
            <div style="font-size: 24px; font-weight: bold; color: #1A73E8; margin-bottom: 16px;">{{ $invoice['invoice_number'] ?? 'INV-' . ($invoice['id'] ?? '') }}</div>
            <div style="font-size: 12px; font-weight: bold; margin-bottom: 12px; color: 
                @if($invoice['status'] === 'paid') #28a745
                @elseif($invoice['status'] === 'pending') #fd7e14
                @elseif($invoice['status'] === 'overdue') #dc3545
                @elseif($invoice['status'] === 'sent') #1A73E8
                @else #888
                @endif">
                {{ ucfirst($invoice['status'] ?? 'draft') }}
            </div>
            @if(isset($invoice['issue_date']) || isset($invoice['date']))
                <div style="font-size: 12px; color: #333; margin-bottom: 8px;">
                    <span style="color: #888;">Invoice Date: </span>
                    <span style="color: #333;">{{ $invoice['issue_date'] ?? $invoice['date'] ?? '' }}</span>
                </div>
            @endif
            @if(isset($invoice['due_date']) && $invoice['due_date'])
                <div style="font-size: 12px; color: #333; margin-bottom: 8px;">
                    <span style="color: #888;">Due Date: </span>
                    <span style="color: #333;">{{ $invoice['due_date'] }}</span>
                </div>
            @endif
            @if(isset($invoice['company']['currency']) && $invoice['company']['currency'])
                <div style="font-size: 10px; color: #888;">{{ $invoice['company']['currency'] }}</div>
            @endif
        </div>
    </div>
</div>
@endif
