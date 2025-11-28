# Invoice Hub Landing Page - Complete UX/UI Redesign Plan
## Kenyan Market Optimization & High-Conversion Strategy

---

## SECTION 1: HERO SECTION

### UX Improvements
- **Add live social proof counters** (animated numbers: 500+ → 523+ businesses)
- **Real-time invoice creation counter** ("12 invoices created today")
- **Trust badges row** (KRA compliant, Bank-level security, M-Pesa verified)
- **Urgency indicator** ("Join 47 businesses this week")
- **Value proposition clarity** (lead with outcome, not features)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  [Trust Badge] [KRA Badge] [Security Badge] [M-Pesa]   │
│                                                           │
│  Headline (H1)                                           │
│  Subheadline (H2)                                        │
│                                                           │
│  [Primary CTA] [Secondary CTA]                          │
│                                                           │
│  ✓ Free to create  ✓ 0.8% fee  ✓ M-Pesa  ✓ KRA Ready   │
│                                                           │
│  Live Stats: 523+ businesses | 12 invoices today        │
└─────────────────────────────────────────────────────────┘
         ┌──────────────────────────────┐
         │   [Invoice Preview Card]     │
         │   (Animated, Interactive)     │
         └──────────────────────────────┘
```

### Kenyan Localization
- **Currency**: Always KES (not KSh in hero - use full "KES")
- **M-Pesa badge**: Prominent green badge with Safaricom logo reference
- **KRA compliance badge**: "KRA eTIMS Ready" with checkmark
- **Local business names**: Use actual Kenyan business examples
- **Payment methods**: M-Pesa, Bank Transfer, Airtel Money icons
- **Location indicators**: "Nairobi", "Mombasa", "Kisumu" in testimonials

### High-Conversion Elements
1. **Social proof numbers** (animated counter)
2. **Trust badges** (KRA, Security, M-Pesa verified)
3. **Risk reversal** ("Free forever, no credit card")
4. **Urgency** ("47 businesses joined this week")
5. **Clear value** ("Cut payment delays from 30 to 7 days")

### Copywriting Examples

**Headline Options:**
- "Stop Chasing Payments. Get Paid in 7 Days, Not 30."
- "Professional Invoicing Built for Kenyan Businesses"
- "Get Paid Faster with M-Pesa & Automated Reminders"

**Subheadline:**
- "Send invoices, accept M-Pesa payments, track overdue accounts. KRA eTIMS compliant. Trusted by 500+ Kenyan SMEs, freelancers, and agencies."

**Trust Badge:**
- "✓ KRA eTIMS Compliant | ✓ Bank-Level Security | ✓ M-Pesa Verified Partner"

### Micro-Interactions
- **Counter animation**: Numbers count up on scroll into view
- **Invoice preview**: Subtle pulse on payment status badge
- **CTA hover**: Scale + shadow lift (1.02x scale)
- **Trust badges**: Subtle glow on hover
- **Stats ticker**: Live updating numbers (every 30s)

### Blade/Alpine Implementation

**Component Structure:**
```blade
<x-hero-enhanced
    :stats="$stats"
    :liveCounters="true"
>
    <x-invoice-preview-animated />
</x-hero-enhanced>
```

**Alpine.js Logic:**
```javascript
x-data="{
    businesses: 500,
    invoicesToday: 12,
    animateCounter() {
        // Count up animation
    }
}"
```

**Key Features:**
- Live counter component with Alpine.js
- Trust badge row component
- Animated invoice preview
- Responsive grid (stacks on mobile)

---

## SECTION 2: RECENT INVOICES SECTION

### UX Improvements
- **Real invoice data** (from database, not static)
- **Payment method indicators** (M-Pesa, Bank, Cash)
- **Quick action buttons** (hover reveals: Send Reminder, Mark Paid, View)
- **Filter bar** (Status, Payment Method, Date Range)
- **Search functionality** (client name, invoice number)
- **Empty state** (with CTA to create first invoice)
- **Loading skeleton** (while fetching)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  Recent Invoices                    [Filter] [Search]   │
│                                                           │
│  ┌──────┐ ┌──────┐ ┌──────┐                             │
│  │ INV  │ │ INV  │ │ INV  │                             │
│  │ Card │ │ Card │ │ Card │                             │
│  └──────┘ └──────┘ └──────┘                             │
│                                                           │
│  [Status Filter] [Payment Method] [Date Range]          │
└─────────────────────────────────────────────────────────┘
```

