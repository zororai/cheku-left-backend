# Cheku Left POS - API Documentation

## Overview

Multi-tenant SaaS backend for butcher shop POS systems with subscription management.

**Base URL:** `/api`

**Authentication:** Laravel Sanctum (Bearer Token)

---

## Authentication

### Login

```
POST /api/login
```

**Body:**
```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "Flutter POS"
}
```

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "owner"
  },
  "butcher_id": 1,
  "subscription_status": "active"
}
```

---

### Logout

```
POST /api/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Logged out successfully."
}
```

---

### Get Current User

```
GET /api/me
```

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "user@example.com",
  "username": "johndoe",
  "role": "owner",
  "is_active": true,
  "butcher_shop": {
    "id": 1,
    "name": "John's Butchery",
    "subscription_status": "active",
    "subscription_end_date": "2026-03-15"
  }
}
```

---

## Super Admin Endpoints

**Middleware:** `role:super_admin`

### Dashboard Statistics

```
GET /api/admin/dashboard
```

**Response:**
```json
{
  "butcher_shops": {
    "total": 25,
    "active": 20,
    "expired": 3,
    "suspended": 2
  },
  "revenue": {
    "total_platform_revenue": 15000.00,
    "monthly_platform_revenue": 2500.00,
    "total_sales_all_shops": 500000.00
  }
}
```

---

### Subscription Overview

```
GET /api/admin/subscriptions/overview
```

**Response:**
```json
{
  "expiring_soon": [
    {
      "id": 1,
      "name": "Shop A",
      "owner": { "id": 1, "name": "Owner", "email": "owner@example.com" },
      "subscription_end_date": "2026-02-20"
    }
  ],
  "recently_expired": []
}
```

---

### Revenue Report

```
GET /api/admin/revenue/report?year=2026
```

**Response:**
```json
{
  "year": 2026,
  "monthly_breakdown": [
    { "month": 1, "total": 2500.00 },
    { "month": 2, "total": 3000.00 }
  ],
  "by_plan": [
    { "name": "Monthly", "total": 1500.00 },
    { "name": "Yearly", "total": 4000.00 }
  ]
}
```

---

### Platform Payments

```
GET /api/admin/payments?date_from=2026-01-01&date_to=2026-12-31&per_page=20
```

**Response:** Paginated list of platform payments

---

## Plans Management (Super Admin)

### List Plans

```
GET /api/admin/plans
```

**Response:**
```json
{
  "plans": [
    {
      "id": 1,
      "name": "Monthly",
      "price": 50.00,
      "duration_days": 30,
      "butcher_shops_count": 15
    },
    {
      "id": 2,
      "name": "Yearly",
      "price": 500.00,
      "duration_days": 365,
      "butcher_shops_count": 10
    }
  ]
}
```

---

### Create Plan

```
POST /api/admin/plans
```

**Body:**
```json
{
  "name": "Quarterly",
  "price": 120.00,
  "duration_days": 90
}
```

---

### Update Plan

```
PUT /api/admin/plans/{id}
```

**Body:**
```json
{
  "name": "Monthly Pro",
  "price": 75.00,
  "duration_days": 30
}
```

---

### Delete Plan

```
DELETE /api/admin/plans/{id}
```

*Note: Cannot delete plans with active subscriptions*

---

## Butcher Shops Management (Super Admin)

### List Butcher Shops

```
GET /api/admin/butcher-shops?status=active&search=john&per_page=20
```

**Response:** Paginated list of butcher shops with owner and plan info

---

### Create Butcher Shop

```
POST /api/admin/butcher-shops
```

**Body:**
```json
{
  "shop_name": "New Butchery",
  "shop_phone": "+263771234567",
  "shop_address": "123 Main Street",
  "owner_name": "John Doe",
  "owner_email": "john@example.com",
  "owner_password": "secret123",
  "plan_id": 1
}
```

---

### View Butcher Shop

```
GET /api/admin/butcher-shops/{id}
```

**Response:**
```json
{
  "butcher_shop": { ... },
  "stats": {
    "total_users": 5,
    "total_products": 20,
    "total_sales": 150,
    "total_revenue": 25000.00
  }
}
```

---

### Update Butcher Shop

```
PUT /api/admin/butcher-shops/{id}
```

