# Complete System End-to-End Testing Guide
**Status**: Ready for Manual Testing
**Date**: February 9, 2026

---

## 🔐 Test Users & Login

### Step 1: Login
Go to: `http://localhost/login`

Use any of these credentials:
- **Admin**: admin / admin123 (Full access to everything)
- **Sales**: sales / password123 (Can approve job orders)
- **Production**: production / password123 (Can mark production complete)
- **Inventory**: inventory / password123 (Can verify inventory)
- **Logistics**: logistics / password123 (Can approve/complete endorsements)

**Expected**: You should be redirected to `/dashboard` after login ✅

---

## 📊 Dashboard Overview (After Login)

### View Dashboard
Go to: `http://localhost/dashboard`

You should see:
- [ ] Sales Dashboard link
- [ ] Production Dashboard link
- [ ] Inventory Dashboard link
- [ ] Logistics Dashboard link

---

## 🛍️ Step 1: Create Product (Admin or Sales User)

**Go to**: `http://localhost/products/create`

**Fill in Form**:
```
Product Code: TEST-PROD-001
Product Name: Test Product
Model Name: TP-2026-V1
Customer Name: Test Customer
Description: Test product for workflow verification
UOM: PC/S
Selling Price: 1000.00 (optional)
Dimension: 10x10x10 (optional)
```

**Expected**: 
- ✅ Form submits successfully
- ✅ Redirected to `/products`
- ✅ New product appears in the list with all fields filled

**Verification**:
```sql
SELECT * FROM products WHERE product_code = 'TEST-PROD-001';
-- Should show your new product
```

---

## 📋 Step 2: Create Job Order (Sales User)

**Go to**: `http://localhost/job-orders/create`

**Fill in Form**:
```
Product: TEST-PROD-001 (select from dropdown)
PO Number: PO-2026-001
Quantity: 100
Delivery Needed By: (pick a date, e.g., 2026-02-20)
```

**Expected**:
- ✅ Form submits successfully
- ✅ Redirected to `/job-orders`
- ✅ New job order appears with:
  - Auto-generated `jo_number` (e.g., JO-2026-0001)
  - Status: "Pending"

**Verification**:
```sql
SELECT jo_number, jo_status, quantity FROM job_orders ORDER BY created_at DESC LIMIT 1;
-- Should show: JO-2026-XXXX | Pending | 100
```

---

## ✔️ Step 3: Approve Job Order (Sales Dashboard)

**Go to**: `http://localhost/dashboard/sales`

**Expected Screen Shows**:
- [ ] Number of pending job orders
- [ ] Number of approved job orders
- [ ] List of "Recent Job Orders"

**Find Your Job Order**:
- [ ] Look for JO-2026-0001 in the list
- [ ] Should show status "Pending"
- [ ] Should have an "Approve" button

**Click Approve**:
- [ ] Click the "Approve" button on your job order
- [ ] Should see success message

**Expected After Approval**:
- ✅ Job order status changes to "Approved"
- ✅ `approved_at` is set to current time
- ✅ `approved_by` is set to your user ID

**Verification**:
```sql
SELECT jo_number, jo_status, date_approved FROM job_orders 
WHERE jo_number = 'JO-2026-0001';
-- Should show: JO-2026-0001 | Approved | 2026-02-09 XX:XX:XX
```

---

## ❌ Step 4: Try Create Delivery Schedule (Will Fail - Expected)

**Go to**: `http://localhost/delivery-schedules/create`

**Try to Create**:
- Select approved job order from dropdown
- Fill in quantity: 50
- Fill in delivery date

**Click Create**:
- Should get **ERROR**: "Cannot create delivery: No verified inventory available for this product"

**Why it fails**: 
- ✅ Correct! Stock validation is working
- FinishedGoods hasn't been created yet
- Inventory hasn't been verified

---

## ⚙️ Step 5: Mark Production Complete (Production Dashboard)

**Go to**: `http://localhost/dashboard/production`

**Expected Screen Shows**:
- [ ] "Pending Production" count
- [ ] "Produced Today" count
- [ ] "Backlog Quantity"
- [ ] "Awaiting Jobs" table showing approved but incomplete jobs

**Find Your Job Order**:
- [ ] Look for JO-2026-0001 in "Awaiting Jobs" table
- [ ] Should show status "Approved"

**Update Status to Complete**:
- [ ] Click on the job order (or find an edit button)
- [ ] Look for status dropdown / "Mark Complete" button
- [ ] Select status "JO Full" or click "Mark Complete"
- [ ] Submit

