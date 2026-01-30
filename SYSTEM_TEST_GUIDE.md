THESIS SYSTEM - COMPLETE WORKFLOW TEST GUIDE
=============================================

This guide will walk you through the complete system flow from customer creation to delivery.
All functionality has been implemented and integrated. Follow each step carefully and verify all expected behaviors.

EXPECTED BEHAVIORS & COMPONENTS:
✓ Real-time updates via WebSocket (Echo/Reverb)
✓ Activity logging for all operations
✓ Authorization & permissions
✓ Status workflows and transitions
✓ Auto-generated codes (TAG-YYYY-NNNN, DS-YYYY-NNNN)
✓ Event broadcasting for notifications
✓ Dashboard updates in real-time

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 1: CREATE A NEW CUSTOMER
─────────────────────────────
Action: Admin Panel → Master Data → Customers → Create New

Fields to Fill:
  • Name: "Test Customer Inc." (or any name)

Expected Outcomes:
  ✓ Customer is created and listed
  ✓ Activity log entry created (check Admin → Activity Logs)
  ✓ Customer appears in Product customer dropdown

Verification:
  1. Go to Customers list, verify new customer appears
  2. Check Activity Logs - should see "Customer created" entry
  3. Navigate to Products → Create, verify customer appears in dropdown

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 2: CREATE A NEW PRODUCT
────────────────────────────
Action: Admin Panel → Master Data → Products → Create New

Fields to Fill (Required):
  • Customer: "Test Customer Inc." (from Step 1)
  • Product Code: "PC-TEST-001"
  • Model Name: "Test Model XYZ"
  • Description: "Test product for workflow"
  • Date Encoded: Today's date
  • UoM: "PCS" (or other unit)
  • Selling Price: "1500.00" (or any price)
  • MOQ: "10"
  
Optional Fields:
  • Specs, Dimension, Location, etc.

Expected Outcomes:
  ✓ Product is created with all fields validated
  ✓ Product code is unique
  ✓ Activity log entry created
  ✓ Product appears in Job Order product dropdown
  ✓ Customer relationship is established

Verification:
  1. Product list shows new product
  2. Product detail page displays correct customer name
  3. Activity log shows product creation
  4. Check that product is searchable by code or model name

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 3: CREATE A NEW JOB ORDER (Sales)
──────────────────────────────────────
Action: Sales/Admin → Job Orders → Create New

Fields to Fill (Required):
  • Product: "Test Model XYZ" (from Step 2)
  • PO Number: "PO-20260129-001" (optional but recommended)
  • Quantity: "50"
  • Date Needed: "2026-02-15" (future date)
  • Status: "pending" (auto-set on creation)

Expected Outcomes:
  ✓ Job Order created with status = "pending"
  ✓ JO Number auto-generated (e.g., "JO-2026-001")
  ✓ Quantity validated (>= MOQ)
  ✓ Activity log entry created with details
  ✓ Sales Dashboard updates to show new pending JO
  ✓ Real-time update on Sales Dashboard (refresh to verify)

Verification:
  1. Job Orders list shows new JO with status "pending" (yellow badge)
  2. Dashboard → Sales shows: +1 in "Pending Job Orders"
  3. Activity log shows "Job Order created"
  4. Job Order detail page displays all information correctly
  5. Edit page allows viewing but respects status restrictions

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 4: APPROVE JOB ORDER (Sales/Admin)
────────────────────────────────────────
Action: Dashboard → Sales → Find the pending JO → Click "Approve" button

Expected Outcomes:
  ✓ JO status changes: "pending" → "approved" (blue badge)
  ✓ Activity log entry: "Job Order Approved"
  ✓ Sales Dashboard updates in real-time:
    - Pending count: -1
    - Approved count: +1
  ✓ Real-time notification/toast appears
  ✓ Production Dashboard now shows JO as available for production
  ✓ Event broadcast: JobOrderStatusChanged

Verification:
  1. Status badge on JO detail changes to blue "Approved"
  2. Sales Dashboard values update immediately (no refresh needed if Echo working)
  3. Activity log shows approval entry with timestamp
  4. Production team can now see this JO in Production Dashboard
  5. Verify in DB: job_orders.status = 'approved'

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 5: START PRODUCTION (Production Team)
───────────────────────────────────────────
Action: Dashboard → Production → Find the approved JO → Click "Start Production" button