**Body:**
```json
{
  "name": "Updated Name",
  "phone": "+263771234567",
  "address": "New Address"
}
```

---

### Delete Butcher Shop

```
DELETE /api/admin/butcher-shops/{id}
```

---

### Suspend Butcher Shop

```
POST /api/admin/butcher-shops/{id}/suspend
```

**Response:**
```json
{
  "message": "Butcher shop suspended successfully.",
  "subscription_status": "suspended"
}
```

---

### Activate Subscription

```
POST /api/admin/butcher-shops/{id}/activate
```

**Body:**
```json
{
  "plan_id": 1
}
```

---

### Extend Subscription

```
POST /api/admin/butcher-shops/{id}/extend
```

**Body:**
```json
{
  "days": 30
}
```

---

### Change Plan

```
POST /api/admin/butcher-shops/{id}/change-plan
```

**Body:**
```json
{
  "plan_id": 2
}
```

---

### Reset API Key

```
POST /api/admin/butcher-shops/{id}/reset-api-key
```

**Response:**
```json
{
  "message": "API key reset successfully.",
  "api_key": "new_64_character_api_key..."
}
```

---

### View Shop Sales

```
GET /api/admin/butcher-shops/{id}/sales?date_from=2026-01-01&date_to=2026-01-31
```

---

### Record Payment

```
POST /api/admin/butcher-shops/{id}/payments
```

**Body:**
```json
{
  "plan_id": 1,
  "amount": 50.00,
  "payment_date": "2026-02-15",
  "payment_method": "EcoCash",
  "reference_number": "EC123456",
  "activate_subscription": true
}
```

---

## Owner Endpoints

**Middleware:** `role:owner`, `subscription`

### View Shop Details

```
GET /api/shop
```

**Response:**
```json
{
  "butcher_shop": {
    "id": 1,
    "name": "My Butchery",
    "phone": "+263771234567",
    "address": "123 Main Street",
    "subscription_plan": "Monthly",
    "subscription_status": "active",
    "subscription_start_date": "2026-02-01",
    "subscription_end_date": "2026-03-01",
    "has_api_key": true
  }
}
```

---

### Update Shop Details

```
PUT /api/shop
```

**Body:**
```json
{
  "name": "Updated Butchery Name",
  "phone": "+263771234567",
  "address": "New Address"
}
```

---

### Generate API Key

```
POST /api/shop/generate-api-key
```

**Response:**
```json
{
  "message": "API key generated successfully.",
  "api_key": "64_character_api_key..."
}
```

---

### View Subscription Status

```
GET /api/shop/subscription
```

**Response:**
```json
{
  "subscription": {
    "plan_name": "Monthly",
    "status": "active",
    "start_date": "2026-02-01",
    "end_date": "2026-03-01",
    "days_remaining": 14,
    "is_active": true
  }
}
```

---

## User Management (Owner)

**Middleware:** `role:owner`, `subscription`

### List Users

```
GET /api/users
```

**Response:**
```json
{
  "users": [
    {
      "id": 2,
      "name": "Manager John",
      "email": "manager@example.com",
      "username": "manager1",
      "role": "manager",
      "is_active": true,
      "created_at": "2026-02-01T10:00:00Z"
    }
  ]
}
```

---

### Create User

```
POST /api/users
```

**Body:**
```json
{
  "name": "New Cashier",
  "email": "cashier@example.com",
  "username": "cashier1",
  "password": "secret123",
  "role": "cashier"
}
```

*Note: Role must be `manager` or `cashier`*

---

### View User

```
GET /api/users/{id}
```

---

### Update User

```
PUT /api/users/{id}
```

**Body:**
```json
{
  "name": "Updated Name",
  "email": "new@example.com",
  "password": "newpassword",
  "role": "manager",
  "is_active": true
}
```

---

### Delete User

```
DELETE /api/users/{id}
```

---

### Toggle User Active Status

```
POST /api/users/{id}/toggle-active
```

**Response:**
```json
{
  "message": "User deactivated.",
  "is_active": false
}
```

---

## Products

**Middleware:** `role:owner,manager`, `subscription`

### List Products

```
GET /api/products?active_only=true
```

**Response:**
```json
{
  "products": [
    {
      "id": 1,
      "butcher_id": 1,
      "name": "Beef",
      "price_per_kg": 12.50,
      "is_active": true
    }
  ]
}
```

