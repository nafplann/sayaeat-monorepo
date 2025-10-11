# Pyramid Internal API Documentation

This document describes the internal API endpoints for BFF services.

## Authentication

All internal API requests must include an API key in the header:

```
X-Api-Key: your-api-key-here
```

API keys are configured in `config/services.php` and set via environment variables:
- `MERCHANT_BFF_API_KEY` - For Merchant BFF
- `HAPI_BFF_API_KEY` - For Hapi BFF
- `HORUS_BFF_API_KEY` - For Horus BFF (future)

## Base URL

```
http://pyramid:8000/api/internal
```

## Endpoints

### Authentication

#### Validate Token
```http
POST /internal/auth/validate-token
Content-Type: application/json

{
  "token": "sanctum-token-here"
}
```

Response:
```json
{
  "valid": true,
  "user_id": "123",
  "user_type": "App\\Models\\Customer",
  "user": { ... },
  "token_name": "mobile-app"
}
```

#### Validate Credentials
```http
POST /internal/auth/validate-credentials
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

Response:
```json
{
  "valid": true,
  "user": { ... }
}
```

#### Get User
```http
GET /internal/auth/user/{id}?user_type=user
```

---

### Merchants

#### List Merchants
```http
GET /internal/merchants?status=active&per_page=15
```

Query Parameters:
- `status` - Filter by status
- `category` - Filter by category
- `search` - Search by name or slug
- `per_page` - Items per page (default: 15)
- `paginate` - Enable/disable pagination (default: true)

#### Get Merchant
```http
GET /internal/merchants/{id}
```

Returns merchant with menus and menu categories.

#### Create Merchant
```http
POST /internal/merchants
Content-Type: application/json

{
  "name": "Restaurant Name",
  "slug": "restaurant-name",
  "category": "FOOD",
  "status": "active",
  "phone": "1234567890",
  "address": "123 Main St",
  "latitude": -6.2088,
  "longitude": 106.8456
}
```

#### Update Merchant
```http
PUT /internal/merchants/{id}
Content-Type: application/json

{
  "name": "Updated Name",
  "status": "inactive"
}
```

#### Delete Merchant
```http
DELETE /internal/merchants/{id}
```

#### Get Merchant Menus
```http
GET /internal/merchants/{id}/menus
```

#### Get Merchant Menu Categories
```http
GET /internal/merchants/{id}/menu-categories
```

#### Toggle Merchant Status
```http
POST /internal/merchants/{id}/toggle-status
```

---

### Orders

#### List Orders
```http
GET /internal/orders?customer_id=123&status=pending
```

Query Parameters:
- `customer_id` - Filter by customer
- `merchant_id` - Filter by merchant
- `driver_id` - Filter by driver
- `status` - Filter by status
- `payment_status` - Filter by payment status
- `date_from` - Start date
- `date_to` - End date
- `sort_by` - Sort field (default: created_at)
- `sort_order` - Sort direction (default: desc)
- `per_page` - Items per page
- `paginate` - Enable/disable pagination

#### Get Order
```http
GET /internal/orders/{id}
```

Returns order with customer, merchant, driver, items, and addons.

#### Create Order
```http
POST /internal/orders
Content-Type: application/json

{
  "customer_id": "123",
  "merchant_id": "456",
  "items": [
    {
      "menu_id": "789",
      "quantity": 2,
      "price": 50000
    }
  ],
  "subtotal": 100000,
  "delivery_fee": 15000,
  "service_fee": 5000,
  "total": 120000,
  "payment_method": "CASH_ON_DELIVERY",
  "delivery_address": "123 Main St",
  "notes": "Please ring doorbell"
}
```

#### Update Order
```http
PUT /internal/orders/{id}
Content-Type: application/json

{
  "status": "processing",
  "driver_id": "789"
}
```

#### Process Order
```http
POST /internal/orders/{id}/process
```

#### Cancel Order
```http
POST /internal/orders/{id}/cancel
Content-Type: application/json

{
  "reason": "Customer requested cancellation"
}
```

#### Reject Order
```http
POST /internal/orders/{id}/reject
Content-Type: application/json

{
  "reason": "Restaurant is closed"
}
```

---

### Stores

#### List Stores
```http
GET /internal/stores?status=active&category=GROCERY
```

#### Get Store
```http
GET /internal/stores/{id}
```

#### Create Store
```http
POST /internal/stores
Content-Type: application/json

{
  "name": "Store Name",
  "slug": "store-name",
  "category": "GROCERY",
  "status": "active"
}
```

#### Update Store
```http
PUT /internal/stores/{id}
```

#### Delete Store
```http
DELETE /internal/stores/{id}
```

#### Get Store Products
```http
GET /internal/stores/{id}/products
```

#### Toggle Store Status
```http
POST /internal/stores/{id}/toggle-status
```

---

### Products

#### List Products
```http
GET /internal/products?store_id=123&search=apple
```

Query Parameters:
- `store_id` - Filter by store
- `category_id` - Filter by category
- `search` - Search by name

#### Get Product
```http
GET /internal/products/{id}
```

#### Create Product
```http
POST /internal/products
Content-Type: application/json

{
  "store_id": "123",
  "category_id": "456",
  "name": "Product Name",
  "price": 25000,
  "unit": "PCS",
  "stock": 100
}
```

#### Get Products by Store
```http
GET /internal/products/by-store/{storeId}
```

#### Toggle Product Status
```http
POST /internal/products/{id}/toggle-status
```

---

### Menus

#### List Menus
```http
GET /internal/menus?merchant_id=123&status=active
```

Query Parameters:
- `merchant_id` - Filter by merchant
- `category_id` - Filter by category
- `status` - Filter by status
- `search` - Search by name

#### Get Menu
```http
GET /internal/menus/{id}
```

Returns menu with merchant, category, and addons.

#### Create Menu
```http
POST /internal/menus
Content-Type: application/json

{
  "merchant_id": "123",
  "category_id": "456",
  "name": "Nasi Goreng",
  "price": 25000,
  "status": "active"
}
```

#### Get Menus by Merchant
```http
GET /internal/menus/by-merchant/{merchantId}
```

#### Toggle Menu Status
```http
POST /internal/menus/{id}/toggle-status
```

---

### Customers

#### List Customers
```http
GET /internal/customers?search=john
```

Query Parameters:
- `search` - Search by name, email, or phone

#### Get Customer
```http
GET /internal/customers/{id}
```

Returns customer with addresses and orders.

#### Create Customer
```http
POST /internal/customers
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890"
}
```

#### Get Customer Addresses
```http
GET /internal/customers/{id}/addresses
```

#### Get Customer Orders
```http
GET /internal/customers/{id}/orders
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "error": "Unauthorized",
  "message": "Invalid API key"
}
```

### 404 Not Found
```json
{
  "error": "Not Found",
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message"
    ]
  }
}
```

---

## Testing with cURL

```bash
# Test authentication
curl -X POST http://localhost:8000/api/internal/auth/validate-credentials \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# List merchants
curl -X GET "http://localhost:8000/api/internal/merchants?per_page=5" \
  -H "X-Api-Key: your-api-key"

# Get specific merchant
curl -X GET http://localhost:8000/api/internal/merchants/123 \
  -H "X-Api-Key: your-api-key"
```

---

## Notes

- All timestamps are in UTC
- IDs use ULID format
- Pagination uses Laravel's default paginator format
- All responses are JSON
- API keys should be kept secret and rotated regularly