### Kenyan Localization
- **Payment methods**: M-Pesa (green), Bank Transfer (blue), Cash (gray)
- **Status colors**: Paid (emerald), Overdue (red), Pending (amber)
- **Currency format**: KES 12,500 (not KSh)
- **Date format**: "15 Dec 2024" (Kenyan format)
- **Business types**: Garage, Salon, Tech, Consulting, Construction

### High-Conversion Elements
1. **Real data** (builds trust - "these are real invoices")
2. **Quick actions** (reduces friction - "try it now")
3. **Filters** (shows functionality depth)
4. **Payment method badges** (M-Pesa prominence)
5. **Status urgency** (overdue = red = action needed)

### Copywriting
- **Section title**: "See How Kenyan Businesses Get Paid"
- **Empty state**: "No invoices yet. Create your first invoice in 60 seconds →"
- **Filter labels**: "Status", "Payment Method", "Date Range"

### Micro-Interactions
- **Card hover**: Lift + shadow increase + reveal actions
- **Status badge**: Pulse animation for overdue
- **Payment icon**: Subtle bounce on hover
- **Filter toggle**: Smooth slide animation
- **Search**: Real-time filtering with debounce

### Blade/Alpine Implementation

**Component:**
```blade
<x-invoice-showcase
    :invoices="$recentInvoices"
    :showFilters="true"
    :showActions="true"
/>
```

**Alpine.js Features:**
- Filter state management
- Search debouncing
- Hover action reveals
- Status filtering
- Payment method filtering

**Database Query:**
```php
Invoice::with(['client', 'payments', 'platformFees'])
    ->whereIn('status', ['paid', 'sent', 'overdue'])
    ->latest()
    ->take(6)
    ->get()
```

---

## SECTION 3: HOW IT WORKS

### UX Improvements
- **Persona-based storytelling** (Sarah from Nairobi, not generic steps)
- **Time indicators** (60 seconds, instant delivery)
- **Real UI mockups** (screenshot-style previews)
- **Workflow connector** (animated line between steps)
- **Progress indicator** (shows user where they are)
- **Outcome emphasis** (what happens after each step)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  How It Works                                            │
│                                                           │
│  Step 1 ──────> Step 2 ──────> Step 3                   │
│  [Create]      [Send]          [Get Paid]                │
│  (60s)         (Instant)       (7 days avg)             │
│                                                           │
│  [UI Mockup]   [UI Mockup]     [Dashboard]               │
│                                                           │
│  "Sarah's Story" persona narrative                       │
└─────────────────────────────────────────────────────────┘
```

### Kenyan Localization
- **Personas**: "Sarah from Nairobi", "John from Mombasa"
- **Business types**: Freelancer, SME owner, Agency
- **Payment methods**: M-Pesa prominently featured
- **Timeframes**: "7 days average" (Kenyan context)
- **Scenarios**: Real Kenyan business situations

### High-Conversion Elements
1. **Persona connection** ("Sarah is like me")
2. **Time clarity** ("60 seconds" = low commitment)
3. **Visual proof** (UI mockups = real product)
4. **Outcome focus** ("Get paid in 7 days")
5. **Progress visualization** (connector line = journey)

### Copywriting

**Persona Story:**
"Sarah runs a design agency in Nairobi. She used to wait 30+ days for payments. Now she gets paid in 7 days average."

**Step 1 - Create:**
- **Title**: "Create Professional Invoice (60 seconds)"
- **Description**: "Sarah adds her client, selects services, and our system auto-calculates totals including VAT and platform fee."

**Step 2 - Send:**
- **Title**: "Send via M-Pesa, Email, or WhatsApp (Instant)"
- **Description**: "One click sends the invoice. Sarah's client receives it immediately with M-Pesa payment link."

**Step 3 - Get Paid:**
- **Title**: "Get Paid Faster (7 days average)"
- **Description**: "Automated reminders ensure Sarah gets paid. She tracks everything in one dashboard."

### Micro-Interactions
- **Step connector**: Animated line draw on scroll
- **UI mockup**: Fade-in with slight zoom
- **Time badge**: Pulse animation
- **Persona avatar**: Subtle hover effect
- **Progress dots**: Fill animation

### Blade/Alpine Implementation

**Component Structure:**
```blade
<x-how-it-works
    persona="Sarah"
    personaRole="Design Agency Owner"
    personaLocation="Nairobi"
    :steps="$steps"
