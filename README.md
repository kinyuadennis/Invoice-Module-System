# InvoiceHub — Professional Invoice Management System

A modern, comprehensive invoice management platform built with **Laravel 12** and **Alpine.js**, specifically designed for Kenyan businesses. This system provides a seamless invoice creation experience with integrated client management, payment method configuration, and automated communication workflows.

---


## 📚 Documentation
- [Architecture Overview](docs/architecture.md)
- [Component Documentation](docs/components.md)
- [API Reference](docs/api.md)
- [UI Guidelines](docs/ui-guidelines.md)
- [Changelog](docs/changelog.md)

## 🚀 Key Features


### **Dual Invoice Creation Interfaces**
- **6-Step Invoice Wizard** — Guided step-by-step invoice creation for structured workflows
- **One-Page Invoice Builder** — Fast, single-page invoice creation with autosave functionality

### **Integrated Client Management**
- **Smart Client Search** — Real-time search and filtering of existing clients
- **Inline Client Creation** — Create new clients directly from the invoice builder without page navigation
- **Client Data Validation** — KRA PIN and phone number validation with automatic normalization
- **Client Scoping** — All clients are scoped per company for data isolation

### **Smart Invoice Numbering**
- Auto-generated invoice references using configurable format (e.g., `INV-YYYY-XXXX`)
- Uniqueness validation to prevent duplicates
- Customizable invoice number format per company
- Real-time validation during invoice creation

### **Dynamic Line Item Management**
- Add items manually or from a service library
- VAT calculation per item (16% default, configurable)
- Real-time subtotal, VAT, platform fee, and grand total calculations
- Inline editing with instant updates
- Quantity and unit price calculations

### **Kenyan Market-Focused**
- **KES Currency Formatting** — All amounts displayed in Kenyan Shillings
- **VAT 16%** — Standard Kenyan VAT rate with per-item toggle
- **Platform Fee 0.8%** — Applied to subtotal + VAT
- **KRA PIN Validation** — Format: Letter + 9 digits + Letter (e.g., A012345678B)
- **Phone Number Normalization** — Automatic conversion to E.164 format for Kenyan numbers

### **Payment Method Integration**
- **Company Payment Methods** — Configure multiple payment methods per company
- **Supported Methods:**
  - **MPesa** — Paybill number, account number, and custom instructions
  - **Bank Transfer** — Account name, number, bank name, and branch
  - **Cash** — Simple cash payment option
- **Conditional Fields** — Dynamic form fields based on selected payment method
- **Payment Method Display** — Show configured methods on invoices

### **Invoice Autosave**
- Automatic draft saving every 30 seconds
- Saves progress without user intervention
- Prevents data loss during long invoice creation sessions
- Visual feedback with save status indicators

### **Export & Communication**
- **Save Draft** — Save invoices as drafts for later completion
- **Generate PDF** — Professional PDF generation with company branding
- **Send via Email** — Direct email delivery to clients
- **Send via WhatsApp** — Queued WhatsApp message delivery
- **Print-Ready Format** — Optimized for printing and digital sharing

### **Company Settings & Customization**
- Invoice template customization
- Invoice format configuration
- Payment methods management
- Company branding and details

---

## 📌 System Architecture

### **Backend Stack**
- **Laravel 12.39.0** — Modern PHP framework
- **PHP 8.3.28** — Latest PHP version
- **MySQL** — Database engine
- **Alpine.js 3.15.2** — Lightweight JavaScript framework for reactivity
- **Tailwind CSS 4.1.17** — Utility-first CSS framework

### **Key Controllers**
- `InvoiceController` — Invoice CRUD, PDF generation, email/WhatsApp sending
- `ClientController` — Client management with AJAX endpoints
- `CompanyController` — Company settings and customization
- `CompanyPaymentMethodController` — Payment method configuration
- `PaymentController` — Payment processing and tracking

