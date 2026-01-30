SYSTEM ENDPOINTS & API REFERENCE
═════════════════════════════════

PRODUCTS
────────
GET    /products                         List all products
GET    /products/{id}                    View product detail
POST   /products                         Create new product
GET    /products/{id}/edit               Edit form
PUT    /products/{id}                    Update product
DELETE /products/{id}                    Delete product

JOB ORDERS
──────────
GET    /job-orders                       List all job orders
GET    /job-orders/{id}                  View job order detail
POST   /job-orders                       Create new job order (status: pending)
GET    /job-orders/{id}/edit             Edit form
PUT    /job-orders/{id}                  Update job order
DELETE /job-orders/{id}                  Delete job order

STATUS WORKFLOWS
POST   /job-orders/{id}/approve          Approve JO (pending → approved) ✓ Real-time
POST   /job-orders/{id}/update-status    Update JO status (JSON request) ✓ Real-time
    → Requires: { "status": "in_progress|completed" }
    → Only: production role
POST   /job-orders/{id}/cancel           Cancel JO (pending/approved → cancelled)
GET    /job-orders/{id}/details          Get JO data via AJAX (JSON response)

TRANSFERS
─────────
GET    /transfers                        List all transfers
GET    /transfers/{id}                   View transfer detail
POST   /transfers                        Create transfer + auto-create FinishedGoods
GET    /transfers/{id}/edit              Edit form
PUT    /transfers/{id}                   Update transfer
DELETE /transfers/{id}                   Delete transfer

DELIVERY SCHEDULES
──────────────────
GET    /delivery-schedules               List all delivery schedules
GET    /delivery-schedules/{id}          View delivery schedule detail
POST   /delivery-schedules               Create delivery schedule (auto-populate from JO)
GET    /delivery-schedules/{id}/edit     Edit form
PUT    /delivery-schedules/{id}          Update delivery schedule
DELETE /delivery-schedules/{id}          Delete delivery schedule
POST   /delivery-schedules/{id}/mark-delivered  Mark as delivered ✓ Real-time

FINISHED GOODS (VIEW ONLY)
──────────────────────────
GET    /finished-goods                   List inventory items
GET    /finished-goods/{id}              View item detail
PUT    /finished-goods/{id}              Update inventory (edit only)

ACTUAL INVENTORY
────────────────
GET    /actual-inventories               List physical counts
GET    /actual-inventories/{id}          View count detail
POST   /actual-inventories               Create physical inventory count
GET    /actual-inventories/{id}/edit     Edit form
PUT    /actual-inventories/{id}          Update count
DELETE /actual-inventories/{id}          Delete count

DASHBOARDS
──────────
GET    /dashboard                        Main dashboard (KPIs overview)
GET    /dashboard/sales                  Sales dashboard (JO approval)
GET    /dashboard/production             Production dashboard (status updates) ✓ Real-time
GET    /dashboard/inventory              Inventory dashboard (stock tracking)
GET    /dashboard/logistics              Logistics dashboard (delivery tracking) ✓ Real-time

REPORTS
───────
GET    /reports/job-orders               Job Orders report view + filter
GET    /reports/job-orders/pdf           Export Job Orders to PDF
GET    /reports/inventory                Inventory report view + filter
GET    /reports/inventory/pdf            Export Inventory to PDF

ACTIVITY LOGS
─────────────
GET    /activity-logs                    View all activity logs
GET    /activity-logs/{id}               View specific log entry

═════════════════════════════════════════════════════════════════════════════════════════════

REAL-TIME UPDATE EVENTS (WebSocket via Echo/Reverb)
───────────────────────────────────────────────────

Channel: job-orders
Event: JobOrderStatusChanged
  → Fired: When JO status updates (approve, start, complete)
  → Data: { jobOrder: {...}, status: "approved|in_progress|completed" }
  → Listen in: production.blade.php, sales.blade.php

Channel: delivery-schedules
Event: MarkedDelivered
  → Fired: When delivery marked as complete
  → Data: { deliverySchedule: {...}, status: "delivered" }
  → Listen in: logistics.blade.php

═════════════════════════════════════════════════════════════════════════════════════════════

REQUIRED FORM DATA & VALIDATION
──────────────────────────────

CREATE PRODUCT:
  • customer_name: required, string, max:255
  • product_code: required, unique
  • model_name: required
  • description: required
  • date_encoded: required, date
  • specs: optional
  • dimension: optional
  • moq: required, integer, >= 1
  • uom: required, in:PCS,KGS,LTR,MTR,etc
  • currency: required
  • selling_price: required, numeric, >= 0
  • location: optional
  • remarks_po: optional
  • mc: optional, numeric
  • pc: optional, numeric

CREATE JOB ORDER:
  • product_id: required, exists:products
  • po_number: optional
  • qty: required, integer, >= product.moq
  • uom: required
  • date_needed: required, date, >= today
  • status: 'pending' (auto-set)

CREATE TRANSFER:
  • job_order_id: required, exists:job_orders, status=completed
  • qty: required, integer, > 0
  • transfer_status: required, in:completed,in_transit

CREATE DELIVERY SCHEDULE:
  • job_order_id: required, exists:job_orders
  • date: required, date
  • (Other fields auto-populate from Job Order)
  • ds_status: 'pending' (auto-set)