**Expected After Submit**:
- ✅ Job order status changes to "JO Full"
- ✅ Success message appears
- ✅ FinishedGood record is **AUTO-CREATED** with `current_qty = 0`

**Verification**:
```sql
SELECT jo_number, jo_status FROM job_orders WHERE jo_number = 'JO-2026-0001';
-- Should show: JO-2026-0001 | JO Full

SELECT product_code, current_qty FROM finished_goods 
WHERE product_id = (SELECT product_id FROM job_orders WHERE jo_number = 'JO-2026-0001');
-- Should show: TEST-PROD-001 | 0
```

---

## 📦 Step 6: Create Actual Inventory Count (Inventory Dashboard)

**Go to**: `http://localhost/actual-inventories/create`

**Fill in Form**:
```
Product: TEST-PROD-001 (select from dropdown)
FG Quantity: 100 (this is the verified count)
Counted By: (auto-filled, can edit)
Counted Date: (today)
```

**Click Create**:

**Expected**:
- ✅ Form submits successfully
- ✅ Redirected to `/actual-inventories`
- ✅ New record appears with:
  - Auto-generated `tag_number` (e.g., TAG-2026-000001)
  - Status: "Pending"
  - FG Quantity: 100

**Verification**:
```sql
SELECT tag_number, fg_quantity, status FROM actual_inventories ORDER BY created_at DESC LIMIT 1;
-- Should show: TAG-2026-XXXXXX | 100 | Pending
```

---

## 🔍 Step 7: Verify Inventory (Instant Operation) ⚡

**Go to**: `http://localhost/dashboard/inventory`

**Expected Screen Shows**:
- [ ] "Stocks On Hand"
- [ ] "Low Stock Items"
- [ ] "Stock In Today"
- [ ] **"Pending Inventory Verification"** section (contains your inventory)

**Find Your Inventory Count**:
- [ ] Look for your tag number in "Pending Inventory Verification"
- [ ] Should show product code, quantity, status

**Click Verify Button**:
- [ ] Find the "Verify" button
- [ ] Modal pops up showing quantity (100)
- [ ] Click "Confirm" in modal

**⏱️ IMPORTANT - Time this operation**:
- [ ] Should complete in **< 2 seconds** (ideally instant)
- [ ] Not 60 seconds ✅

**Expected After Verification**:
- ✅ Status changes to "Verified"
- ✅ FinishedGood `current_qty` is updated to 100 (atomic operation)
- ✅ EndorseToLogistic record is **AUTO-CREATED** with status='pending'

**Verification**:
```sql
-- Check ActualInventory
SELECT tag_number, status FROM actual_inventories WHERE fg_quantity = 100;
-- Should show: TAG-2026-XXXXXX | Verified

-- Check FinishedGood updated
SELECT product_code, current_qty FROM finished_goods WHERE product_code = 'TEST-PROD-001';
-- Should show: TEST-PROD-001 | 100

-- Check EndorseToLogistic auto-created
SELECT etl_code, product_code, status FROM endorse_to_logistics 
WHERE product_code = 'TEST-PROD-001' ORDER BY created_at DESC LIMIT 1;
-- Should show: ETL-XXXXXXXX-XXXX | TEST-PROD-001 | pending
```

---

## ✅ Step 8: Now Create Delivery Schedule (Should Succeed!)

**Go to**: `http://localhost/delivery-schedules/create`

**Fill in Form**:
```
Job Order: JO-2026-0001 (from dropdown)
Quantity: 50
Delivery Date: 2026-02-20
```

**Expected**:
- ✅ Form auto-fills these fields:
  - Product Code: TEST-PROD-001
  - Customer Name: Test Customer
  - Model Name: TP-2026-V1
  - Description: [your description]
  - UOM: PC/S
  - FG Stocks: 100
- ✅ Submit succeeds (no stock validation error)
- ✅ Auto-generated `ds_code` (e.g., 2026C-DS-001)
- ✅ Status: "ON SCHEDULE"

**Verification**:
```sql
SELECT ds_code, ds_status, quantity, fg_stocks FROM delivery_schedules ORDER BY created_at DESC LIMIT 1;
-- Should show: 2026C-DS-001 | ON SCHEDULE | 50 | 100
```

---

## 🚚 Step 9: Check Logistics Dashboard - Pending Endorsements

**Go to**: `http://localhost/dashboard/logistics`

**Expected Screen Shows**:
- [ ] KPI cards: Deliveries Today, Pending, Delayed, Completed
- [ ] **"Pending Endorsements"** section

**Find Your Endorsement**:
- [ ] In "Pending Endorsements", should see ETL record with:
  - Product Code: TEST-PROD-001
  - Customer: Test Customer
  - Status: pending
  - **"Approve" button**