### **Services & Business Logic**
- `InvoiceService` — Invoice creation and calculation logic
- `PlatformFeeService` — Platform fee calculations
- `PhoneNumberService` — Phone number normalization and validation
- `KraPin` Validation Rule — KRA PIN format validation

### **Models**
- `Invoice` — Invoice data model with relationships
- `InvoiceItem` — Line items with VAT and pricing
- `Client` — Client information with company scoping
- `Company` — Company details and settings
- `CompanyPaymentMethod` — Payment method configuration
- `Payment` — Payment tracking and records

---

## 🧭 Invoice Creation Workflows

### **6-Step Invoice Wizard**

#### **1️⃣ Client Selection**
- Search existing clients by name, email, or phone
- Auto-fill client details upon selection
- Inline "Create New Client" modal via AJAX
- Real-time client search with debouncing
- Clients automatically scoped to user's company

#### **2️⃣ Invoice Details**
- Issue date and due date selection
- Auto-generated invoice reference
- Editable reference with uniqueness validation
- PO number (optional)
- Notes and terms & conditions fields
- VAT registration toggle

#### **3️⃣ Line Items**
- Add predefined services from library
- Add custom line items manually
- Quantity, unit price, and description
- VAT toggle per item (16%)
- Real-time total calculations
- Remove and edit items inline

#### **4️⃣ Summary**
- Subtotal calculation
- VAT total (16% on selected items)
- Platform fee (0.8% on subtotal + VAT)
- Grand total
- All calculations update instantly with Alpine.js reactivity

#### **5️⃣ Payment Method**
- Select from company-configured payment methods
- Conditional fields based on method type:
  - **MPesa:** Paybill, account number, instructions
  - **Bank Transfer:** Account details, bank name, branch
  - **Cash:** Simple confirmation
- Payment method displayed on invoice

#### **6️⃣ Save & Send**
- **Save Draft** — Save for later completion
- **Generate PDF** — Create downloadable PDF
- **Send Email** — Email invoice to client
- **Send WhatsApp** — Queue WhatsApp message
- **Preview** — Review invoice before sending

### **One-Page Invoice Builder**

A streamlined single-page interface that combines all wizard steps into one view:

- **Autosave** — Automatically saves progress every 30 seconds
- **Real-time Calculations** — Instant updates as you type
- **Inline Client Creation** — Create clients without leaving the page
- **Service Library Integration** — Quick-add services from library
- **Keyboard Shortcuts** — Ctrl+S / Cmd+S to save draft
- **Visual Feedback** — Save status indicators and validation messages

---

## 📂 Project Structure

### **Frontend Components**

```
resources/views/components/
├── invoice-wizard.blade.php              # 6-step wizard main component
├── one-page-invoice-builder.blade.php    # Single-page builder
├── client-selector.blade.php             # Client search and selection
├── client-create-modal.blade.php         # Inline client creation modal
├── invoice-details-form.blade.php        # Invoice details form
├── line-items-editor.blade.php           # Line items management
├── invoice-summary.blade.php             # Summary calculations
├── payment-method-selector.blade.php     # Payment method selection
├── payment-method-modal.blade.php        # Payment method configuration
├── payment-methods-section.blade.php       # Payment methods display
├── invoice-actions.blade.php             # Save, PDF, email, WhatsApp
├── step-indicator.blade.php              # Wizard step navigation
└── service-library-dropdown.blade.php    # Service library selector
```

### **Backend Controllers**

```
app/Http/Controllers/User/
├── InvoiceController.php                  # Invoice CRUD and actions
├── ClientController.php                   # Client management
├── CompanyController.php                 # Company settings
└── CompanyPaymentMethodController.php     # Payment method config
```

### **Models**

```
app/Models/
├── Invoice.php                           # Invoice model
├── InvoiceItem.php                       # Line items model
├── Client.php                            # Client model
├── Company.php                           # Company model
├── CompanyPaymentMethod.php              # Payment method model
└── Payment.php                           # Payment tracking
```

### **Services**

