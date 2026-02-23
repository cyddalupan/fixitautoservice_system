# Vehicle Tools Feature Documentation

## Overview

The Vehicle Tools feature provides comprehensive tools for managing vehicle information, including VIN decoding, recall management, and service history tracking. This feature is designed to help automotive service centers efficiently manage vehicle data and safety recalls.

## Features

### 1. VIN Decoder
- **Single VIN Decoding**: Decode individual Vehicle Identification Numbers to get detailed specifications
- **Batch VIN Decoding**: Process multiple VINs simultaneously
- **VIN Validation**: Validate VIN format and check digit
- **Cache Management**: Smart caching of decoded VIN data to reduce API calls
- **Export Functionality**: Export decoded VIN data in JSON or CSV format

### 2. Recall Management
- **Recall Tracking**: Track vehicle safety recalls with comprehensive details
- **Customer Notifications**: Manage customer notification status and history
- **Batch Operations**: Check recalls and send notifications for multiple vehicles
- **Analytics Dashboard**: Visualize recall trends and statistics
- **Export Functionality**: Export recall data in multiple formats

### 3. Service History
- **Vehicle Selection**: Browse and select vehicles to view service history
- **Service Records**: View detailed service records with costs and technician information
- **Charts and Analytics**: Visualize service cost trends and type distribution
- **Vehicle Specifications**: Display vehicle specifications from decoded VIN data

## Database Schema

### Tables Created

#### `vehicle_recalls`
- `id`: Primary key
- `vehicle_id`: Foreign key to vehicles table
- `campaign_number`: Recall campaign identifier
- `component`: Affected component
- `summary`: Brief description of the recall
- `consequence`: Potential consequences if not addressed
- `remedy`: Recommended fix
- `recall_date`: Date of recall announcement
- `status`: Current status (open, in_progress, completed, closed)
- `severity`: Severity level (low, medium, high, critical)
- `estimated_cost`: Estimated repair cost
- `actual_cost`: Actual repair cost
- `estimated_repair_time`: Estimated repair time in hours
- `actual_repair_time`: Actual repair time in hours
- `repair_date`: Date of repair completion
- `customer_notified`: Whether customer has been notified
- `customer_notification_date`: Date of customer notification
- `customer_response`: Customer response if any
- `customer_response_date`: Date of customer response
- `parts_required`: Required parts for repair
- `parts_used`: Actual parts used
- `notes`: Additional notes
- `added_by`: User who added the recall
- `updated_by`: User who last updated the recall
- `created_at`, `updated_at`, `deleted_at`: Timestamps

#### `vin_decoder_cache`
- `id`: Primary key
- `vin`: Vehicle Identification Number (unique)
- `decoded_data`: Full decoded data (JSON)
- `make`: Vehicle make
- `model`: Vehicle model
- `year`: Model year
- `trim`: Trim level
- `engine`: Engine type
- `transmission`: Transmission type
- `body_style`: Body style
- `fuel_type`: Fuel type
- `drive_type`: Drive type (FWD, RWD, AWD)
- `manufacturer`: Manufacturer name
- `plant_code`: Manufacturing plant code
- `basic_info`: Basic vehicle information (JSON)
- `specifications`: Technical specifications (JSON)
- `features`: Vehicle features (JSON)
- `maintenance_schedule`: Recommended maintenance (JSON)
- `cache_hits`: Number of times cached data was accessed
- `last_accessed_at`: Last access timestamp
- `expires_at`: Cache expiration timestamp
- `created_at`, `updated_at`: Timestamps

### Enhanced `vehicles` Table
Added 22 new fields for VIN decoding and enhanced recall tracking:
- VIN decoding details (trim, body_style, drive_type, etc.)
- VIN validation metadata (vin_decoded_at, vin_source, vin_valid, etc.)
- Enhanced recall tracking (open_recall_count, last_recall_check, etc.)
- Enhanced service history (detailed_service_history, first_service_date, etc.)

## Models