**Expected**:
- ✅ EndorseToLogistic visible with all product details
- ✅ "Approve" button is clickable

---

## 👍 Step 10: Approve Endorsement (Logistics Dashboard)

**On Dashboard**:
- [ ] Find your pending endorsement in "Pending Endorsements"
- [ ] Click "Approve" button

**Expected After Approval**:
- ✅ Success message appears
- ✅ Endorsement disappears from "Pending Endorsements"
- ✅ Endorsement appears in **"Approved Endorsements"** section
- ✅ Status changes to "approved"

**Verification**:
```sql
SELECT etl_code, status, approved_at FROM endorse_to_logistics 
WHERE product_code = 'TEST-PROD-001' ORDER BY created_at DESC LIMIT 1;
-- Should show: ETL-XXXXXXXX | approved | 2026-02-09 XX:XX:XX
```

---

## ✨ Step 11: Complete Endorsement (Final Step)

**On Dashboard**:
- [ ] Find your approved endorsement in **"Approved Endorsements"**
- [ ] Click "Complete" button

**Expected After Completion**:
- ✅ Success message appears
- ✅ Endorsement disappears from active sections
- ✅ Status changes to "completed"
- ✅ **Workflow is COMPLETE** ✅

**Verification**:
```sql
SELECT etl_code, status, completed_at FROM endorse_to_logistics 
WHERE product_code = 'TEST-PROD-001' ORDER BY created_at DESC LIMIT 1;
-- Should show: ETL-XXXXXXXX | completed | 2026-02-09 XX:XX:XX
```

---

## 📊 Complete Workflow Checklist

**Test Completed Successfully** ✅ if all items are checked:

- [ ] Step 1: Product created
- [ ] Step 2: JobOrder created with Pending status
- [ ] Step 3: JobOrder approved with status change
- [ ] Step 4: Delivery schedule creation fails (stock validation works)
- [ ] Step 5: Production marked complete, FG auto-created with qty=0
- [ ] Step 6: ActualInventory created with Pending status
- [ ] Step 7: Inventory verified **in <2 seconds**, FG qty updated to 100, ETL auto-created
- [ ] Step 8: Delivery schedule creation succeeds
- [ ] Step 9: EndorseToLogistic visible on logistics dashboard
- [ ] Step 10: Endorsement approved (moves to approved section)
- [ ] Step 11: Endorsement completed (disappears from active sections)

---

## 🎯 Success Criteria

✅ **All systems working** if:

1. **Performance** ✅
   - Verification completes in < 2 seconds (not 60 seconds)
   - Dashboards load smoothly
   - No slow queries

2. **Workflow** ✅
   - All 11 steps complete without errors
   - Auto-fill features work
   - Status transitions correct

3. **Database** ✅
   - All verification SQLs return expected results
   - No duplicate records
   - Foreign keys intact

4. **Authorization** ✅
   - Sales can approve
   - Production can update status
   - Inventory can verify
   - Logistics can approve/complete

---

## 🐛 Troubleshooting

### If Verification Hangs (>2 seconds)
- Check database logs for slow queries
- Run: `php artisan config:clear`
- Restart PHP server

### If Delivery Creation Still Fails
- Verify FinishedGood was created: `SELECT * FROM finished_goods WHERE product_code = 'TEST-PROD-001';`
- Check current_qty > 0
- Verify ActualInventory status = 'Verified'

### If Endorsement Not Auto-Created
- Check ActualInventory status is 'Verified'
- Check database logs for errors in markAsVerified()
- Run verification again

### If Approve/Complete Fails
- Check you're logged in as logistics user
- Check EndorseToLogistic record exists
- Refresh page and try again

---

## 📝 Notes

- All timestamps use server timezone
- All `encoded_by` / `approved_by` / `completed_by` fields use logged-in user ID
- Auto-generated codes are UUIDs or sequential
- Test can be repeated with different products/users
- Each step is **independent** - later steps don't affect earlier ones

---

## 🚀 Next Steps After Testing

1. **If all tests pass** ✅
   - System is production-ready
   - Deploy to staging for UAT
   - Create system documentation

2. **If issues found** 🐛
   - Note the exact step where it fails
   - Check database / logs for errors
   - Report with error messages and screenshots

---

**Total Estimated Test Time**: 15-20 minutes  
**Difficulty Level**: Easy (mostly clicking buttons)  
**Success Rate Expected**: 100% ✅

---

**Good luck with testing!** 🎉

If you encounter any errors, screenshot the error message and let me know the exact step where it occurred.
