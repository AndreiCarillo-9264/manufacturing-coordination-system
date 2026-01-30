QUICK START GUIDE
═════════════════════════════════════════════════════════════════════════════════════════════════

FOR IMMEDIATE TESTING - FOLLOW THIS 10-MINUTE FLOW
────────────────────────────────────────────────────

PREREQUISITES:
  ✓ Laravel running on http://localhost:8000 (or your server)
  ✓ Database migrated (php artisan migrate)
  ✓ Logged in as Admin user
  ✓ WebSocket (Reverb) running in background

═════════════════════════════════════════════════════════════════════════════════════════════════

1. CREATE CUSTOMER (1 minute)
────────────────────────────

  Step 1: Navigate to Admin → Master Data → Customers
  Step 2: Click "Create New" button
  Step 3: Fill form:
    • Name: "ABC Manufacturing Co."
  Step 4: Click "Save"
  
  ✓ Expected: Customer appears in list
  ✓ Check: Activity log shows "Customer created"


2. CREATE PRODUCT (1 minute)
────────────────────────────

  Step 1: Navigate to Admin → Master Data → Products
  Step 2: Click "Create New" button
  Step 3: Fill required fields:
    • Customer: "ABC Manufacturing Co." (from Step 1)
    • Product Code: "PROD-001"
    • Model Name: "Widget Pro X1"
    • Description: "High-performance widget"
    • Date Encoded: Today
    • UoM: "PCS"
    • MOQ: "10"
    • Selling Price: "1,500.00"
  Step 4: Click "Save"
  
  ✓ Expected: Product appears in list
  ✓ Check: Product linked to customer


3. CREATE JOB ORDER (1 minute)
──────────────────────────────

  Step 1: Navigate to Sales/Admin → Job Orders → Create New
  Step 2: Fill form:
    • Product: "Widget Pro X1"
    • PO Number: "PO-TEST-001"
    • Quantity: "50"
    • UoM: "PCS"
    • Date Needed: "2026-02-15" (future date)
  Step 3: Click "Save"
  
  ✓ Expected: JO created with status "pending" (yellow badge)
  ✓ Auto-generated: JO-2026-00001
  ✓ Check: Sales Dashboard → Pending count increased


4. APPROVE JOB ORDER (1 minute)
───────────────────────────────

  Step 1: Go to Dashboard → Sales
  Step 2: Find your Job Order in the list
  Step 3: Click "Approve" button
  Step 4: Confirm when prompted
  
  ✓ Expected: Status changes to "approved" (blue badge)
  ✓ Real-time: KPI updates immediately without refresh
  ✓ Toast: "Job order approved" notification appears
  ✓ Check: Activity log entry created


5. START PRODUCTION (1 minute)
──────────────────────────────

  Step 1: Go to Dashboard → Production
  Step 2: Find your Job Order in the list (status: approved)
  Step 3: Click "Start Production" button
  
  ✓ Expected: Status changes to "in_progress" (purple badge)
  ✓ Real-time: Button changes to "Complete"
  ✓ Toast: "Job order status updated to in_progress"
  ✓ Check: Dashboard KPI updates


6. COMPLETE PRODUCTION (1 minute)
─────────────────────────────────

  Step 1: Still on Dashboard → Production
  Step 2: Find your Job Order with "Complete" button
  Step 3: Click "Complete" button
  
  ✓ Expected: Status changes to "completed" (green badge)
  ✓ Real-time: Job order removed from active list
  ✓ Toast: "Job order status updated to completed"
  ✓ Check: Finished Goods created automatically


7. CREATE DELIVERY SCHEDULE (1 minute)
──────────────────────────────────────

  Step 1: Navigate to Logistics → Delivery Schedules → Create New
  Step 2: Select Job Order:
    • Choose your completed Job Order
  Step 3: Fields should auto-populate:
    • Product: Auto-filled
    • Quantity: Auto-filled (50)
    • UoM: Auto-filled (PCS)
  Step 4: Set delivery date:
    • Date: "2026-02-20" (after date needed)
  Step 5: Click "Save"
  
  ✓ Expected: Delivery Schedule created with status "pending"
  ✓ Auto-generated: DS-2026-00001
  ✓ Check: Logistics Dashboard → Pending count increased


8. MARK DELIVERY COMPLETE (1 minute)
────────────────────────────────────

  Step 1: Go to Dashboard → Logistics
  Step 2: Find your Delivery Schedule in the recent list
  Step 3: Click "Mark Delivered" button
  
  ✓ Expected: Status changes to "delivered" (green badge)
  ✓ Real-time: KPI updates immediately
  ✓ Toast: "Delivery marked as delivered"
  ✓ Check: Pending count -1, Completed count +1