### VehicleRecall Model
- Relationships: `vehicle()`, `vehicleWithCustomer()`
- Scopes: `active()`, `open()`, `inProgress()`, `completed()`, `needsNotification()`, `overdue()`, `byStatus()`
- Methods: `statusColor`, `severityColor`, `isOverdue`, `daysOverdue`, `formattedEstimatedCost`, `formattedActualCost`

### VINDecoderCache Model
- Relationships: None (standalone cache)
- Scopes: `notExpired()`, `needsRefresh()`
- Methods: `incrementHit()`, `ageInDays`, `basicInfo`, `specifications`, `features`, `maintenanceSchedule`, `createFromDecodedData()`

### Enhanced Vehicle Model
- New relationships: `recalls()`, `openRecalls()`, `vinCache()`
- New methods: `isVINDecoded()`, `vin_decoding_status`, `vin_decoding_status_with_color`, `needsRecallCheck()`, `recall_status`, `recall_status_with_color`, `specifications`, `features`, `maintenance_schedule`, `detailed_description`, `updateRecallCount()`, `markVINAsDecoded()`

## Controllers

### VehicleToolsController
Main controller for vehicle tools dashboard and operations:
- `dashboard()`: Display vehicle tools dashboard with statistics
- `vinDecoder()`: Display VIN decoder interface
- `decodeVIN()`: Process VIN decoding request
- `serviceHistory()`: Display service history for vehicles
- `recalls()`: Display recall notifications
- `vinResults()`: Display VIN decoding results
- `batchDecodeVIN()`: Batch process VIN decoding
- `batchCheckRecalls()`: Batch check recalls for multiple vehicles
- `exportVehicleData()`: Export vehicle data as JSON
- `clearExpiredCache()`: Clear expired VIN cache entries
- `getStatistics()`: Get vehicle tools statistics for dashboard widgets

### VINDecoderController
Dedicated controller for VIN decoding operations:
- `index()`: Display VIN decoder interface
- `decode()`: Decode VIN and return JSON response
- `batchDecode()`: Batch decode multiple VINs
- `cacheStats()`: Get VIN decoding cache statistics
- `clearCache()`: Clear VIN decoding cache
- `validateVIN()`: Validate VIN format
- `history()`: Get VIN decoding history for a specific vehicle
- `export()`: Export VIN decoding results

### RecallController
Dedicated controller for recall management:
- `dashboard()`: Display recall management dashboard
- `index()`: Display all recalls with filtering
- `show()`: Display recall details
- `create()`: Create a new recall
- `store()`: Store a new recall
- `edit()`: Edit a recall
- `update()`: Update a recall
- `destroy()`: Delete a recall
- `checkVehicleRecalls()`: Check for recalls for a specific vehicle
- `batchCheckRecalls()`: Batch check recalls for multiple vehicles
- `sendNotification()`: Send recall notification to customer
- `batchSendNotifications()`: Batch send notifications for multiple recalls
- `updateStatus()`: Update recall status
- `export()`: Export recalls data
- `statistics()`: Get recall statistics for dashboard
- `needsNotification()`: Get recalls that need customer notification
- `overdue()`: Get overdue recalls
- `urgent()`: Get urgent recalls
- `analytics()`: Get recall analytics report
- `search()`: Search for recalls
- `apiIndex()`: Get recall API for external integration
- `apiShow()`: Get single recall API endpoint
- `scheduleChecks()`: Schedule automatic recall checks
- `runScheduledChecks()`: Run scheduled recall checks

## Views

### Layout Structure
All views are located in `resources/views/vehicle-tools/`:
- `dashboard.blade.php`: Main vehicle tools dashboard
- `vin-decoder.blade.php`: VIN decoding interface
- `service-history.blade.php`: Service history viewer
- `recalls-index.blade.php`: Recall management interface
- `recall-dashboard.blade.php`: Recall analytics dashboard

### View Features
- Responsive design with Bootstrap 4
- JavaScript interactivity for forms and validation
- Chart.js integration for data visualization
- Real-time feedback and validation
- Modal dialogs for batch operations
- Auto-refresh functionality for dashboards
- Comprehensive error states and empty states
- Consistent styling with existing application

## Routes

