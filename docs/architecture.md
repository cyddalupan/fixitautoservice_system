# System Architecture

## Overview

Fixit Auto Services is built on a modern Laravel 11 architecture following MVC (Model-View-Controller) patterns with additional service layers for business logic separation.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                        │
├─────────────────────────────────────────────────────────────┤
│  • Blade Templates (Views)                                  │
│  • JavaScript Components                                    │
│  • CSS/SCSS Stylesheets                                     │
│  • Chart.js Visualizations                                  │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                        │
├─────────────────────────────────────────────────────────────┤
│  • Controllers (HTTP Layer)                                 │
│  • Middleware (Request Processing)                          │
│  • Form Requests (Validation)                               │
│  • API Resources (API Responses)                            │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Business Logic Layer                      │
├─────────────────────────────────────────────────────────────┤
│  • Services (Business Logic)                                │
│  • Jobs (Queueable Tasks)                                   │
│  • Events & Listeners                                       │
│  • Commands (Console)                                       │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Data Access Layer                        │
├─────────────────────────────────────────────────────────────┤
│  • Eloquent Models                                          │
│  • Query Builders                                           │
│  • Database Migrations                                      │
│  • Seeders & Factories                                      │
└─────────────────────────────────────────────────────────────┘
                              │
┌─────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                     │
├─────────────────────────────────────────────────────────────┤
│  • MySQL Database                                           │
│  • Redis Cache                                              │
│  • File Storage                                             │
│  • External APIs (VIN, Recall)                              │
└─────────────────────────────────────────────────────────────┘
```

## Directory Structure

### Core Application Structure

```
app/
├── Console/
│   └── Commands/           # Artisan commands
├── Exceptions/             # Custom exception handlers
├── Http/
│   ├── Controllers/        # 20+ controllers organized by feature
│   ├── Middleware/         # Custom middleware (Auth, PortalAuth, etc.)
│   └── Requests/           # Form request validation
├── Models/                 # 40+ Eloquent models
├── Providers/              # Service providers
├── Services/               # Business logic services
└── Traits/                 # Reusable traits
```

### Feature-Based Organization

The application is organized by business features:

```
app/Http/Controllers/
├── CustomerController.php           # Customer management
├── VehicleController.php            # Vehicle management
├── AppointmentController.php        # Appointment scheduling
├── WorkOrderController.php          # Work order management
├── InventoryController.php          # Inventory management
├── PartsProcurementController.php   # Parts procurement
├── CustomerPortalController.php     # Customer portal
├── InvoiceController.php            # Invoicing & payments
├── TechnicianController.php         # Technician management
├── PricingController.php            # Pricing & profit analysis
├── ReportsController.php            # Business intelligence
├── QualityControlDashboardController.php # Quality management
├── ComplianceController.php         # Compliance management
├── VehicleToolsController.php       # Vehicle tools dashboard
├── VINDecoderController.php         # VIN decoding
└── RecallController.php             # Recall management
```

## Database Architecture

### Core Entities

#### Customer Domain
```
Customer ────┐
             ├─── Vehicle ──── ServiceRecord
             └─── Appointment
```

#### Service Domain
```
WorkOrder ────┐
              ├─── VehicleInspection
              ├─── TimeLog (Technician)
              └─── Invoice ──── Payment
```

#### Inventory Domain
```
Inventory ────┐
              ├─── InventoryCategory
              ├─── InventorySupplier
              └─── PurchaseOrder ──── PurchaseOrderItem
```

#### Quality Domain
```
QualityControlChecklist ───┐
                           ├─── QualityAudit
                           └─── NonConformanceReport ──── CorrectiveAction
```

### Key Relationships

1. **One-to-Many:**
   - Customer → Vehicles
   - Vehicle → ServiceRecords
   - WorkOrder → VehicleInspections

2. **Many-to-Many:**
   - Technician ↔ Skill (via pivot table)
   - Vehicle ↔ Parts (via parts_lookup)

3. **Polymorphic:**
   - Documents (attachable to multiple models)
   - Notes (attachable to multiple models)

## Authentication & Authorization

### Authentication System
- **Laravel Sanctum** for API authentication
- **Session-based authentication** for web interface
- **Remember me functionality**
- **Password reset system**

### Authorization Layers

#### 1. Role-Based Access Control (RBAC)
```php
// User roles defined in database
enum UserRole: string {
    case ADMIN = 'admin';
    case TECHNICIAN = 'technician';
    case CUSTOMER = 'customer';
    case MANAGER = 'manager';
}
```

#### 2. Permission System
- **Global permissions** (manage_users, view_reports)
- **Feature-specific permissions** (create_invoice, update_inventory)
- **Object-level permissions** (edit_own_workorders)

#### 3. Middleware Protection
```php
// Route middleware examples
Route::middleware(['auth', 'role:admin'])->group(...);
Route::middleware(['auth', 'permission:view_reports'])->group(...);
Route::middleware(['auth', 'portal'])->group(...); // Customer portal
```

## Service Layer Architecture

### Service Classes
Business logic is extracted into service classes for better separation of concerns:

```php
namespace App\Services;

class VehicleService {
    public function decodeVIN(string $vin): array;
    public function checkRecalls(Vehicle $vehicle): array;
    public function calculateServiceCost(WorkOrder $workOrder): float;
}

class InventoryService {
    public function checkStock(string $partNumber): StockStatus;
    public function createPurchaseOrder(array $items): PurchaseOrder;
    public function updateInventoryLevels(): void;
}