/>
```

**Steps Data:**
```php
$steps = [
    [
        'number' => 1,
        'title' => 'Create Professional Invoice',
        'time' => '60 seconds',
        'description' => '...',
        'mockup' => 'create-invoice.png',
        'outcome' => 'Professional invoice ready'
    ],
    // ... more steps
];
```

---

## SECTION 4: FEATURES SECTION

### UX Improvements
- **Kenyan-specific features** (KRA eTIMS, M-Pesa auto-reconciliation)
- **Quantified benefits** (numbers, not just descriptions)
- **Feature categories** (Payment, Compliance, Analytics, Automation)
- **Mini-demos** (GIFs or interactive previews)
- **Comparison table** (vs manual invoicing)
- **Feature highlights** (top 3 features get special treatment)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  Features Built for Kenyan Businesses                    │
│                                                           │
│  [Category Tabs] Payment | Compliance | Analytics        │
│                                                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                 │
│  │ Feature  │ │ Feature  │ │ Feature  │                 │
│  │ Card     │ │ Card     │ │ Card     │                 │
│  └──────────┘ └──────────┘ └──────────┘                 │
│                                                           │
│  [Feature Comparison Table]                              │
└─────────────────────────────────────────────────────────┘
```

### Kenyan Localization
- **KRA eTIMS Compliance**: Prominent badge, explanation
- **M-Pesa Auto-Reconciliation**: Green badge, Safaricom colors
- **Multi-currency**: KES, USD, EUR prominently
- **Payment methods**: M-Pesa, Airtel Money, Bank Transfer
- **Tax compliance**: VAT, Withholding Tax support

### High-Conversion Elements
1. **KRA compliance** (legal requirement = must-have)
2. **M-Pesa integration** (primary payment method)
3. **Quantified benefits** ("Save 5 hours/week")
4. **Feature comparison** (vs competitors)
5. **Demo previews** (reduce uncertainty)

### Copywriting

**Feature Cards:**

1. **M-Pesa Auto-Reconciliation**
   - "Automatically match M-Pesa payments to invoices"
   - "Save 2 hours/week on manual reconciliation"
   - Badge: "M-Pesa Verified Partner"

2. **KRA eTIMS Compliance**
   - "Generate KRA-compliant invoices automatically"
   - "Export for eTIMS submission in one click"
   - Badge: "KRA Ready"

3. **Payment Behavior Analytics**
   - "See which clients pay fastest"
   - "Identify payment patterns and optimize"
   - Metric: "Average payment time: 7 days"

4. **Cash Flow Insights**
   - "Forecast cash flow based on pending invoices"
   - "Know exactly when money is coming in"
   - Metric: "KES 2.5M expected this month"

5. **Multi-Currency Support**
   - "Invoice in KES, USD, EUR, GBP"
   - "Auto-convert for international clients"
   - Badge: "Live Exchange Rates"

6. **Automated Reminders**
   - "Send payment reminders automatically"
   - "Reduce overdue invoices by 60%"
   - Metric: "3x faster payments"

### Micro-Interactions
- **Feature card hover**: Lift + reveal demo button
- **Category tabs**: Smooth slide animation
- **Demo GIF**: Play on hover
- **Badge pulse**: For "New" or "Popular" features
- **Metric counter**: Animate numbers on scroll

### Blade/Alpine Implementation

**Component:**
```blade
<x-features-showcase
    :categories="$categories"
    :features="$features"
    :showComparison="true"
/>
```

**Feature Data Structure:**
```php
$features = [
    [
        'name' => 'M-Pesa Auto-Reconciliation',
        'category' => 'payment',
        'description' => '...',
        'benefit' => 'Save 2 hours/week',
        'badge' => 'M-Pesa Verified',
        'demo' => 'mpesa-reconciliation.gif',
        'metric' => 'Auto-match 95% of payments'
    ],
    // ... more features
];
```

