THESIS SYSTEM - VISUAL WORKFLOW GUIDE
═════════════════════════════════════════════════════════════════════════════════════════════════

COMPLETE END-TO-END FLOW DIAGRAM
────────────────────────────────

    ┌─────────────────────────────────────────────────────────────────────────────────────┐
    │                          SALES TEAM WORKFLOW                                         │
    └─────────────────────────────────────────────────────────────────────────────────────┘

    Create Customer          Create Product          Create Job Order          Approve JO
         │                        │                        │                       │
         ▼                        ▼                        ▼                       ▼
    ┌─────────┐            ┌─────────┐          ┌──────────────────┐      ┌──────────────┐
    │Customer │            │ Product │          │ JO: pending      │      │ JO: approved │
    │  Inc.   │──linked──▶│ PC-001  │◀─linked─│ JO-2026-00001    │      │ (blue badge) │
    └─────────┘            └─────────┘          │ Qty: 50, Qty/MOQ │      └──────────────┘
    Activity Log ✓         Activity Log ✓        │ Status: pending  │      Activity Log ✓
                                                 └──────────────────┘      Real-time ✓
                                                 Activity Log ✓


    ┌─────────────────────────────────────────────────────────────────────────────────────┐
    │                       PRODUCTION TEAM WORKFLOW                                       │
    └─────────────────────────────────────────────────────────────────────────────────────┘

    View Approved JOs      Start Production       Complete Production      Record Transfer
          │                      │                        │                      │
          ▼                      ▼                        ▼                      ▼
    ┌──────────────┐      ┌───────────────────┐   ┌──────────────┐      ┌────────────────┐
    │ JO: approved │      │ JO: in_progress   │   │ JO: completed│      │ Transfer +50   │
    │ (blue badge) │──▶   │ (purple badge)    │──▶│ (green badge)│──▶   │ Qty = 50       │
    └──────────────┘      │ Button: "Complete"│   └──────────────┘      │ Auto-creates   │
    Dashboard View        └───────────────────┘   Activity Log ✓        │ FinishedGood   │
    Real-time ✓            Activity Log ✓          Real-time ✓          │ Auto-update    │
                          Real-time ✓                                    └────────────────┘
                                                                         Activity Log ✓


    ┌─────────────────────────────────────────────────────────────────────────────────────┐
    │                       INVENTORY TEAM WORKFLOW                                        │
    └─────────────────────────────────────────────────────────────────────────────────────┘

    Finished Goods          Activity Logs          Low Stock Alerts        Physical Counts
         │                      │                        │                      │
         ▼                      ▼                        ▼                      ▼
    ┌──────────────┐      ┌──────────────┐      ┌─────────────────┐   ┌──────────────┐
    │FG: 50 units  │      │ All entity   │      │ Product below   │   │ TAG: count   │
    │Value: 75,000│──▶   │ operations   │      │ min_stock level │──▶│ Inventory    │
    │Product: PC-1 │      │ JSON data    │      │ (red highlight) │   │ Adjustments  │
    └──────────────┘      │ searchable   │      └─────────────────┘   └──────────────┘
    Dashboard View        └──────────────┘      Dashboard View         Activity Log ✓
                          Activity Log ✓


    ┌─────────────────────────────────────────────────────────────────────────────────────┐
    │                       LOGISTICS TEAM WORKFLOW                                        │
    └─────────────────────────────────────────────────────────────────────────────────────┘

    Create Delivery        Auto-Populate           Mark Delivered          Tracking
         │                      │                        │                   │
         ▼                      ▼                        ▼                   ▼
    ┌─────────────┐      ┌────────────────────┐   ┌─────────────────┐   ┌─────────────┐
    │ Select JO   │      │ DS: Auto-filled    │   │ DS: delivered   │   │ All Shipped │
    │ (completed) │──▶   │ Qty: 50 auto-pop   │──▶│ (green badge)   │──▶│ Orders      │
    └─────────────┘      │ Product: auto-pop  │   │ Mark Delivered  │   │ Completed   │
                         │ Code: DS-2026-0001 │   │ button disabled │   └─────────────┘
                         │ Status: pending    │   │ Activity Log ✓  │   Dashboard View
                         │ (yellow badge)     │   │ Real-time ✓     │
                         └────────────────────┘   └─────────────────┘
                         Activity Log ✓