class ReportingService {
    public function generateDailyReport(DateTime $date): Report;
    public function calculateTechnicianProductivity(): array;
    public function analyzeProfitMargins(): ProfitAnalysis;
}
```

### Job Queue System
Long-running tasks are handled by Laravel's queue system:

```php
namespace App\Jobs;

class ProcessVINDecoding implements ShouldQueue {
    public function handle(): void {
        // Batch VIN decoding
    }
}

class SendRecallNotifications implements ShouldQueue {
    public function handle(): void {
        // Batch customer notifications
    }
}

class GenerateMonthlyReports implements ShouldQueue {
    public function handle(): void {
        // Report generation
    }
}
```

## API Architecture

### RESTful API Design
- **Resource-based URLs** (`/api/vehicles`, `/api/invoices`)
- **Standard HTTP methods** (GET, POST, PUT, DELETE, PATCH)
- **Consistent response formats**
- **Versioning support** (`/api/v1/`)

### API Response Format
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Example Resource"
  },
  "meta": {
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1
    }
  }
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  },
  "code": "VALIDATION_ERROR"
}
```

## Caching Strategy

### Multi-Level Caching

#### 1. Application Cache (Redis)
- **VIN decoding results** (30-day TTL)
- **API responses** (5-minute TTL)
- **Frequently accessed data** (user permissions, settings)

#### 2. Database Query Cache
- **Complex query results** (15-minute TTL)
- **Report data** (1-hour TTL)
- **Dashboard statistics** (5-minute TTL)

#### 3. Browser Cache
- **Static assets** (CSS, JS, images)
- **API responses** with proper cache headers

### Cache Invalidation
- **Time-based expiration** for time-sensitive data
- **Event-based invalidation** when data changes
- **Manual cache clearing** via admin interface

## Security Architecture

### Input Validation
- **Form Request Validation** for all user inputs
- **SQL Injection Prevention** via Eloquent ORM
- **XSS Protection** via Blade template escaping
- **CSRF Protection** for all forms

### Data Protection
- **Encryption at rest** for sensitive data
- **HTTPS enforcement** for all communications
- **Secure session management**
- **Regular security audits**

### Access Control
- **Principle of least privilege**
- **Role-based access control**
- **Audit logging** for sensitive operations
- **Two-factor authentication** (optional)

## Performance Optimization

### Database Optimization
- **Indexes** on frequently queried columns
- **Query optimization** with eager loading
- **Database partitioning** for large tables
- **Read replicas** for reporting queries

### Application Optimization
- **Lazy loading** of non-critical resources
- **Asset minification** and bundling
- **CDN integration** for static assets
- **HTTP/2 support**

### Caching Strategy
- **Redis** for session and cache storage
- **OPcache** for PHP bytecode caching
- **Browser caching** with proper headers
- **Database query caching**

## Monitoring & Logging

### Application Logging
- **Structured logging** with context
- **Error tracking** with stack traces
- **Performance metrics** collection
- **Audit trails** for sensitive operations

### System Monitoring
- **Application health checks**
- **Database performance monitoring**
- **Queue worker monitoring**
- **External API availability**

### Alerting System
- **Error rate alerts**
- **Performance degradation alerts**
- **Security incident alerts**
- **Business metric alerts**

## Deployment Architecture

### Development Environment
- **Local development** with Docker
- **Feature branches** with CI/CD
- **Automated testing** on pull requests
- **Code quality checks**

### Staging Environment
- **Production-like configuration**
- **Integration testing**
- **Performance testing**
- **User acceptance testing**

### Production Environment
- **Load-balanced web servers**
- **Database clustering**
- **Redis cache cluster**
- **CDN for static assets**
- **Automated backups**

## Scalability Considerations

### Horizontal Scaling
- **Stateless application servers**
- **Database read replicas**
- **Redis cluster for cache**
- **Load balancer configuration**

### Vertical Scaling
- **Database optimization**
- **Application server tuning**
- **Cache optimization**
- **Queue worker optimization**

### Microservices Readiness
The architecture is designed to support future migration to microservices:
- **Service boundaries** clearly defined
- **API-first design**
- **Event-driven architecture** support
- **Database per service** readiness

## Technology Decisions

### Why Laravel?
- **Rapid development** with built-in features
- **Strong ecosystem** and community support
- **Enterprise-ready** with robust security
- **Excellent documentation** and learning resources

### Why MySQL?
- **ACID compliance** for financial transactions
- **Strong consistency** for inventory management
- **Mature ecosystem** with proven reliability
- **Excellent tooling** for administration

### Why Bootstrap?
- **Responsive design** out of the box
- **Component library** for consistent UI
- **Accessibility features** built-in
- **Large community** and resources

## Future Architecture Evolution

### Phase 2: Microservices
- **Service decomposition** by business domain
- **API Gateway** for service orchestration
- **Event-driven communication** between services
- **Independent deployment** of services

### Phase 3: Cloud Native
- **Container orchestration** with Kubernetes
- **Service mesh** for service communication
- **Cloud-native databases** and caching
- **Serverless functions** for specific tasks

### Phase 4: AI/ML Integration
- **Predictive maintenance** models
- **Customer behavior analysis**
- **Inventory optimization** algorithms
- **Price optimization** models

---

**Last Updated:** February 23, 2026  
**Architecture Version:** 1.0