9. VERIFY ACTIVITY LOG (1 minute)
─────────────────────────────────

  Step 1: Go to Admin → Activity Logs
  Step 2: Should see entries (newest first):
    • "Delivery Schedule marked as delivered"
    • "Delivery Schedule created"
    • "Job Order Status Updated" (3 entries)
    • "Job Order Approved"
    • "Job Order created"
    • "Product created"
    • "Customer created"
  
  ✓ Each entry should have: User, Action, Entity, Timestamp
  ✓ Status updates should show Old/New values


10. EXPORT REPORT (1 minute)
────────────────────────────

  Step 1: Go to Dashboard → Sales (or Reports → Job Orders)
  Step 2: Click "Export PDF" button
  Step 3: PDF should download with filename:
    • "job-orders-report-2026-01-29.pdf"
  
  ✓ Expected: Professional PDF with:
    • Your Job Order in the table
    • Status badge with color
    • Total quantity (50)
    • Total amount (₱75,000)
  ✓ Check: All formatting looks good


═════════════════════════════════════════════════════════════════════════════════════════════════

WHAT TO VERIFY IN REAL-TIME TESTING
────────────────────────────────────

REAL-TIME UPDATES:
  ✓ Open Dashboard → Sales in Browser Tab A
  ✓ Open Dashboard → Production in Browser Tab B
  ✓ In Tab A: Click Approve button
  ✓ In Tab B: Watch for automatic KPI updates (no refresh needed)
  ✓ If not updating: Check browser console (F12) for WebSocket errors

TOAST NOTIFICATIONS:
  ✓ Should appear at bottom-right when actions complete
  ✓ Should show action result (success/error)
  ✓ Should auto-dismiss after 3-4 seconds
  ✓ If not appearing: Check JavaScript console for errors

BUTTON STATES:
  ✓ "Start Production" button visible when status = approved
  ✓ "Complete" button visible when status = in_progress
  ✓ "Mark Delivered" button visible when status = pending
  ✓ Buttons disabled or hidden when not applicable

STATUS BADGES:
  ✓ Pending = Yellow
  ✓ Approved = Blue
  ✓ In Progress = Purple
  ✓ Completed = Green

DASHBOARD UPDATES:
  ✓ KPI cards update in real-time
  ✓ Table data refreshes without page load
  ✓ Multiple windows receive same updates
  ✓ No lag or delay (< 200ms)


═════════════════════════════════════════════════════════════════════════════════════════════════

TROUBLESHOOTING QUICK FIXES
────────────────────────────

WebSocket Not Connecting:
  Fix 1: Verify Reverb is running (check console output)
  Fix 2: Check browser console (F12 → Console tab)
  Fix 3: Refresh page and try again

Status Not Updating:
  Fix 1: Ensure user has correct role (production for status updates)
  Fix 2: Check that you clicked the correct button
  Fix 3: Verify page didn't have errors (check console)

Real-Time Not Working:
  Fix 1: Open new browser window
  Fix 2: Check WebSocket connection in console
  Fix 3: Restart Reverb server

PDF Export Error:
  Fix 1: Try exporting with no filters first
  Fix 2: Check file permissions on storage directory
  Fix 3: Verify enough disk space available

Permission Denied Error:
  Fix 1: Verify user role matches required role
  Fix 2: Refresh page and log back in
  Fix 3: Check user permissions in database


═════════════════════════════════════════════════════════════════════════════════════════════════

KEY ENDPOINTS REFERENCE
───────────────────────

DASHBOARDS:
  • http://localhost:8000/dashboard/sales
  • http://localhost:8000/dashboard/production
  • http://localhost:8000/dashboard/logistics
  • http://localhost:8000/dashboard/inventory

MASTERS:
  • http://localhost:8000/customers
  • http://localhost:8000/products

JOB ORDERS:
  • http://localhost:8000/job-orders
  • http://localhost:8000/job-orders/create
  • http://localhost:8000/job-orders/{id}

REPORTS:
  • http://localhost:8000/reports/job-orders
  • http://localhost:8000/reports/inventory

ACTIVITY:
  • http://localhost:8000/activity-logs


═════════════════════════════════════════════════════════════════════════════════════════════════

EXPECTED TIME: 10 minutes
SUCCESS RATE: 100% (if all components are set up correctly)

After completing this flow, refer to:
  📖 SYSTEM_TEST_GUIDE.md - Detailed testing instructions
  📖 API_REFERENCE.md - Complete endpoint documentation
  📖 IMPLEMENTATION_CHECKLIST.md - Feature checklist
  📖 VISUAL_GUIDE.md - Diagrams and visual explanations

═════════════════════════════════════════════════════════════════════════════════════════════════
