<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'username' => 'admin', 
            'email' => 'admin@cpc.com',
            'password' => Hash::make('admin123'),
            'department' => 'admin',
        ]);

        $sales = User::create([
            'name' => 'Sales Employee',
            'username' => 'sales', 
            'email' => 'sales@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'sales',
        ]);

        User::create([
            'name' => 'Production Employee',
            'username' => 'production', 
            'email' => 'production@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'production',
        ]);

        User::create([
            'name' => 'Inventory Employee',
            'username' => 'inventory', 
            'email' => 'inventory@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'inventory',
        ]);

        User::create([
            'name' => 'Logistics Employee',
            'username' => 'logistics', 
            'email' => 'logistics@cpc.com',
            'password' => Hash::make('password123'),
            'department' => 'logistics',
        ]);
<<<<<<< HEAD

        // Seed sample data for testing
        $this->seedProducts();
        $this->seedJobOrders();
        $this->seedFinishedGoods();
        $this->seedActualInventories();
        $this->seedDeliverySchedules();
        $this->seedTransfers();
        
    }

    private function seedProducts(): void
    {
        Product::create([
            'customer' => 'ADAMAY INTERNATIONAL',
            'encoded_by_user_id' => 1,
            'product_code' => 'C-ADA-070-01-0CA',
            'model_name' => 'AIKAWA DR BOX',
            'description' => 'RSC BOX WITH PRINT',
            'date_encoded' => '2024-12-05',
            'specs' => 'C-175 S.T.S',
            'dimension' => '221 X 207 X 187 mm',
            'moq' => 500,
            'uom' => 'PC/S',
            'currency' => 'PHP',
            'selling_price' => 16.64,
            'rsqf_number' => '20-03219',
            'remarks' => 'AD-010760',
            'mc' => 11.08,
            'diff' => 5.56,
            'mu' => 0.501805054,
            'location' => 'CIP2',
            'pc' => 'CRA',
        ]);

        Product::create([
            'customer' => 'TRP INC.',
            'encoded_by_user_id' => 1,
            'product_code' => 'C-TRP-010-09-1EA',
            'model_name' => 'B13 HSC BOX',
            'description' => 'HSC BOX WITH PRINT',
            'date_encoded' => '2024-11-15',
            'specs' => 'STS',
            'dimension' => '710X543X80mm',
            'moq' => 100,
            'uom' => 'PC/S',
            'currency' => 'PHP',
            'selling_price' => 45.50,
            'rsqf_number' => '20-04521',
            'remarks' => null,
            'mc' => 32.00,
            'diff' => 13.50,
            'mu' => 0.42,
            'location' => 'CIP1',
            'pc' => 'TRP',
        ]);

        Product::create([
            'customer' => 'CAC PHILIPPINES INC.',
            'encoded_by_user_id' => 2,
            'product_code' => 'C-CAC-003-102-0CA',
            'model_name' => '9850000566200 BAG',
            'description' => 'ESD PE BAG - GUSSETED',
            'date_encoded' => '2024-06-24',
            'specs' => null,
            'dimension' => 'O.D. 630 X 420 X 755mm',
            'moq' => 1000,
            'uom' => 'PC/S',
            'currency' => 'PHP',
            'selling_price' => 13.20,
            'rsqf_number' => '19-08765',
            'remarks' => null,
            'mc' => 9.80,
            'diff' => 3.40,
            'mu' => 0.347,
            'location' => 'CIP3',
            'pc' => 'RSP',
        ]);
    }

    private function seedJobOrders(): void
    {
        JobOrder::create([
            'jo_number' => 'C-25-10422',
            'status' => 'approved',
            'fulfillment_status' => 'full',
            'date_needed' => '2024-12-02',
            'po_number' => 'PO0000061501',
            'product_id' => 2,
            'qty_ordered' => 10,
            'encoded_by_user_id' => 1,
            'remarks' => null,
            'qty_balance' => 0,
            'qty_transferred_to_ppqc' => 10,
            'qty_in_delivery_schedule' => 20,
            'withdrawal_status' => 'approved',
            'withdrawal_number' => 'DEC0020',
            'week_number' => 49,
            'date_encoded' => '2024-11-20',
            'date_approved' => '2024-11-21',
        ]);

        JobOrder::create([
            'jo_number' => 'C-25-9747',
            'status' => 'in_progress',
            'fulfillment_status' => 'balance',
            'date_needed' => '2024-12-04',
            'po_number' => '84303',
            'product_id' => 3,
            'qty_ordered' => 40,
            'encoded_by_user_id' => 2,
            'remarks' => 'W/ URGENT LIST 12/04-12NN',
            'qty_balance' => 90,
            'qty_transferred_to_ppqc' => null,
            'qty_in_delivery_schedule' => 40,
            'withdrawal_status' => 'with_fg_stocks',
            'withdrawal_number' => null,
            'week_number' => 49,
            'date_encoded' => '2024-12-02',
            'date_approved' => '2024-12-02',
        ]);

        JobOrder::create([
            'jo_number' => 'C-25-9731',
            'status' => 'approved',
            'fulfillment_status' => 'balance',
            'date_needed' => '2024-12-03',
            'po_number' => 'PO0000062145',
            'product_id' => 1,
            'qty_ordered' => 77,
            'encoded_by_user_id' => 1,
            'remarks' => 'FG EFREN',
            'qty_balance' => 357,
            'qty_transferred_to_ppqc' => 77,
            'qty_in_delivery_schedule' => null,
            'withdrawal_status' => 'approved',
            'withdrawal_number' => null,
            'week_number' => 49,
            'date_encoded' => '2024-11-28',
            'date_approved' => '2024-11-29',
        ]);
    }

    private function seedFinishedGoods(): void
    {
        FinishedGood::updateOrCreate([
            'product_id' => 1,
        ], [
            'qty_pc_area' => null,
            'qty_beginning' => 0,
            'qty_in' => 0,
            'qty_out' => 0,
            'qty_theoretical_ending' => 0,
            'remarks' => null,
            'qty_buffer_stock' => 0,
            'amount_beginning' => 0.00,
            'amount_in' => 0.00,
            'amount_out' => 0.00,
            'amount_ending' => 0.00,
            'qty_actual_ending' => 0,
            'qty_variance' => 0,
            'amount_variance' => 0.00,
            'date_last_in' => null,
            'date_oldest' => null,
            'days_aging' => 0,
            'aging_1_30_days' => 0,
            'aging_31_60_days' => 0,
            'aging_61_90_days' => 0,
            'aging_91_120_days' => 0,
            'aging_over_120_days' => 0,
        ]);

        FinishedGood::updateOrCreate([
            'product_id' => 2,
        ], [
            'qty_pc_area' => null,
            'qty_beginning' => 0,
            'qty_in' => 10,
            'qty_out' => 10,
            'qty_theoretical_ending' => 0,
            'remarks' => null,
            'qty_buffer_stock' => 5,
            'amount_beginning' => 0.00,
            'amount_in' => 455.00,
            'amount_out' => 455.00,
            'amount_ending' => 0.00,
            'qty_actual_ending' => 0,
            'qty_variance' => 0,
            'amount_variance' => 0.00,
            'date_last_in' => '2024-11-25',
            'date_oldest' => '2024-11-25',
            'days_aging' => 5,
            'aging_1_30_days' => 0,
            'aging_31_60_days' => 0,
            'aging_61_90_days' => 0,
            'aging_91_120_days' => 0,
            'aging_over_120_days' => 0,
        ]);

        FinishedGood::updateOrCreate([
            'product_id' => 3,
        ], [
            'qty_pc_area' => 179,
            'qty_beginning' => 0,
            'qty_in' => 5000,
            'qty_out' => 0,
            'qty_theoretical_ending' => 5000,
            'remarks' => null,
            'qty_buffer_stock' => 0,
            'amount_beginning' => 0.00,
            'amount_in' => 66000.00,
            'amount_out' => 0.00,
            'amount_ending' => 66000.00,
            'qty_actual_ending' => -5000,
            'qty_variance' => -66000,
            'amount_variance' => -5000.00,
            'date_last_in' => '2024-12-02',
            'date_oldest' => '2024-06-24',
            'days_aging' => 54,
            'aging_1_30_days' => 0,
            'aging_31_60_days' => 5000,
            'aging_61_90_days' => 0,
            'aging_91_120_days' => 0,
            'aging_over_120_days' => 0,
        ]);
    }

    private function seedActualInventories(): void
    {
        ActualInventory::create([
            'tag_number' => 'TAG-2024-001',
            'product_id' => 1,
            'qty_counted' => 500,
            'location' => 'CIP2',
            'counted_by_user_id' => 2,
            'verified_by_user_id' => 3,
            'remarks' => 'Initial count',
        ]);

        ActualInventory::create([
            'tag_number' => 'TAG-2024-002',
            'product_id' => 2,
            'qty_counted' => 150,
            'location' => 'CIP1',
            'counted_by_user_id' => 1,
            'verified_by_user_id' => 2,
            'remarks' => null,
        ]);

        ActualInventory::create([
            'tag_number' => 'TAG-2024-003',
            'product_id' => 3,
            'qty_counted' => 5000,
            'location' => 'CIP3',
            'counted_by_user_id' => 3,
            'verified_by_user_id' => 1,
            'remarks' => 'Large quantity verified',
        ]);
    }

    private function seedDeliverySchedules(): void
    {
        DeliverySchedule::create([
            'delivery_code' => '45995',
            'status' => 'urgent',
            'delivery_date' => '2024-12-04',
            'job_order_id' => 2,
            'product_id' => 3,
            'qty_scheduled' => 40,
            'remarks' => 'W/ URGENT LIST 12/04-12NN',
            'pmp_commitment' => 'WITH RM COMPLETE',
            'ppqc_commitment' => null,
            'qty_fg_stocks' => 0,
            'delivery_remarks' => 'BACKLOG',
            'jo_remarks' => 'Balance',
            'ppqc_status' => 'Urgent',
            'qty_jo_balance' => 90,
            'qty_transferred' => 0,
            'qty_delivered' => 0,
            'week_number' => 49,
            'date_encoded' => '2024-12-02',
            'qty_max' => 40,
            'qty_buffer_stock' => 0,
            'qty_backlog' => 0,
        ]);

        DeliverySchedule::create([
            'delivery_code' => 'DS-2024-002',
            'status' => 'complete',
            'delivery_date' => '2024-12-02',
            'job_order_id' => 1,
            'product_id' => 2,
            'qty_scheduled' => 10,
            'remarks' => null,
            'pmp_commitment' => 'COMPLETED',
            'ppqc_commitment' => 'DONE',
            'qty_fg_stocks' => 0,
            'delivery_remarks' => 'Delivered on time',
            'jo_remarks' => null,
            'ppqc_status' => 'Approved',
            'qty_jo_balance' => 0,
            'qty_transferred' => 10,
            'qty_delivered' => 10,
            'week_number' => 49,
            'date_encoded' => '2024-11-20',
            'qty_max' => 10,
            'qty_buffer_stock' => 5,
            'qty_backlog' => 0,
        ]);

        DeliverySchedule::create([
            'delivery_code' => 'DS-2024-003',
            'status' => 'pending',
            'delivery_date' => '2024-12-05',
            'job_order_id' => 3,
            'product_id' => 1,
            'qty_scheduled' => 77,
            'remarks' => 'FG EFREN',
            'pmp_commitment' => 'IN PROGRESS',
            'ppqc_commitment' => null,
            'qty_fg_stocks' => 50,
            'delivery_remarks' => null,
            'jo_remarks' => 'Balance',
            'ppqc_status' => 'Pending',
            'qty_jo_balance' => 357,
            'qty_transferred' => 0,
            'qty_delivered' => 0,
            'week_number' => 49,
            'date_encoded' => '2024-11-28',
            'qty_max' => 77,
            'qty_buffer_stock' => 20,
            'qty_backlog' => 0,
        ]);
    }

    private function seedTransfers(): void
    {
        Transfer::create([
            'ptt_number' => 'PTT 25-25422',
            'section' => 'IMPORTED',
            'date_transferred' => '2024-12-02',
            'job_order_id' => 3,
            'qty_transferred' => 77,
            'date_delivery_scheduled' => '2024-12-03',
            'remarks' => 'FG EFREN',
            'time_transferred' => '16:50:00',
            'status' => 'balance',
            'qty_jo_balance' => 357,
            'product_id' => 1,
            'grade' => '2 WAY',
            'dimension' => 'AB-FLUTE O.D. 1130 X960mm',
            'received_by_user_id' => 2,
            'date_received' => '2024-12-03',
            'time_received' => '14:31:00',
            'qty_received' => 77,
            'jit_days' => 1,
            'delivery_schedule_status' => 'Urgent',
            'week_number' => 49,
            'category' => 'IMPORTED',
            'unit_selling_price' => 661.67,
            'total_amount' => 50948.59,
        ]);

        Transfer::create([
            'ptt_number' => 'PTT 25-25423',
            'section' => 'LOCAL',
            'date_transferred' => '2024-11-25',
            'job_order_id' => 1,
            'qty_transferred' => 10,
            'date_delivery_scheduled' => '2024-12-02',
            'remarks' => null,
            'time_transferred' => '10:30:00',
            'status' => 'complete',
            'qty_jo_balance' => 0,
            'product_id' => 2,
            'grade' => 'A',
            'dimension' => '710X543X80mm',
            'received_by_user_id' => 3,
            'date_received' => '2024-11-25',
            'time_received' => '11:00:00',
            'qty_received' => 10,
            'jit_days' => 0,
            'delivery_schedule_status' => 'Complete',
            'week_number' => 49,
            'category' => 'LOCAL',
            'unit_selling_price' => 45.50,
            'total_amount' => 455.00,
        ]);

        Transfer::create([
            'ptt_number' => 'PTT 25-25424',
            'section' => 'IMPORTED',
            'date_transferred' => '2024-12-02',
            'job_order_id' => 2,
            'qty_transferred' => 5000,
            'date_delivery_scheduled' => '2024-12-04',
            'remarks' => 'Large order - urgent',
            'time_transferred' => '14:00:00',
            'status' => 'balance',
            'qty_jo_balance' => 90,
            'product_id' => 3,
            'grade' => 'B',
            'dimension' => 'O.D. 630 X 420 X 755mm',
            'received_by_user_id' => 1,
            'date_received' => '2024-12-02',
            'time_received' => '15:30:00',
            'qty_received' => 5000,
            'jit_days' => 0,
            'delivery_schedule_status' => 'Urgent',
            'week_number' => 49,
            'category' => 'IMPORTED',
            'unit_selling_price' => 13.20,
            'total_amount' => 66000.00,
        ]);
=======
>>>>>>> parent of 4b3ed60 (enhanced the chatbot capability)
    }
}