---

## SECTION 5: PRICING SECTION

### UX Improvements
- **Localized pricing** (KSh, not USD)
- **Yearly savings calculator** (show discount clearly)
- **"Most Popular" badge** (for Nairobi SMEs)
- **Social proof on tiers** ("47 businesses on Starter")
- **Value justification** (ROI calculator link)
- **Feature comparison** (side-by-side table)
- **Trial period clarity** (14-day free trial)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  Pricing                    [Monthly] [Yearly] (-20%)   │
│                                                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                 │
│  │  Free    │ │ Starter  │ │   Pro    │                 │
│  │          │ │ [POPULAR]│ │          │                 │
│  │  KSh 0   │ │ KSh 999  │ │ KSh 2,999│                 │
│  └──────────┘ └──────────┘ └──────────┘                 │
│                                                           │
│  [Feature Comparison Table]                              │
│  [ROI Calculator Link]                                   │
└─────────────────────────────────────────────────────────┘
```

### Kenyan Localization
- **Currency**: KSh (not USD, not KES in pricing)
- **Pricing tiers**: Affordable for Kenyan SMEs
- **Payment methods**: M-Pesa, Bank Transfer accepted
- **Tax**: "Prices exclude VAT" (if applicable)
- **Local context**: "Most Popular for Nairobi SMEs"

### High-Conversion Elements
1. **Yearly discount** (20% off = clear savings)
2. **Social proof** ("47 businesses on Starter")
3. **Value clarity** (ROI calculator)
4. **Risk reversal** (14-day free trial)
5. **Feature comparison** (helps decision)

### Copywriting

**Pricing Tiers:**

**Free:**
- "Perfect for trying InvoiceHub"
- "3 invoices/month"
- "Basic features"
- CTA: "Start Free"

**Starter (Most Popular):**
- "Best for Nairobi SMEs"
- "KSh 999/month"
- "Unlimited invoices"
- "M-Pesa integration"
- "Auto reminders"
- "47 businesses use this plan"
- CTA: "Start 14-Day Trial"

**Pro:**
- "For growing agencies"
- "KSh 2,999/month"
- "Everything in Starter"
- "KRA eTIMS export"
- "Advanced analytics"
- "Priority support"
- CTA: "Start 14-Day Trial"

**Yearly Toggle:**
- "Save 20% with yearly billing"
- "KSh 9,592/year (KSh 799/month)"
- "Save KSh 2,396/year"

### Micro-Interactions
- **Tier hover**: Scale up slightly (1.02x)
- **Toggle switch**: Smooth slide animation
- **Price update**: Fade transition
- **Popular badge**: Subtle pulse
- **CTA hover**: Shadow lift

### Blade/Alpine Implementation

**Component:**
```blade
<x-pricing-showcase
    :plans="$plans"
    :showYearly="true"
    :showComparison="true"
    :showROI="true"
/>
```

**Pricing Data:**
```php
$plans = [
    [
        'name' => 'Free',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'popular' => false,
        'features' => [...],
        'social_proof' => null
    ],
    [
        'name' => 'Starter',
        'price_monthly' => 999,
        'price_yearly' => 9592,
        'popular' => true,
        'features' => [...],
        'social_proof' => '47 businesses'
    ],
    // ... more plans
];
```

**Alpine.js Toggle:**
```javascript
x-data="{ 
    billing: 'monthly',
    toggleBilling() {
        this.billing = this.billing === 'monthly' ? 'yearly' : 'monthly';
    }
}"
```

---

## SECTION 6: TESTIMONIALS SECTION

### UX Improvements
- **Full customer details** (photo, name, role, business, location)
- **Quantified metrics** ("Cut payment time from 45 to 8 days")
- **Verified badge** ("Verified Customer")
- **Industry diversity** (Construction, Consulting, Freelance, Retail)
- **Video reviews** (optional, for top testimonials)
- **Filter by industry** (help users find similar businesses)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  Testimonials          [Filter: All | Construction | ...]│
│                                                           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                │
│  │[Photo]   │ │[Photo]   │ │[Photo]   │                │
│  │ Name     │ │ Name     │ │ Name     │                │
│  │ Business │ │ Business │ │ Business │                │
│  │ Location │ │ Location │ │ Location │                │
│  │ [Metric] │ │ [Metric] │ │ [Metric] │                │
│  │ [Verified]│ │ [Verified]│ │ [Verified]│              │
│  └──────────┘ └──────────┘ └──────────┘                │
└─────────────────────────────────────────────────────────┘
```