```
app/Http/Services/
├── InvoiceService.php                    # Invoice business logic
└── PlatformFeeService.php                # Fee calculations
```

### **Validation Rules**

```
app/Rules/
├── PhoneNumber.php                       # Phone number validation
└── KraPin.php                            # KRA PIN format validation
```

### **Database Migrations**

```
database/migrations/
├── create_invoices_table.php
├── create_invoice_items_table.php
├── create_clients_table.php
├── create_companies_table.php
├── create_company_payment_methods_table.php
├── add_invoice_reference_to_invoices_table.php
└── add_payment_method_to_invoices_table.php
```

---

## 🔧 Installation & Setup

### **Prerequisites**
- PHP 8.3.28 or higher
- Composer
- Node.js and npm
- MySQL database

### **1. Clone Repository**

```bash
git clone https://github.com/<your-username>/invoice-module-system.git
cd invoice-module-system
```

### **2. Install Dependencies**

```bash
# PHP dependencies
composer install

# JavaScript dependencies
npm install
```

### **3. Environment Configuration**

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Update `.env` with your database credentials and other settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=invoice_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### **4. Run Migrations**

```bash
php artisan migrate
```

### **5. Build Frontend Assets**

```bash
# Development
npm run dev

# Production
npm run build
```

### **6. Start Development Server**

```bash
php artisan serve
```

Or use the combined dev command:

```bash
composer run dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite dev server

The application will be available at `http://localhost:8000`

---

## 🧪 Testing Scenarios

### **Invoice Creation**
- ✅ Create invoice with existing client
- ✅ Create invoice with new client (inline creation)
- ✅ Add custom line items
- ✅ Add items from service library
- ✅ Toggle VAT per item
- ✅ Validate invoice reference uniqueness
- ✅ Save draft and resume later
- ✅ Autosave functionality

### **Client Management**
- ✅ Search and filter clients
- ✅ Create new client inline
- ✅ Validate KRA PIN format
- ✅ Validate and normalize phone numbers
- ✅ Client scoping per company

### **Payment Methods**
- ✅ Configure MPesa payment method
- ✅ Configure bank transfer details
- ✅ Configure cash payment
- ✅ Display payment methods on invoice
- ✅ Conditional field validation

### **Invoice Actions**
- ✅ Generate PDF
- ✅ Send email
- ✅ Send WhatsApp (queued)
- ✅ Preview invoice
- ✅ Print invoice

### **Calculations**
- ✅ Subtotal calculation
- ✅ VAT calculation (16% per item)
- ✅ Platform fee calculation (0.8%)
- ✅ Grand total calculation
- ✅ Real-time updates

---

## 🔍 Edge Cases Handled

- **Empty Line Items** — Validation prevents saving invoices without items
- **Due Date Validation** — Ensures due date is after issue date
- **Duplicate Invoice Reference** — Real-time uniqueness validation
- **Invalid Client Data** — Comprehensive validation with clear error messages
- **Network Errors** — Graceful handling of AJAX failures
- **CSRF Token Mismatch** — Automatic detection and user feedback
- **Session Expiration** — Clear error messages for expired sessions
- **Phone Number Formats** — Automatic normalization of various formats
- **KRA PIN Validation** — Strict format validation with helpful messages

---

## 📘 API Endpoints

### **Invoice Endpoints**

```
GET    /app/invoices                    # List invoices
POST   /app/invoices                    # Create invoice
GET    /app/invoices/{id}               # Show invoice
PUT    /app/invoices/{id}               # Update invoice
DELETE /app/invoices/{id}               # Delete invoice
POST   /app/invoices/preview            # Preview invoice
POST   /app/invoices/autosave           # Autosave draft
GET    /app/invoices/{id}/pdf           # Generate PDF
POST   /app/invoices/{id}/send-email    # Send email
POST   /app/invoices/{id}/send-whatsapp # Send WhatsApp
```

### **Client Endpoints**

