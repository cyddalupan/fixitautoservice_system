# API Documentation

## Overview

The Fixit Auto Services API provides RESTful endpoints for external integration, mobile applications, and third-party services. All API endpoints require authentication and return JSON responses.

## Base URL

```
https://api.fixitauto.com/v1/
```

## Authentication

### API Token Authentication

All API requests require an authentication token in the Authorization header.

```http
Authorization: Bearer {api_token}
```

### Obtain API Token

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "admin"
    }
  }
}
```

### Refresh Token

```http
POST /api/auth/refresh
Authorization: Bearer {api_token}
```

### Logout

```http
POST /api/auth/logout
Authorization: Bearer {api_token}
```

## Rate Limiting

- **Default:** 60 requests per minute per user
- **Burst:** 100 requests per minute (short bursts)
- **Headers included in response:**
  - `X-RateLimit-Limit`: Maximum requests per minute
  - `X-RateLimit-Remaining`: Remaining requests
  - `X-RateLimit-Reset`: Time when limit resets (Unix timestamp)

## Error Handling

### Standard Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message for field"]
  },
  "code": "ERROR_CODE",
  "timestamp": "2026-02-23T10:00:00Z"
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 400 | Input validation failed |
| `UNAUTHENTICATED` | 401 | Authentication required |
| `UNAUTHORIZED` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `METHOD_NOT_ALLOWED` | 405 | HTTP method not allowed |
| `CONFLICT` | 409 | Resource conflict |
| `UNPROCESSABLE_ENTITY` | 422 | Business logic validation failed |
| `TOO_MANY_REQUESTS` | 429 | Rate limit exceeded |
| `SERVER_ERROR` | 500 | Internal server error |

## Customers API

### List Customers

```http
GET /api/customers
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20, max: 100)
- `search` (optional): Search by name, email, or phone
- `sort` (optional): Sort field (name, email, created_at)
- `order` (optional): Sort order (asc, desc)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "address": "123 Main St",
      "city": "Anytown",
      "state": "CA",
      "postal_code": "12345",
      "customer_since": "2024-01-15",
      "loyalty_points": 1500,
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    }
  ],
  "meta": {
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1,
      "last_page": 5,
      "from": 1,
      "to": 20
    }
  }
}
```

### Get Customer

```http
GET /api/customers/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "city": "Anytown",
    "state": "CA",
    "postal_code": "12345",
    "customer_since": "2024-01-15",
    "loyalty_points": 1500,
    "preferences": {
      "contact_method": "email",
      "newsletter": true,
      "service_reminders": true
    },
    "vehicles": [
      {
        "id": 1,
        "vin": "1HGCM82633A123456",
        "make": "Honda",
        "model": "Accord",
        "year": 2023,
        "color": "Blue"
      }
    ],
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
}
```

### Create Customer

```http
POST /api/customers
Authorization: Bearer {token}
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "phone": "+1234567891",
  "address": "456 Oak Ave",
  "city": "Othertown",
  "state": "NY",
  "postal_code": "54321",
  "preferences": {
    "contact_method": "sms",
    "newsletter": false,
    "service_reminders": true
  }
}
```

### Update Customer

```http
PUT /api/customers/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane.updated@example.com",
  "phone": "+1234567891"
}
```

### Delete Customer

```http
DELETE /api/customers/{id}
Authorization: Bearer {token}
```

## Vehicles API

### List Vehicles

```http
GET /api/vehicles
Authorization: Bearer {token}
```

**Query Parameters:**
- `customer_id` (optional): Filter by customer
- `make` (optional): Filter by make
- `model` (optional): Filter by model
- `year` (optional): Filter by year
- `vin` (optional): Filter by VIN
- `has_recalls` (optional): true/false for vehicles with recalls

### Get Vehicle

```http
GET /api/vehicles/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "vin": "1HGCM82633A123456",
    "license_plate": "ABC123",
    "make": "Honda",
    "model": "Accord",
    "year": 2023,
    "color": "Blue",
    "mileage": 15000,
    "trim": "EX-L",
    "body_style": "Sedan",
    "engine": "2.0L Turbo",
    "transmission": "Automatic",
    "drive_type": "FWD",
    "fuel_type": "Gasoline",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "service_records": [
      {
        "id": 1,
        "service_date": "2024-01-15",
        "service_type": "Oil Change",
        "description": "Regular maintenance",
        "cost": 89.99
      }
    ],
    "recalls": [
      {
        "id": 1,
        "campaign_number": "R2023001",
        "component": "Airbag",
        "status": "open",
        "severity": "high"
      }
    ],
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
}
```

### Create Vehicle

```http
POST /api/vehicles
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 1,
  "vin": "1HGCM82633A123456",
  "license_plate": "ABC123",
  "make": "Honda",
  "model": "Accord",
  "year": 2023,
  "color": "Blue",
  "mileage": 15000
}
```

### Decode VIN

```http
POST /api/vehicles/{id}/decode-vin
Authorization: Bearer {token}
```

### Check Recalls

```http
POST /api/vehicles/{id}/check-recalls
Authorization: Bearer {token}
```

## Service Records API

### List Service Records

```http
GET /api/service-records
Authorization: Bearer {token}
```

**Query Parameters:**
- `vehicle_id` (optional): Filter by vehicle
- `customer_id` (optional): Filter by customer
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date
- `service_type` (optional): Filter by service type

### Create Service Record

```http
POST /api/service-records
Authorization: Bearer {token}
Content-Type: application/json