═════════════════════════════════════════════════════════════════════════════════════════════

STATUS BADGE COLOR SCHEME
─────────────────────────

  ┌────────────────────────────────────────────────────────────┐
  │  YELLOW: Pending        │  Status waiting for action       │
  │  (warning/action needed)                                    │
  ├────────────────────────────────────────────────────────────┤
  │  BLUE: Approved         │  Approved but not started        │
  │  (in review/processing)                                     │
  ├────────────────────────────────────────────────────────────┤
  │  PURPLE: In Progress    │  Currently being worked on       │
  │  (active/processing)                                        │
  ├────────────────────────────────────────────────────────────┤
  │  GREEN: Completed       │  Successfully finished           │
  │  (success/done)                                             │
  ├────────────────────────────────────────────────────────────┤
  │  RED: Cancelled         │  Cancelled/Deleted               │
  │  (error/cancelled)                                          │
  └────────────────────────────────────────────────────────────┘


═════════════════════════════════════════════════════════════════════════════════════════════

DASHBOARD KPI UPDATES (REAL-TIME)
──────────────────────────────────

SALES DASHBOARD:
┌─────────────────────────────────────────────────────────────────┐
│ Total JOs │ Pending │ Approved │ Completed │ Cancelled           │
│    10     │    2    │    3     │    4      │    1                │
│   KPI     │  KPI    │   KPI    │    KPI    │   KPI               │
└─────────────────────────────────────────────────────────────────┘
      │          │         │         │         │
      └──────────┴─────────┴─────────┴─────────┘
             When Approve button clicked:
             Pending -1 ──▶ Approved +1
             Updates in real-time (< 200ms)


PRODUCTION DASHBOARD:
┌──────────────────────────────────────────────────────────────────┐
│ In Progress │ Backlog │ Completed │ Last Updated: 2:45 PM        │
│      2      │    5    │     4     │                              │
│   KPI       │  KPI    │   KPI     │                              │
└──────────────────────────────────────────────────────────────────┘
      │          │         │
      └──────────┴─────────┘
      When Start/Complete clicked:
      In Progress ±1, Backlog ±1, Completed ±1
      Updates in real-time with toast notification


LOGISTICS DASHBOARD:
┌───────────────────────────────────────────────────────────────┐
│ Today │ Pending │ Completed │ Delayed │ Last Updated: 2:45 PM │
│   3   │    5    │     12    │    2    │                       │
│ KPI   │  KPI    │    KPI    │  KPI    │                       │
└───────────────────────────────────────────────────────────────┘
      │     │        │         │
      └─────┴────────┴─────────┘
      When Mark Delivered clicked:
      Pending -1 ──▶ Completed +1
      Updates in real-time with toast notification


═════════════════════════════════════════════════════════════════════════════════════════════

AUTO-GENERATED CODES PATTERN
────────────────────────────

JOB ORDER (JO):
  Format: JO-YYYY-NNNNN
  ┌─────────────────────────────────────────────────────────────┐
  │ JO-2026-00001 ──▶ First job order created in 2026           │
  │ JO-2026-00002 ──▶ Second job order created same year        │
  │ JO-2027-00001 ──▶ First job order created in 2027 (resets)  │
  └─────────────────────────────────────────────────────────────┘
  Properties:
    • Unique globally
    • Auto-increments daily
    • Resets per year
    • Indexed for fast lookup
    • Database constraint prevents duplicates