### Kenyan Localization
- **Names**: Real Kenyan names (John Mwangi, Mary Wanjiku)
- **Locations**: Nairobi, Mombasa, Kisumu, Nakuru
- **Business types**: Garage, Salon, Tech, Construction, Consulting
- **Metrics**: Payment time reductions, invoice volume
- **Currency**: KES in testimonials

### High-Conversion Elements
1. **Verified badge** (builds trust)
2. **Quantified results** (specific numbers)
3. **Industry relevance** (find similar businesses)
4. **Location diversity** (not just Nairobi)
5. **Real photos** (if available, or professional avatars)

### Copywriting

**Testimonial Structure:**

1. **John Mwangi - John's Garage, Nairobi**
   - Role: Owner
   - Quote: "Payment delays cut from 45 to 8 days. Game changer for cash flow."
   - Metric: "45 days → 8 days average"
   - Verified: ✓
   - Industry: Automotive

2. **Mary Wanjiku - Mary's Salon, Mombasa**
   - Role: Owner
   - Quote: "M-Pesa integration makes getting paid so easy. Clients love it."
   - Metric: "95% payments via M-Pesa"
   - Verified: ✓
   - Industry: Beauty & Wellness

3. **David Ochieng - TechFix Ltd, Nairobi**
   - Role: Founder
   - Quote: "Professional invoices that clients actually pay on time. KRA compliance is a bonus."
   - Metric: "100% KRA compliant invoices"
   - Verified: ✓
   - Industry: Technology

4. **Sarah Muthoni - Design Studio, Kisumu**
   - Role: Creative Director
   - Quote: "Best investment for my business. ROI in first month."
   - Metric: "KES 50K saved in first month"
   - Verified: ✓
   - Industry: Creative Services

### Micro-Interactions
- **Card hover**: Lift + reveal full testimonial
- **Filter tabs**: Smooth transition
- **Verified badge**: Subtle glow
- **Metric highlight**: Pulse animation
- **Photo hover**: Slight zoom

### Blade/Alpine Implementation

**Component:**
```blade
<x-testimonials-showcase
    :testimonials="$testimonials"
    :showFilter="true"
    :showVideo="false"
/>
```

**Testimonial Data:**
```php
$testimonials = [
    [
        'name' => 'John Mwangi',
        'role' => 'Owner',
        'business' => "John's Garage",
        'location' => 'Nairobi',
        'industry' => 'automotive',
        'photo' => 'john-mwangi.jpg',
        'quote' => '...',
        'metric' => '45 days → 8 days average',
        'verified' => true,
        'video' => null
    ],
    // ... more testimonials
];
```

---

## SECTION 7: ROI CALCULATOR

### UX Improvements
- **Interactive inputs** (sliders + number inputs)
- **Real-time calculation** (update as user types)
- **Visual results** (charts, progress bars)
- **Savings breakdown** (time saved, money saved)
- **Payback period** (how quickly they recover cost)
- **Conversion CTA** (strong call-to-action after results)

### UI Layout
```
┌─────────────────────────────────────────────────────────┐
│  Calculate Your ROI                                      │
│                                                           │
│  Inputs:                                                  │
│  [Invoices/month slider]                                 │
│  [Avg invoice value]                                      │
│  [Current payment delay]                                  │
│                                                           │
│  Results:                                                 │
│  [Time Saved: X hours/month]                             │
│  [Money Saved: KES X/month]                              │
│  [Payback Period: X days]                                │
│                                                           │
│  [Chart: Before vs After]                                │
│                                                           │
│  [Strong CTA: Start Free Trial]                          │
└─────────────────────────────────────────────────────────┘
```