CREATE ACTUAL INVENTORY:
  • product_id: required, exists:products
  • fg_qty: required, integer
  • location: required
  • counted_by_user_id: required, exists:users
  • counted_date: required, date

═════════════════════════════════════════════════════════════════════════════════════════════

STATUS FLOW DIAGRAMS
────────────────────

JOB ORDER STATUS LIFECYCLE:
┌─────────┐     approve      ┌──────────┐     start prod     ┌─────────────┐     complete    ┌──────────┐
│ pending │ ─────────────────>│ approved │ ─────────────────>│ in_progress │ ─────────────>│ completed│
└─────────┘                   └──────────┘                   └─────────────┘              └──────────┘
    │                              │
    └──────────────────────────────┴────────────────────────────────────────────────────────>│ cancelled│
                      cancel                                                               └──────────┘

DELIVERY SCHEDULE STATUS:
┌─────────┐    mark delivered   ┌──────────┐
│ pending │ ───────────────────>│ delivered│
└─────────┘                     └──────────┘
    │
    └────────────────────────────────────────>│ urgent | backlog │ (optional manual status)

═════════════════════════════════════════════════════════════════════════════════════════════

ROLE-BASED ACCESS MATRIX
────────────────────────

                    Admin    Sales   Production  Inventory  Logistics
Customers — handled via Products (managed by product records)
Products             R/W      R        R           R/W        R
Job Orders           R/W      R/W      R           R          R
  - Create            ✓        ✓        ✗           ✗          ✗
  - Approve           ✓        ✓        ✗           ✗          ✗
  - Update Status     ✓        ✗        ✓           ✗          ✗
  - Cancel            ✓        ✓        ✗           ✗          ✗

Transfers            R/W      ✗        R/W         R          ✗
  - Create            ✓        ✗        ✓           ✗          ✗

Delivery Schedules   R/W      ✗        ✗           ✗          R/W
  - Create            ✓        ✗        ✗           ✗          ✓
  - Mark Delivered    ✓        ✗        ✗           ✗          ✓

Finished Goods       R/W      ✗        ✗           R/W        ✗
  - View              ✓        ✗        ✗           ✓          ✗
  - Edit              ✓        ✗        ✗           ✓          ✗

Actual Inventory     R/W      ✗        ✗           R/W        ✗

Activity Logs        R        ✗        ✗           ✗          ✗

Reports              R/W      R        R           R          R
  - Job Orders        ✓        ✓        ✓           ✓          ✓
  - Inventory         ✓        ✗        ✗           ✓          ✗

═════════════════════════════════════════════════════════════════════════════════════════════

AUTO-GENERATED CODES
────────────────────

Job Order Number (JO):
  Format: JO-YYYY-NNNNN
  Example: JO-2026-00001
  Generation: Auto-increment daily, resets per year
  Uniqueness: Database constraint ensures uniqueness

Delivery Schedule Code (DS):
  Format: DS-YYYY-NNNNN
  Example: DS-2026-00001
  Generation: Auto-increment daily, resets per year
  Uniqueness: Database constraint with pessimistic locking

Product Code:
  Format: User-defined (e.g., PC-TEST-001)
  Validation: Required, must be unique per system
  Indexing: Searchable and indexed

Tag Number (Actual Inventory):
  Format: TAG-YYYY-NNNNN
  Example: TAG-2026-00001
  Generation: Auto-increment, ensures uniqueness
  Tracking: Links physical counts to inventory records

═════════════════════════════════════════════════════════════════════════════════════════════

ACTIVITY LOG ENTRY STRUCTURE
───────────────────────────

Each activity log entry contains:
{
  user_id: current user ID,
  action: "created|updated|deleted|approved|marked_delivered",
  entity_type: "Customer|Product|JobOrder|Transfer|DeliverySchedule",
  entity_id: database record ID,
  description: human-readable description,
  old_values: array of before-update values (JSON),
  new_values: array of after-update values (JSON),
  ip_address: user IP address,
  created_at: timestamp,
  updated_at: timestamp
}

Query: SELECT * FROM activity_logs WHERE entity_type='JobOrder' ORDER BY created_at DESC;

═════════════════════════════════════════════════════════════════════════════════════════════

QUICK TEST QUERIES (Laravel Tinker)
────────────────────────────────────

php artisan tinker

# Get total job orders
App\Models\JobOrder::count()

# Get pending job orders
App\Models\JobOrder::where('status', 'pending')->count()

# Get job order with relationships
App\Models\JobOrder::with('product.customer')->first()

# Get latest activity logs
App\Models\ActivityLog::latest()->take(10)->get()

# Check customer-product relationship
$customer = App\Models\Customer::first();
$customer->products()->count()

# Get job order by status
App\Models\JobOrder::where('status', 'approved')->get()

# Create test data (seeder)
php artisan seed:CustomerSeeder

═════════════════════════════════════════════════════════════════════════════════════════════

DEBUG HELPERS
─────────────

Enable Debug Mode (if needed):
  .env: APP_DEBUG=true
  .env: APP_URL=http://localhost

View Database Queries (in blade):
  @dump(DB::getQueryLog())

Check WebSocket Connection:
  Browser Console: Echo (should show connection status)
  echo-server logs

View Activity Logs in Real-time:
  php artisan tail
  (shows all logs including database queries)

═════════════════════════════════════════════════════════════════════════════════════════════