DELIVERY SCHEDULE (DS):
  Format: DS-YYYY-NNNNN
  ┌─────────────────────────────────────────────────────────────┐
  │ DS-2026-00001 ──▶ First delivery schedule in 2026           │
  │ DS-2026-00002 ──▶ Second delivery schedule same year        │
  │ DS-2027-00001 ──▶ First delivery schedule in 2027 (resets)  │
  └─────────────────────────────────────────────────────────────┘
  Properties:
    • Unique globally
    • Auto-increments daily
    • Pessimistic locking prevents collisions
    • Resets per year
    • Database constraint enforces uniqueness


TAG NUMBER (Inventory):
  Format: TAG-YYYY-NNNNN
  ┌─────────────────────────────────────────────────────────────┐
  │ TAG-2026-00001 ──▶ First physical count in 2026             │
  │ TAG-2026-00002 ──▶ Second physical count same year          │
  └─────────────────────────────────────────────────────────────┘
  Properties:
    • Unique globally
    • Auto-increments continuously
    • Links physical counts to inventory records
    • Searchable for audits


═════════════════════════════════════════════════════════════════════════════════════════════

ACTIVITY LOG ENTRIES
────────────────────

Sample Activity Log Sequence for Complete Workflow:

  1. 09:15 AM │ User: John (Sales)      │ Action: Customer created         │ Entity: Customer
  2. 09:16 AM │ User: John (Sales)      │ Action: Product created          │ Entity: Product
  3. 10:00 AM │ User: John (Sales)      │ Action: Job Order created        │ Entity: JobOrder (JO-2026-00001)
             │                          │ Values: status: pending           │
             │                          │           qty: 50                 │
  4. 10:30 AM │ User: Alice (Sales)     │ Action: Job Order Approved       │ Entity: JobOrder
             │                          │ Old: status: pending              │
             │                          │ New: status: approved             │
  5. 01:00 PM │ User: Bob (Production)  │ Action: Status Updated           │ Entity: JobOrder
             │                          │ Old: status: approved             │
             │                          │ New: status: in_progress          │
  6. 02:30 PM │ User: Bob (Production)  │ Action: Status Updated           │ Entity: JobOrder
             │                          │ Old: status: in_progress          │
             │                          │ New: status: completed            │
  7. 02:45 PM │ User: Bob (Production)  │ Action: Transfer created         │ Entity: Transfer
             │                          │ Values: qty: 50                   │
  8. 03:00 PM │ User: Carol (Logistics) │ Action: Delivery Schedule created│ Entity: DeliverySchedule (DS-2026-00001)
             │                          │ Values: status: pending           │
  9. 04:00 PM │ User: Carol (Logistics) │ Action: Marked as delivered      │ Entity: DeliverySchedule
             │                          │ Old: status: pending              │
             │                          │ New: status: delivered            │

Each entry includes:
  ✓ Timestamp (HH:MM AM/PM)
  ✓ User name and role
  ✓ Action performed
  ✓ Entity type
  ✓ Entity ID
  ✓ Old/New values for updates
  ✓ Complete audit trail


═════════════════════════════════════════════════════════════════════════════════════════════

REAL-TIME UPDATE FLOW (WebSocket)
──────────────────────────────────

When Status Update Occurs:

  User clicks "Approve" button on Sales Dashboard
         │
         ▼
  POST /job-orders/1/approve
         │
         ▼
  ┌─────────────────────────────┐
  │ JobOrderController::approve │
  │ • Update status to approved │
  │ • Log activity entry        │
  │ • BROADCAST EVENT           │
  └─────────────────────────────┘
         │
         ▼
  Event: JobOrderApproved
         │
         ├─────────────┬────────────┬──────────────┐
         │             │            │              │
         ▼             ▼            ▼              ▼
  Browser 1       Browser 2    Browser 3      Browser 4
  Sales Team      Production   Inventory      Logistics
         │             │            │              │
         ▼             ▼            ▼              ▼
  ┌──────────┐   ┌──────────┐  ┌──────────┐  ┌──────────┐
  │ Dashboard│   │ Dashboard│  │Dashboard │  │Dashboard │
  │ Updates: │   │ Updates: │  │ Updates: │  │ Updates: │
  │ Pending -1   │ Approved │  │ JO seen  │  │ No change│
  │ Approved +1  │ appears  │  │ approved │  │ (not JO) │
  │              │ in list  │  │ in logs  │  │          │
  │ Real-time    │ Real-time    │Real-time    │          │
  │ < 200ms      │ < 200ms      │< 200ms      │          │
  └──────────────┴──────────┴──────────────┴──────────┘
         │
         ▼
  ┌──────────────────┐
  │ Toast appears on │
  │ all connected    │
  │ browsers:        │
  │ "Job Order       │
  │  Approved!"      │
  └──────────────────┘


