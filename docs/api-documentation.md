# InvoiceHub API Documentation

## Base URL

```
https://your-domain.com/api/v1
```

## Authentication

InvoiceHub API uses Laravel Sanctum for token-based authentication.

### Login

**Endpoint:** `POST /api/v1/auth/login`

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "My Device" // Optional
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer",
    "expires_at": "2025-12-31T23:59:59Z",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "company_id": 1
    }
  }
}
```

### Using the Token

Include the token in the `Authorization` header:

```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Logout

**Endpoint:** `POST /api/v1/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "message": "Logged out successfully."
}
```

### Get Authenticated User

**Endpoint:** `GET /api/v1/auth/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "company_id": 1,
      "active_company_id": 1
    }
  }
}
```

## Rate Limiting

All API endpoints are rate-limited to **60 requests per minute** per authenticated user.

When rate limit is exceeded, you'll receive a `429 Too Many Requests` response.

## Company Scoping

All API requests are automatically scoped to the authenticated user's active company. You cannot access data from other companies.

## Invoices

### List Invoices

**Endpoint:** `GET /api/v1/invoices`

**Query Parameters:**
- `status` (optional): Filter by status (`draft`, `finalized`, `sent`, `paid`, etc.)
- `search` (optional): Search by invoice number, PO number, or client name
- `per_page` (optional): Number of results per page (default: 15, max: 100)
- `page` (optional): Page number

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "invoice_number": "INV-001",
      "status": "finalized",
      "issue_date": "2025-01-15",
      "due_date": "2025-02-15",
      "subtotal": 1000.00,
      "vat_amount": 160.00,
      "grand_total": 1160.00,
      "client": {
        "id": 1,
        "name": "Client Name",
        "email": "client@example.com"
      },
      "has_snapshot": true
    }
  ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### Get Single Invoice

**Endpoint:** `GET /api/v1/invoices/{id}`

**Response:**
```json
{
  "data": {
    "id": 1,
    "invoice_number": "INV-001",
    "status": "finalized",
    "issue_date": "2025-01-15",
    "due_date": "2025-02-15",
    "subtotal": 1000.00,
    "vat_amount": 160.00,
    "grand_total": 1160.00,
    "client": {
      "id": 1,
      "name": "Client Name",
      "email": "client@example.com"
    },
    "items": [
      {
        "id": 1,
        "description": "Item Description",
        "quantity": 10,
        "unit_price": 100.00,
        "total_price": 1000.00
      }
    ],
    "has_snapshot": true
  }
}
```

### Create Invoice

**Endpoint:** `POST /api/v1/invoices`

**Request Body:**
```json
{
  "client_id": 1,
  "issue_date": "2025-01-15",
  "due_date": "2025-02-15",
  "po_number": "PO-123",
  "notes": "Payment terms: Net 30",
  "vat_registered": true,
  "items": [
    {
      "description": "Item Description",
      "quantity": 10,
      "unit_price": 100.00
    }
  ],
  "discount": 0,
  "discount_type": "fixed"
}
```

**Response:** `201 Created`

```json
{
  "data": {
    "id": 1,
    "invoice_number": "INV-001",
    "status": "draft",
    "subtotal": 1000.00,
    "grand_total": 1000.00
  }
}
```

### Update Invoice

**Endpoint:** `PATCH /api/v1/invoices/{id}`

**Important:** Only draft invoices can be updated. Finalized invoices are immutable.

**Request Body:** (Same as create, all fields optional)

**Response:** `200 OK`

### Finalize Invoice

**Endpoint:** `POST /api/v1/invoices/{id}/finalize`

**Important:** Only draft invoices can be finalized. This creates an immutable snapshot.

**Response:** `200 OK`

```json
{
  "data": {
    "id": 1,
    "invoice_number": "INV-001",
    "status": "finalized",
    "has_snapshot": true
  }
}
```

### Export Invoice for ETIMS

**Endpoint:** `GET /api/v1/invoices/{id}/export/etims`

**Important:** Only finalized invoices with snapshots can be exported.

**Response:** `200 OK`

```json
{
  "status": "success",
  "data": {
    "invoiceNumber": "INV-001",
    "issueDate": "2025-01-15",
    "seller": {
      "kraPin": "P051234567A",
      "name": "Company Name"
    },
    "buyer": {
      "kraPin": "P052345678B",
      "name": "Client Name"
    },
    "items": [...],
    "totals": {
      "subtotal": 1000.00,
      "vatAmount": 160.00,
      "total": 1160.00
    }
  }
}
```

## Companies

### List Companies

**Endpoint:** `GET /api/v1/companies`

Returns all companies the authenticated user has access to.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Company Name",
      "email": "company@example.com",
      "kra_pin": "P051234567A",
      "default_vat_rate": 16.00,
      "vat_enabled": true
    }
  ]
}
```

### Get Single Company

**Endpoint:** `GET /api/v1/companies/{id}`

**Response:** (Same structure as list item)

## Clients

### List Clients

**Endpoint:** `GET /api/v1/clients`

**Query Parameters:**
- `search` (optional): Search by name, email, or phone
- `per_page` (optional): Number of results per page
- `page` (optional): Page number

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Client Name",
      "email": "client@example.com",
      "phone": "+254712345678",
      "kra_pin": "P052345678B"
    }
  ]
}
```

### Get Single Client

**Endpoint:** `GET /api/v1/clients/{id}`

**Response:** (Same structure as list item)

## Error Responses

All errors follow this structure:

```json
{
  "status": "error",
  "message": "Error description"
}
```

### Common Status Codes

- `200 OK`: Success
- `201 Created`: Resource created successfully
- `401 Unauthorized`: Authentication required or invalid token
- `403 Forbidden`: Action not allowed (e.g., updating finalized invoice)
- `404 Not Found`: Resource not found or not accessible
- `422 Unprocessable Entity`: Validation errors
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

### Validation Errors

When validation fails (422), response includes field-specific errors:

```json
{
  "status": "error",
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "items": ["The items field is required."]
  }
}
```

## Best Practices

1. **Always include Authorization header** for protected endpoints
2. **Handle rate limiting** - implement exponential backoff
3. **Check invoice status** before attempting updates
4. **Use pagination** for list endpoints to avoid large responses
5. **Store tokens securely** - never expose tokens in client-side code
6. **Handle token expiration** - implement token refresh logic

## Support

For API support, contact: support@invoicehub.com