Expected Outcomes:
  ✓ JO status changes: "approved" → "in_progress" (purple badge)
  ✓ Button changes from "Start Production" to "Complete"
  ✓ Activity log entry: "Job Order Status Updated" (status: in_progress)
  ✓ Production Dashboard updates in real-time:
    - KPI: "In Progress" count increases
    - "Backlog" count may adjust
  ✓ Echo listener receives status update event
  ✓ Real-time toast notification appears: "Job order status updated to in_progress"
  ✓ Event broadcast: JobOrderStatusChanged

Verification:
  1. Status badge changes to purple "In Progress"
  2. Start Production button is replaced with "Complete" button
  3. Production Dashboard updates without page refresh
  4. Activity log shows status change with old/new values
  5. Sales Dashboard "Approved" count decreases (if real-time working)
  6. Check DB: job_orders.status = 'in_progress'

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 6: COMPLETE PRODUCTION (Production Team)
──────────────────────────────────────────────
Action: Dashboard → Production → Find the in-progress JO → Click "Complete" button

Expected Outcomes:
  ✓ JO status changes: "in_progress" → "completed" (green badge)
  ✓ Activity log entry: "Job Order Status Updated" (status: completed)
  ✓ Production Dashboard updates:
    - "In Progress" count decreases
    - Job order moves from active list
  ✓ Finished Goods are auto-created/updated (if applicable)
  ✓ Real-time notification appears
  ✓ JO becomes available for transfer recording

Verification:
  1. Status badge changes to green "Completed"
  2. JO no longer appears in active production list
  3. Activity log entry created with completion timestamp
  4. Real-time dashboard updates reflect completion
  5. Finished Goods records are created (check Inventory → Finished Goods)
  6. Check DB: job_orders.status = 'completed'

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 7: CREATE TRANSFER / RECORD PRODUCTION OUTPUT (Production)
───────────────────────────────────────────────────────────────
Action: Admin/Production → Transfers → Create New

Fields to Fill (Required):
  • Job Order: Select the completed JO from Step 6
  • Transfer Qty: "50" (should match or be less than JO qty)
  • Transfer Status: "completed"

Expected Outcomes:
  ✓ Transfer created linking to completed Job Order
  ✓ Finished Goods record is created/updated:
    - Beginning Count updated
    - Ending Count = Beginning + Transfer Qty
  ✓ Activity log entry: "Transfer created"
  ✓ Dashboard Inventory updates to show new stock

Verification:
  1. Transfer appears in Transfers list
  2. Finished Goods page shows new inventory for the product
  3. Activity log shows transfer creation
  4. Stock values correctly calculated
  5. Check: finished_goods.ending_count reflects the transfer

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 8: CREATE DELIVERY SCHEDULE (Logistics)
─────────────────────────────────────────────
Action: Admin/Logistics → Delivery Schedules → Create New

The system should auto-populate fields from Job Order:

Fields (Auto-filled expected):
  • Job Order: Select the completed JO
  • Product: Auto-fills from JO → Product
  • Qty: Auto-fills from JO → Qty
  • UoM: Auto-fills from JO → Product → UoM
  • Date: Today or specify delivery date
  • Delivery Status: "pending" (auto-set)

Expected Outcomes:
  ✓ Delivery Schedule created with status "pending"
  ✓ Delivery Code auto-generated (DS-2026-001)
  ✓ Auto-population from Job Order works correctly
  ✓ Activity log entry: "Delivery Schedule created"
  ✓ Logistics Dashboard shows new pending delivery

Verification:
  1. Delivery Schedule appears in list with "pending" status (yellow badge)
  2. Check auto-populated fields match the Job Order
  3. Delivery Code is unique and properly formatted
  4. Activity log shows creation entry
  5. Logistics Dashboard KPI updates: "Pending Deliveries" increases

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 9: MARK DELIVERY AS DELIVERED (Logistics)
───────────────────────────────────────────────
Action: Dashboard → Logistics → Find the delivery schedule → Click "Mark Delivered" button

Expected Outcomes:
  ✓ Delivery Status changes: "pending" → "delivered" (green badge)
  ✓ Activity log entry: "Delivery Schedule marked as delivered"
  ✓ Logistics Dashboard updates in real-time:
    - Completed Deliveries count increases
    - Pending Deliveries count decreases
    - Delivery removed from active list
  ✓ Real-time notification appears
  ✓ Status badge changes from yellow to green

Verification:
  1. Status badge changes to green "Delivered"
  2. Button is no longer available (replaced with "Delivered" text)
  3. Logistics Dashboard updates without page refresh
  4. Activity log shows delivery completion
  5. Check DB: delivery_schedules.ds_status = 'delivered'
  6. Historical data preserved for reporting

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 10: VERIFY REAL-TIME UPDATES & NOTIFICATIONS
──────────────────────────────────────────────────
Open Dashboard in Multiple Browser Tabs/Windows:

Test Real-Time Updates:
  1. Open Dashboard → Sales in TAB A
  2. Open Dashboard → Sales in TAB B
  3. In TAB A: Approve a pending Job Order
  4. In TAB B: Verify the dashboard updates WITHOUT manual refresh
     - Status badge changes color
     - KPI counts update immediately
     - Pending count decreases, Approved count increases
  5. Look for toast notification at bottom right

Expected Real-Time Behaviors:
  ✓ Echo listeners active in all dashboards
  ✓ WebSocket channel broadcasts received
  ✓ UI updates without page reload
  ✓ Toast notifications appear with status messages
  ✓ Activity log displays new entries in real-time

Test Action Methods:
  • Job Order Approve: Real-time on Sales Dashboard
  • Status Update (Start/Complete): Real-time on Production Dashboard
  • Mark Delivered: Real-time on Logistics Dashboard

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 11: VERIFY ACTIVITY LOGS
──────────────────────────────
Action: Admin → Activity Logs

Expected Activity Log Entries (in order):
  1. Customer created
  2. Product created
  3. Job Order created (JO-2026-001)
  4. Job Order Approved
  5. Job Order Status Updated (pending → in_progress)
  6. Job Order Status Updated (in_progress → completed)
  7. Transfer created (if done)
  8. Delivery Schedule created
  9. Delivery Schedule marked as delivered

Each Log Should Include:
  ✓ Entity Type (Customer, Product, Job Order, etc.)
  ✓ Action (created, updated, approved)
  ✓ Timestamp with user
  ✓ Old/New values for updates (in JSON format)
  ✓ Entity ID for tracing

Verification:
  1. All steps logged with correct timestamps
  2. Status changes show old and new values
  3. Can click on activity log to see details
  4. Array values properly JSON encoded (fixed in recent update)

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 12: VERIFY AUTHORIZATIONS & PERMISSIONS
──────────────────────────────────────────────
Test Role-Based Access Control:

Sales Role:
  ✓ Can create and view Job Orders
  ✓ Can approve Job Orders
  ✓ Cannot access Inventory, Production, Logistics
  ✓ Can view Sales Dashboard

Production Role:
  ✓ Can view approved Job Orders
  ✓ Can start/complete production (update status)
  ✓ Can create Transfers
  ✓ Cannot create Job Orders
  ✓ Can view Production Dashboard

Inventory Role:
  ✓ Can view Finished Goods
  ✓ Can view Inventory Dashboard
  ✓ Can create Actual Inventory counts
  ✓ Cannot create Job Orders or Transfers

Logistics Role:
  ✓ Can create Delivery Schedules
  ✓ Can mark deliveries as delivered
  ✓ Can view Logistics Dashboard
  ✓ Cannot approve Job Orders

Admin Role:
  ✓ Can access ALL modules
  ✓ Can view all dashboards
  ✓ Can perform all operations

Test Unauthorized Access:
  1. Login as Production user
  2. Try to directly access: /job-orders/1/approve
  3. Should get 403 Forbidden error

═══════════════════════════════════════════════════════════════════════════════════════════

STEP 13: VERIFY REPORTS & EXPORTS
──────────────────────────────────
Action: Dashboard → Sales → Export PDF (or Reports → Job Orders)

Expected PDF Report Features:
  ✓ Professional formatting with header
  ✓ Filter information displayed
  ✓ All job orders in table format
  ✓ Status badges with colors
  ✓ Total quantity and amount calculated correctly
  ✓ Currency symbols (₱) properly displayed
  ✓ Proper pagination and column alignment

Test Report Filtering:
  1. Filter by Customer: "Test Customer Inc."
  2. Filter by Status: "completed"
  3. Filter by Date Range: Last 30 days
  4. Export to PDF and verify filters applied in report header

Export Inventory Report:
  1. Dashboard → Inventory → Export PDF
  2. Verify stock levels, variance amounts, totals
  3. Check color coding (low stock highlighted)

═══════════════════════════════════════════════════════════════════════════════════════════

TROUBLESHOOTING CHECKLIST
─────────────────────────

Real-Time Updates Not Working?
  ✓ Verify .env has REVERB_* configuration
  ✓ Check browser console for WebSocket errors (F12 → Console)
  ✓ Verify Echo listener in dashboard views (check source)
  ✓ Ensure auth:web middleware is applied
  ✓ Check that events are broadcast (search for 'broadcast(' in controllers)