═════════════════════════════════════════════════════════════════════════════════════════════

AUTHORIZATION MATRIX (VISUAL)
──────────────────────────────

          ┌────────┬──────┬────────────┬───────────┬──────────┐
          │ Admin  │Sales │ Production │ Inventory │Logistics │
          ├────────┼──────┼────────────┼───────────┼──────────┤
Customers │   ✓✓   │  ✓   │     ✓      │     ✓     │    ✓     │
Products  │   ✓✓   │  ✓   │     ✓      │    ✓✓     │    ✓     │
Job Orders│   ✓✓   │  ✓✓  │     ✓      │     ✓     │    ✓     │
- Approve │   ✓    │  ✓   │     ✗      │     ✗     │    ✗     │
- Start   │   ✓    │  ✗   │     ✓      │     ✗     │    ✗     │
Transfers │   ✓✓   │  ✗   │    ✓✓      │     ✓     │    ✗     │
Delivery  │   ✓✓   │  ✗   │     ✗      │     ✗     │   ✓✓     │
- Mark Del│   ✓    │  ✗   │     ✗      │     ✗     │    ✓     │
Inventory │   ✓✓   │  ✗   │     ✗      │    ✓✓     │    ✗     │
Reports   │   ✓✓   │  ✓   │     ✓      │    ✓      │    ✓     │
Activity  │   ✓    │  ✗   │     ✗      │     ✗     │    ✗     │

Legend:
  ✓✓ = Full read/write access
  ✓  = Read-only access
  ✗  = No access


═════════════════════════════════════════════════════════════════════════════════════════════

REPORT EXPORT SAMPLES
─────────────────────

JOB ORDERS REPORT (PDF):

┌─────────────────────────────────────────────────────────────────────────┐
│                     JOB ORDERS REPORT                                   │
│              Generated on January 29, 2026 at 03:15 PM                  │
│                                                                         │
│ Customer: Test Customer Inc. │ Status: All │ Period: Start to End      │
├──────────────────────────────────────────────────────────────────────────┤
│ JO Number │ PO Number │ Product/Model │ Customer │ Qty │ Date │ Status│ Amount
├──────────────────────────────────────────────────────────────────────────┤
│ JO-2026-1 │ PO-12345  │ Model XYZ     │ Test Co. │  50 │Feb15 │ ✓Appr │₱75,000│
│ JO-2026-2 │ PO-12346  │ Model ABC     │ Test Co. │  30 │Feb20 │ ✓Done │₱45,000│
│ JO-2026-3 │ —         │ Model DEF     │ Test Co. │  25 │Feb28 │ Pend │₱37,500│
├──────────────────────────────────────────────────────────────────────────┤
│                                   TOTALS: Qty: 105  │ Amount: ₱157,500   │
└──────────────────────────────────────────────────────────────────────────┘


INVENTORY REPORT (PDF):