### Vehicle Tools Routes
Protected by auth middleware, grouped under `/vehicle-tools`:
- `GET /vehicle-tools` → `vehicle-tools.dashboard`
- `GET /vehicle-tools/vin-decoder` → `vehicle-tools.vin-decoder`
- `POST /vehicle-tools/decode-vin` → `vehicle-tools.decode-vin`
- `GET /vehicle-tools/vin-results/{vin?}` → `vehicle-tools.vin-results`
- `GET /vehicle-tools/service-history` → `vehicle-tools.service-history`
- `POST /vehicle-tools/batch-decode-vin` → `vehicle-tools.batch-decode-vin`
- etc.

### VIN Decoder API Routes
Protected by auth middleware, grouped under `/api/vin-decoder`:
- `POST /api/vin-decoder/decode` → `vin-decoder.decode`
- `POST /api/vin-decoder/batch-decode` → `vin-decoder.batch-decode`
- `GET /api/vin-decoder/cache-stats` → `vin-decoder.cache-stats`
- etc.

### Recall Management Routes
Protected by auth middleware, grouped under `/recalls`:
- `GET /recalls` → `recalls.index`
- `GET /recalls/dashboard` → `recalls.dashboard`
- `POST /recalls` → `recalls.store`
- `GET /recalls/{id}` → `recalls.show`
- etc.

## Navigation Integration

The Vehicle Tools feature is integrated into the main navigation sidebar under "Vehicle Tools" with a collapsible menu containing:
- Dashboard
- VIN Decoder
- Service History
- Recall Management
- Recall Analytics
- Quick Actions (Add Recall, Needs Notification, Overdue Recalls)

## Testing

### Unit Tests
- `VehicleToolsControllerTest`: Tests for main vehicle tools controller
- `VINDecoderControllerTest`: Tests for VIN decoder controller
- `RecallControllerTest`: Tests for recall management controller

### Test Coverage
- Authentication and authorization
- Form validation and error handling
- Database operations and relationships
- API responses and error states
- Batch operations and edge cases
- Export functionality
- Cache management

## API Documentation

### VIN Decoder API

#### Decode VIN
```http
POST /api/vin-decoder/decode
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
  "data": { ... },
  "basic_info": { ... },
  "specifications": { ... },
  "features": { ... },
  "maintenance_schedule": { ... },
  "cache_info": { ... }
}
```

#### Batch Decode VINs
```http
POST /api/vin-decoder/batch-decode
Content-Type: application/json

{
  "vins": ["1HGCM82633A123456", "2HGFA16566H123456"]
}
```

### Recall API

#### List Recalls
```http
GET /recalls/api?vin=1HGCM82633A123456&status=open&limit=50&offset=0
```

#### Get Single Recall
```http
GET /recalls/api/{id}
```

## Configuration

### Environment Variables
No additional environment variables required for basic functionality. For production use with real VIN decoding APIs, additional configuration would be needed.

### Cache Configuration
VIN decoding cache expires after 30 days by default. Cache hits are tracked for analytics.

### Scheduled Tasks
Automatic recall checks can be scheduled via the UI (daily, weekly, monthly).

## Deployment Notes

1. Run migrations to create new tables and add fields to vehicles table:
   ```bash
   php artisan migrate
   ```

2. Clear route cache if needed:
   ```bash
   php artisan route:clear
   ```

3. Run tests to verify installation:
   ```bash
   php artisan test --testsuite=Feature
   ```

## Future Enhancements

1. **Real API Integration**: Connect to actual VIN decoding and recall checking services
2. **Email/SMS Notifications**: Automated customer notifications for recalls
3. **Advanced Analytics**: Predictive maintenance and recall trend analysis
4. **Mobile Integration**: Mobile app for technicians to access vehicle tools
5. **API Rate Limiting**: Implement rate limiting for VIN decoding API
6. **Webhook Support**: Webhooks for real-time recall notifications
7. **Multi-language Support**: Support for multiple languages in customer notifications
8. **Advanced Search**: Elasticsearch integration for advanced vehicle search

## Support

For issues or questions regarding the Vehicle Tools feature, please contact the development team or refer to the application documentation.