```
GET    /app/clients                     # List clients
POST   /app/clients                     # Create client
GET    /app/clients/search              # Search clients
GET    /app/clients/{id}                # Show client
PUT    /app/clients/{id}                # Update client
DELETE /app/clients/{id}                # Delete client
```

### **Company Endpoints**

```
GET    /app/company/invoice-customization    # Get customization settings
POST   /app/company/invoice-format           # Update invoice format
POST   /app/company/invoice-template         # Update invoice template
GET    /app/company/payment-methods          # List payment methods
POST   /app/company/payment-methods          # Create payment method
PUT    /app/company/payment-methods/{id}     # Update payment method
DELETE /app/company/payment-methods/{id}     # Delete payment method
```

---

## 🏗️ Implementation Details

### **Alpine.js State Management**

The invoice builder uses Alpine.js for reactive state management:

```javascript
{
    currentStep: 1,              // Wizard step (1-6)
    formData: {                   // Invoice data
        client_id: null,
        client: null,
        issue_date: '',
        due_date: '',
        invoice_reference: '',
        items: [],
        payment_method: null,
        // ... more fields
    },
    calculations: {               // Real-time calculations
        subtotal: 0,
        vat: 0,
        platform_fee: 0,
        total: 0,
        grand_total: 0
    },
    autosaveStatus: 'idle',      // 'idle', 'saving', 'saved'
    draftId: null,
    // ... more state
}
```

### **Platform Fee Calculation**

Platform fee is calculated as 0.8% of (subtotal + VAT):

```php
$platformFee = ($subtotal + $vat) * 0.008;
```

### **VAT Calculation**

VAT is calculated at 16% on items where VAT is enabled:

```php
$vat = $item->vat_enabled 
    ? ($item->quantity * $item->unit_price) * 0.16 
    : 0;
```

### **Phone Number Normalization**

Kenyan phone numbers are normalized to E.164 format:

- `0712345678` → `+254712345678`
- `+254 712 345 678` → `+254712345678`
- `254712345678` → `+254712345678`

### **KRA PIN Validation**

KRA PIN format: Letter + 9 digits + Letter (e.g., `A012345678B`)

- Case-insensitive input
- Automatic uppercase conversion
- Format validation with helpful error messages

### **CSRF Token Handling**

All AJAX requests include CSRF token:

```javascript
headers: {
    'X-CSRF-TOKEN': csrfToken,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
}
```

---

## 🎯 Success Criteria

✅ **Smooth Invoice Creation** — Intuitive, step-by-step or single-page experience  
✅ **Accurate Calculations** — Real-time VAT, fees, and totals  
✅ **Clean UI/UX** — Responsive design that works on mobile and desktop  
✅ **Print-Ready PDFs** — Professional invoice formatting  
✅ **Reliable Communication** — Email and WhatsApp delivery  
✅ **Robust Validation** — Comprehensive backend and frontend validation  
✅ **Data Integrity** — Proper scoping, uniqueness, and relationship management  
✅ **Performance** — Fast autosave, real-time updates, optimized queries  

---

## 🛠️ Development

### **Code Style**

The project uses Laravel Pint for code formatting:

```bash
vendor/bin/pint
```

### **Running Tests**

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=InvoiceTest

# Run with coverage
php artisan test --coverage
```

### **Database Seeding**

```bash
php artisan db:seed
```

### **Queue Processing**

For WhatsApp and email queuing:

```bash
php artisan queue:work
```

---

## 📄 License

MIT License — Free to use and modify.

---

## 🤝 Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss your proposed changes.

### **Contributing Guidelines**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 👤 Author

**Denis** — Nuvemite Technologies

- Email: denis@nuvemite.co.ke
- Location: Nairobi, Kenya

---

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- Styled with [Tailwind CSS](https://tailwindcss.com)
- Enhanced with [Alpine.js](https://alpinejs.dev)
- Designed for Kenyan businesses and freelancers

---

## 📞 Support

For support, email denis@nuvemite.co.ke or open an issue in the repository.

---

**Made with ❤️ in Nairobi, Kenya**