┌──────────────────────────────────────────────────────────────────────────┐
│                     INVENTORY REPORT                                     │
│              Generated on January 29, 2026 at 03:20 PM                   │
│                                                                          │
│ Customer: Test Customer Inc. │ Type: All Items                           │
├───────────────────────────────────────────────────────────────────────────┤
│ Product   │ Customer │ Begin│ End │ Variance│ Variance │ End Value     │
│           │          │ Count│Count│ Qty     │ Amount   │               │
├───────────────────────────────────────────────────────────────────────────┤
│ PC-001    │ Test Co. │  100 │ 50  │  -2     │ -₱3,000  │ ₱75,000       │
│ PC-002    │ Test Co. │   50 │ 30  │   0     │  ₱0     │ ₱45,000       │
│ PC-003    │ Test Co. │   80 │ 25  │⚠-8     │⚠-₱12,000 │ ₱37,500 ⚠LOW │
├───────────────────────────────────────────────────────────────────────────┤
│                TOTALS: Stock: 105  │ Variance: -₱15,000 │ Total: ₱157,500│
└───────────────────────────────────────────────────────────────────────────┘


═════════════════════════════════════════════════════════════════════════════════════════════

SYSTEM ARCHITECTURE OVERVIEW
────────────────────────────

┌─────────────────────────────────────────────────────────────────────────────┐
│                          USER INTERFACE LAYER                               │
│                          (Blade Templates)                                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │  Dashboards  │  │  CRUD Forms  │  │   Reports    │  │   Activity   │   │
│  │  (Real-time) │  │  (Validated) │  │   (PDF/HTML) │  │   Logs       │   │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘   │
└────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                         BUSINESS LOGIC LAYER                                │
│                       (Laravel Controllers)                                 │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐           │
│  │ Customer   │  │ Product    │  │ JobOrder   │  │ Dashboard  │           │
│  │ Controller │  │ Controller │  │ Controller │  │ Controller │           │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘           │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐  ┌────────────┐           │
│  │ Transfer   │  │ Delivery   │  │ Report     │  │ Activity   │           │
│  │ Controller │  │ Controller │  │ Controller │  │ Controller │           │
│  └────────────┘  └────────────┘  └────────────┘  └────────────┘           │
└────────────────────────────────────────────────────────────────────────────┘
                    │                                    │
                    ▼                                    ▼
    ┌──────────────────────────┐      ┌──────────────────────────┐
    │ Services & Helpers       │      │ Authorization            │
    │ • ActivityLogger         │      │ • Policies               │
    │ • JobOrderService        │      │ • Middleware             │
    │ • InventoryService       │      │ • Role-based Access      │
    │ • DeliveryService        │      └──────────────────────────┘
    └──────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DATA ACCESS LAYER                                 │
│                        (Eloquent ORM Models)                                │
│  ┌──────────┐ ┌──────────┐ ┌───────────┐ ┌────────┐ ┌──────────┐          │
│  │ Customer │ │ Product  │ │ JobOrder  │ │Transfer│ │Delivery  │ ┌─────┐ │
│  │  Model   │ │  Model   │ │   Model   │ │ Model  │ │ Model    │ │more │ │
│  └──────────┘ └──────────┘ └───────────┘ └────────┘ └──────────┘ └─────┘ │
└────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          DATABASE LAYER                                     │
│                         (MySQL 8.0+)                                       │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │ Tables: customers, products, job_orders, transfers,                 │  │
│  │         delivery_schedules, finished_goods, actual_inventories,      │  │
│  │         activity_logs, users                                         │  │
│  │                                                                      │  │
│  │ Features: Foreign Keys, Indexes, Constraints, Transactions          │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                      REAL-TIME LAYER (WebSocket)                            │
│                                                                             │
│  Laravel Reverb (WebSocket Server)                                         │
│  ├─ Handles WebSocket connections                                         │
│  ├─ Broadcasts Events                                                     │
│  └─ Maintains channel subscriptions                                       │
│                                                                             │
│  Laravel Echo (JavaScript Client)                                          │
│  ├─ Listens for events                                                    │
│  ├─ Updates UI in real-time                                               │
│  └─ Shows notifications                                                   │
│                                                                             │
│  Channels: job-orders, delivery-schedules                                  │
│  Events: status-updated, marked-delivered                                  │
└─────────────────────────────────────────────────────────────────────────────┘


═════════════════════════════════════════════════════════════════════════════════════════════