---

### Create Product

```
POST /api/products
```

**Body:**
```json
{
  "name": "Chicken",
  "price_per_kg": 8.50,
  "is_active": true
}
```

---

### View Product

```
GET /api/products/{id}
```

---

### Update Product

```
PUT /api/products/{id}
```

**Body:**
```json
{
  "name": "Beef Premium",
  "price_per_kg": 15.00,
  "is_active": true
}
```

---

### Delete Product

```
DELETE /api/products/{id}
```

---

## Sales

**Middleware:** `role:owner,manager,cashier`, `subscription`

### List Sales

```
GET /api/sales?date_from=2026-01-01&date_to=2026-01-31&per_page=20
```

**Response:** Paginated list of sales with user and items

---

### View Sale

```
GET /api/sales/{id}
```

**Response:**
```json
{
  "sale": {
    "id": 1,
    "butcher_id": 1,
    "user_id": 2,
    "device_sale_id": "DEVICE-001",
    "sale_number": "INV-001",
    "total_amount": 150.00,
    "payment_method": "cash",
    "sale_date": "2026-02-15T10:30:00Z",
    "synced_at": "2026-02-15T10:35:00Z",
    "user": { "id": 2, "name": "Cashier" },
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "weight_grams": 2000,
        "price_per_kg": 12.50,
        "total_price": 25.00,
        "product": { "id": 1, "name": "Beef" }
      }
    ]
  }
}
```

---

### Sync Single Sale

```
POST /api/sales
```

**Body:**
```json
{
  "device_sale_id": "DEVICE-001",
  "sale_number": "INV-001",
  "total_amount": 150.00,
  "payment_method": "cash",
  "sale_date": "2026-02-15T10:30:00Z",
  "items": [
    {
      "product_id": 1,
      "weight_grams": 2000,
      "price_per_kg": 12.50,
      "total_price": 25.00
    }
  ]
}
```

*Note: Uses `device_sale_id` for idempotency - duplicate syncs are ignored*

---

### Batch Sync Sales

```
POST /api/sales/sync-batch
```

**Body:**
```json
{
  "sales": [
    {
      "device_sale_id": "DEVICE-001",
      "sale_number": "INV-001",
      "total_amount": 150.00,
      "payment_method": "cash",
      "sale_date": "2026-02-15T10:30:00Z",
      "items": [...]
    },
    {
      "device_sale_id": "DEVICE-002",
      ...
    }
  ]
}
```

**Response:**
```json
{
  "message": "Batch sync completed.",
  "results": {
    "synced": ["DEVICE-002"],
    "skipped": ["DEVICE-001"],
    "errors": []
  }
}
```

---

## Reports

**Middleware:** `role:owner,manager`, `subscription`

### Sales Report

```
GET /api/reports/sales?date_from=2026-01-01&date_to=2026-01-31
```

**Response:**
```json
{
  "period": {
    "from": "2026-01-01",
    "to": "2026-01-31"
  },
  "summary": {
    "total_sales": 150,
    "total_revenue": 25000.00
  },
  "by_payment_method": [
    { "payment_method": "cash", "count": 100, "total": 15000.00 },
    { "payment_method": "card", "count": 50, "total": 10000.00 }
  ],
  "daily_breakdown": [
    { "date": "2026-01-01", "count": 5, "total": 800.00 },
    { "date": "2026-01-02", "count": 8, "total": 1200.00 }
  ]
}
```

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden - Role

```json
{
  "message": "You do not have permission to access this resource."
}
```

### 403 Forbidden - Subscription Expired

```json
{
  "message": "Your subscription has expired. Please contact administrator."
}
```

### 403 Forbidden - Account Suspended

```json
{
  "message": "Your account has been suspended. Please contact administrator."
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## User Roles

| Role | Description |
|------|-------------|
| `super_admin` | Platform owner - manages all butcher shops |
| `owner` | Butcher shop owner - full shop management |
| `manager` | Shop manager - products & reports |
| `cashier` | Sales only |

---

## Default Credentials

**Super Admin:**
- Email: `admin@chekuleft.com`
- Password: `password`

---

## Subscription Status Values

| Status | Description |
|--------|-------------|
| `active` | Subscription is valid |
| `expired` | Subscription end date has passed |
| `suspended` | Manually suspended by Super Admin |