{
  "vehicle_id": 1,
  "service_date": "2024-02-23",
  "service_type": "Brake Service",
  "description": "Brake pad replacement and rotor resurfacing",
  "mileage_at_service": 25000,
  "cost": 450.00,
  "technician_id": 2,
  "parts_used": [
    {
      "part_number": "BP-1234",
      "description": "Brake Pads",
      "quantity": 2,
      "unit_cost": 45.00
    }
  ],
  "next_service_due": "2024-08-23",
  "next_service_mileage": 35000
}
```

## Appointments API

### List Appointments

```http
GET /api/appointments
Authorization: Bearer {token}
```

**Query Parameters:**
- `date` (optional): Filter by specific date
- `start_date` (optional): Filter by start date range
- `end_date` (optional): Filter by end date range
- `status` (optional): Filter by status
- `customer_id` (optional): Filter by customer

### Create Appointment

```http
POST /api/appointments
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 1,
  "vehicle_id": 1,
  "appointment_date": "2024-02-25T09:00:00Z",
  "service_type": "Oil Change",
  "estimated_duration": 60,
  "notes": "Customer requested synthetic oil"
}
```

### Update Appointment Status

```http
PATCH /api/appointments/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "confirmed"
}
```

## Work Orders API

### List Work Orders

```http
GET /api/work-orders
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Filter by status
- `priority` (optional): Filter by priority
- `technician_id` (optional): Filter by technician
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date

### Create Work Order

```http
POST /api/work-orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "customer_id": 1,
  "vehicle_id": 1,
  "appointment_id": 1,
  "priority": "medium",
  "description": "Oil change and tire rotation",
  "estimated_cost": 120.00,
  "estimated_completion": "2024-02-25T11:00:00Z"
}
```

### Update Work Order Status

```http
PATCH /api/work-orders/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "in_progress",
  "technician_notes": "Started oil change"
}
```

## Inventory API

### List Inventory Items

```http
GET /api/inventory
Authorization: Bearer {token}
```

**Query Parameters:**
- `category_id` (optional): Filter by category
- `supplier_id` (optional): Filter by supplier
- `low_stock` (optional): true/false for low stock items
- `search` (optional): Search by part number or description

### Check Stock

```http
GET /api/inventory/check-stock
Authorization: Bearer {token}
```

**Query Parameters:**
- `part_numbers` (required): Comma-separated list of part numbers

**Response:**
```json
{
  "success": true,
  "data": {
    "BP-1234": {
      "available": true,
      "quantity": 15,
      "location": "A-12",
      "unit_cost": 45.00
    },
    "FL-5678": {
      "available": false,
      "quantity": 0,
      "message": "Out of stock, expected restock: 2024-03-01"
    }
  }
}
```

## VIN Decoder API

### Decode VIN

```http
POST /api/vin-decoder/decode
Authorization: Bearer {token}
Content-Type: application/json

{
  "vin": "1HGCM82633A123456",
  "force_refresh": false
}
```

**Response:**
```json
{
  "success": true,
  "cached": false,
  "data": {
    "vin": "1HGCM82633A123456",
    "make": "Honda",
    "model": "Accord",
    "year": 2023,
    "trim": "EX-L",
    "body_style": "Sedan",
    "engine": "2.0L Turbo 4-cylinder",
    "transmission": "10-Speed Automatic",
    "drive_type": "FWD",
    "fuel_type": "Gasoline",
    "manufacturer": "Honda Motor Co., Ltd.",
    "plant_code": "H",
    "country": "United States"
  },
  "basic_info": {
    "vehicle_type": "Passenger Car",
    "doors": 4,
    "seats": 5,
    "gvwr": "1984 kg"
  },
  "specifications": {
    "engine_displacement": "1996 cc",
    "horsepower": "252 hp @ 6500 rpm",
    "torque": "273 lb-ft @ 1500-4000 rpm",
    "fuel_capacity": "14.8 gallons",
    "epa_city_mpg": 22,
    "epa_highway_mpg": 32,
    "wheelbase": "111.4 in",
    "length": "192.2 in",
    "width": "73.3 in",
    "height": "57.1 in"
  },
  "features": {
    "safety": ["ABS", "Traction Control", "Stability Control", "Airbags"],
    "comfort": ["Leather Seats", "Heated Seats", "Dual Zone Climate Control"],
    "technology": ["Apple CarPlay", "Android Auto", "Navigation System"]
  },
  "maintenance_schedule": {
    "oil_change": "Every 7,500 miles or 12 months",
    "tire_rotation": "Every 7,500 miles",
    "brake_inspection": "Every 15,000 miles",
    "transmission_service": "Every 60,000 miles"
  },
  "cache_info": {
    "cache_hits": 0,
    "last_accessed": null,
    "expires_at": "2024-03-24T10:00:00Z"
  }
}
```

### Batch Decode VINs

```http
POST /api/vin-decoder/batch-decode
Authorization: Bearer {token}
Content-Type: application/json

{
  "vins": ["1HGCM82633A123456", "2HGFA16566H123456", "3VWDP7AJ7DM123456"]
}
```

### Validate VIN

```http
POST /api/vin-decoder/validate
Authorization: Bearer {token}
Content-Type: application/json

{
  "vin": "1HGCM82633A123456"
}
```

**Response:**
```json
{
  "success": true,
  "valid": true,
  "message": "VIN is valid",
  "vin": "1HGCM82633A123456",
  "check_digit_valid": true,
  "wmi": "1HG",
  "vds": "CM8263",
  "vis": "3A123456"
}
```

## Recall Management API

### List Recalls

```http
GET /api/recalls
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` (optional): Filter by status
- `severity` (optional): Filter by severity
- `vehicle_id` (optional): Filter by vehicle
- `start_date` (optional): Filter by start date
- `end_date` (optional): Filter by end date
- `needs_notification` (optional): true/false for recalls needing notification

### Get Recall

```http
GET /api/recalls/{id}
Authorization: Bearer {token}
```

### Create Recall

```http