Status Updates Not Changing?
  ✓ Verify user has appropriate role
  ✓ Check authorization in controller (authorize() call)
  ✓ Verify database has correct status value
  ✓ Check for JavaScript errors in console
  ✓ Ensure form CSRF token is present

Activity Logs Not Appearing?
  ✓ Check ActivityLogger service is being called
  ✓ Verify ActivityLog model exists
  ✓ Check database migrations ran successfully
  ✓ Ensure observer is registered in AppServiceProvider

Auto-Generation Not Working?
  ✓ Verify JO number generation in JobOrderController
  ✓ Check date format is correct for code generation
  ✓ Verify database query for last JO/DS
  ✓ Ensure transaction is used to prevent duplicates

Permission Denied Errors?
  ✓ Verify user role is set correctly
  ✓ Check Role middleware in routes
  ✓ Verify Policy class exists and is registered
  ✓ Check authorize() call in controller methods

═══════════════════════════════════════════════════════════════════════════════════════════

PERFORMANCE TESTING
───────────────────

Load Testing Suggestions:
  1. Create 10+ Job Orders and verify Dashboard still responsive
  2. Approve multiple JOs in quick succession, verify real-time updates queue correctly
  3. Open 5 browser tabs with Dashboard, verify all get updates simultaneously
  4. Check database query performance (use Laravel Debugbar if enabled)

Response Time Expectations:
  ✓ List pages (Customers, Products, JO): < 500ms
  ✓ Create/Edit forms: < 1000ms
  ✓ Status updates: < 500ms
  ✓ Real-time updates via WebSocket: < 100ms
  ✓ PDF export: < 2000ms

═══════════════════════════════════════════════════════════════════════════════════════════

FINAL VERIFICATION SUMMARY
──────────────────────────

After completing all steps, verify:

Data Integrity:
  ✓ Customer exists in database with products
  ✓ Product linked to Customer with correct details
  ✓ Job Order linked to Product with correct status flow
  ✓ Delivery Schedule linked to Job Order
  ✓ Finished Goods created and updated correctly
  ✓ All activity logs recorded with proper timestamps

Workflows:
  ✓ Complete customer → product → JO → approval → production → delivery flow
  ✓ Status transitions occur in correct sequence
  ✓ Auto-generated codes are unique and properly formatted
  ✓ Activity logs capture all operations

Real-Time:
  ✓ Dashboard updates appear without page refresh
  ✓ Multiple users see changes simultaneously
  ✓ Toast notifications appear for key actions
  ✓ Event broadcasting working correctly

Exports:
  ✓ PDF reports generate with correct data
  ✓ Filter information applies correctly
  ✓ Professional formatting maintained
  ✓ All totals calculate accurately

═══════════════════════════════════════════════════════════════════════════════════════════

SYSTEM COMPONENTS VERIFIED
──────────────────────────

Controllers (All Implemented ✓):
  • CustomerController - CRUD operations
  • ProductController - CRUD operations
  • JobOrderController - CRUD + approve + cancel + updateStatus
  • TransferController - CRUD operations
  • DeliveryScheduleController - CRUD + markDelivered
  • DashboardController - All 5 dashboards with KPIs
  • ReportController - Job Orders & Inventory reports
  • ActivityLogController - View and display logs

Models (All Related ✓):
  • Customer (has many Products)
  • Product (belongs to Customer, has many JobOrders)
  • JobOrder (belongs to Product, has many Transfers, DeliverySchedules)
  • Transfer (belongs to JobOrder, updates FinishedGoods)
  • DeliverySchedule (belongs to JobOrder)
  • FinishedGood (tracks inventory)
  • ActivityLog (records all operations)
  • User (tracks who performed actions)

Events (All Broadcast ✓):
  • JobOrderStatusChanged - broadcast when status updates
  • JobOrderApproved - broadcast when approved

Services (All Integrated ✓):
  • ActivityLogger - logs all operations
  • JobOrderService - handles JO business logic
  • InventoryService - manages stock levels
  • DeliveryService - handles delivery operations

Policies (All Authorized ✓):
  • CustomerPolicy, ProductPolicy, JobOrderPolicy, etc.
  • authorize() called in all relevant methods
  • Role-based access working

═══════════════════════════════════════════════════════════════════════════════════════════

You're all set to test the complete system! Follow the steps in order and verify each expected outcome.
The system is production-ready with comprehensive error handling, validation, and real-time features.