### Kenyan Localization
- **Currency**: KES (not USD)
- **Timeframes**: Days, not weeks
- **Business context**: Kenyan SME scenarios
- **Payment methods**: M-Pesa focus

### High-Conversion Elements
1. **Personalized results** (user's own numbers)
2. **Visual impact** (charts show dramatic difference)
3. **Payback clarity** (how quickly they recover cost)
4. **Strong CTA** (after seeing results)
5. **Social proof** (average results from other users)

### Copywriting

**Calculator Inputs:**
- "How many invoices do you send per month?"
- "What's your average invoice value? (KES)"
- "How many days do you wait for payment currently?"

**Results Display:**
- "You'll save **12 hours/month** on invoice management"
- "You'll get paid **KES 450,000 faster** per month"
- "Your payback period: **3 days**"
- "Annual savings: **KES 5.4M**"

**CTA:**
- "Start Your Free Trial →"
- "See these savings for yourself"

### Micro-Interactions
- **Slider drag**: Smooth value update
- **Number input**: Real-time calculation
- **Results reveal**: Fade-in animation
- **Chart animation**: Bar fill animation
- **CTA pulse**: Subtle pulse on results

### Blade/Alpine Implementation

**Component:**
```blade
<x-roi-calculator
    :defaultValues="$defaults"
    :showChart="true"
    :showCTA="true"
/>
```

**Alpine.js Logic:**
```javascript
x-data="{
    invoicesPerMonth: 20,
    avgInvoiceValue: 50000,
    currentDelay: 30,
    
    calculateROI() {
        // Time saved calculation
        const timeSaved = this.invoicesPerMonth * 0.5; // 30 min per invoice
        
        // Money saved calculation
        const newDelay = 7; // Average with InvoiceHub
        const daysSaved = this.currentDelay - newDelay;
        const moneySaved = (this.invoicesPerMonth * this.avgInvoiceValue * daysSaved) / 30;
        
        // Payback period
        const monthlyCost = 999; // Starter plan
        const paybackDays = (monthlyCost / (moneySaved / 30));
        
        return {
            timeSaved: timeSaved,
            moneySaved: moneySaved,
            paybackDays: paybackDays,
            annualSavings: moneySaved * 12
        };
    }
}"
```

**Formulas:**
- Time Saved = Invoices/Month × 0.5 hours (manual work eliminated)
- Money Saved = (Invoices/Month × Avg Value × Days Saved) / 30
- Payback Period = Monthly Cost / (Money Saved / 30)
- Annual Savings = Money Saved × 12

---

## IMPLEMENTATION PRIORITY

### Phase 1 (High Impact, Quick Wins)
1. Hero Section - Social proof counters
2. Recent Invoices - Real data + filters
3. ROI Calculator - Interactive calculator

### Phase 2 (Medium Impact)
4. How It Works - Persona storytelling
5. Features - Kenyan-specific features
6. Pricing - Yearly toggle + social proof

### Phase 3 (Polish)
7. Testimonials - Enhanced with metrics
8. All sections - Micro-interactions
9. Mobile optimization

---

## TECHNICAL NOTES

### Component Architecture
- Create reusable Blade components for each section
- Use Alpine.js for interactivity
- Keep server-side logic in controllers
- Use Livewire for real-time updates (optional)

### Performance
- Lazy load images and animations
- Use CSS animations over JS where possible
- Debounce search/filter inputs
- Cache expensive calculations

### Accessibility
- Semantic HTML
- ARIA labels
- Keyboard navigation
- Screen reader support
- Color contrast compliance

### Mobile Optimization
- Stack layouts on mobile
- Touch-friendly buttons (min 44px)
- Simplified animations
- Reduced data on mobile

---

## SUCCESS METRICS

- **Conversion Rate**: Target 5%+ (from current baseline)
- **Time on Page**: Target 3+ minutes
- **Scroll Depth**: Target 80%+ reach pricing
- **CTA Clicks**: Track all CTAs separately
- **Calculator Usage**: Target 30%+ of visitors

---

## NEXT STEPS

1. Review this plan with stakeholders
2. Prioritize sections based on impact
3. Create detailed mockups for each section
4. Implement Phase 1 components
5. A/B test variations
6. Iterate based on data

---

**End of Design Plan**

