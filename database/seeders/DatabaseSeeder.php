<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\ActualInventory;
use App\Models\DeliverySchedule;
use App\Models\Transfer;
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
            'remarks_po' => 'AD-010760',
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
            'remarks_po' => null,
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
            'remarks_po' => null,
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
            'jo_status' => 'jo_full',
            'date_needed' => '2024-12-02',
            'po_number' => 'PO0000061501',
            'product_id' => 2,
            'qty' => 10,
            'uom' => 'PC/S',
            'encoded_by_user_id' => 1,
            'remarks' => null,
            'jo_balance' => 0,
            'ppqc_transfer' => 10,
            'ds_quantity' => 20,
            'withdrawal' => 'approved',
            'withdrawal_number' => 'DEC0020',
            'week_number' => 49,
            'date_encoded' => '2024-11-20',
            'date_approved' => '2024-11-21',
        ]);

        JobOrder::create([
            'jo_number' => 'C-25-9747',
            'status' => 'in_progress',
            'jo_status' => 'balance',
            'date_needed' => '2024-12-04',
            'po_number' => '84303',
            'product_id' => 3,
            'qty' => 40,
            'uom' => 'SET/S',
            'encoded_by_user_id' => 2,
            'remarks' => 'W/ URGENT LIST 12/04-12NN',
            'jo_balance' => 90,
            'ppqc_transfer' => null,
            'ds_quantity' => 40,
            'withdrawal' => 'with_fg_stocks',
            'withdrawal_number' => null,
            'week_number' => 49,
            'date_encoded' => '2024-12-02',
            'date_approved' => '2024-12-02',
        ]);

        JobOrder::create([
            'jo_number' => 'C-25-9731',
            'status' => 'approved',
            'jo_status' => 'balance',
            'date_needed' => '2024-12-03',
            'po_number' => 'PO0000062145',
            'product_id' => 1,
            'qty' => 77,
            'uom' => 'PC/S',
            'encoded_by_user_id' => 1,
            'remarks' => 'FG EFREN',
            'jo_balance' => 357,
            'ppqc_transfer' => 77,
            'ds_quantity' => null,
            'withdrawal' => 'approved',
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
            'count_pc_area' => null,
            'beg' => 0,
            'in_qty' => 0,
            'out_qty' => 0,
            'theo_end' => 0,
            'remarks' => null,
            'buffer_stocks' => 0,
            'cur_sell_price' => 16.64,
            'beg_amt' => 0.00,
            'in_amt' => 0.00,
            'out_amt' => 0.00,
            'end_amt' => 0.00,
            'ending_count' => 0,
            'uom3' => 'PC/S',
            'variance_count' => 0,
            'variance_amount' => 0.00,
            'last_in_date' => null,
            'older_date' => null,
            'days' => 0,
            'range_1_30' => 0,
            'range_31_60' => 0,
            'range_61_90' => 0,
            'range_91_120' => 0,
            'range_over_120' => 0,
        ]);

        FinishedGood::updateOrCreate([
            'product_id' => 2,
        ], [
            'count_pc_area' => null,
            'beg' => 0,
            'in_qty' => 10,
            'out_qty' => 10,
            'theo_end' => 0,
            'remarks' => null,
            'buffer_stocks' => 5,
            'cur_sell_price' => 45.50,
            'beg_amt' => 0.00,
            'in_amt' => 455.00,
            'out_amt' => 455.00,
            'end_amt' => 0.00,
            'ending_count' => 0,
            'uom3' => 'PC/S',
            'variance_count' => 0,
            'variance_amount' => 0.00,
            'last_in_date' => '2024-11-25',
            'older_date' => '2024-11-25',
            'days' => 5,
            'range_1_30' => 0,
            'range_31_60' => 0,
            'range_61_90' => 0,
            'range_91_120' => 0,
            'range_over_120' => 0,
        ]);

        FinishedGood::updateOrCreate([
            'product_id' => 3,
        ], [
            'count_pc_area' => 179,
            'beg' => 0,
            'in_qty' => 5000,
            'out_qty' => 0,
            'theo_end' => 5000,
            'remarks' => null,
            'buffer_stocks' => 0,
            'cur_sell_price' => 13.20,
            'beg_amt' => 0.00,
            'in_amt' => 66000.00,
            'out_amt' => 0.00,
            'end_amt' => 66000.00,
            'ending_count' => -5000,
            'uom3' => 'PC/S',
            'variance_count' => -66000,
            'variance_amount' => -5000.00,
            'last_in_date' => '2024-12-02',
            'older_date' => '2024-06-24',
            'days' => 54,
            'range_1_30' => 0,
            'range_31_60' => 5000,
            'range_61_90' => 0,
            'range_91_120' => 0,
            'range_over_120' => 0,
        ]);
    }

    private function seedActualInventories(): void
    {
        ActualInventory::create([
            'tag_number' => 'TAG-2024-001',
            'product_id' => 1,
            'fg_qty' => 500,
            'uom' => 'PC/S',
            'location' => 'CIP2',
            'counted_by_user_id' => 2,
            'verified_by_user_id' => 3,
            'remarks' => 'Initial count',
        ]);

        ActualInventory::create([
            'tag_number' => 'TAG-2024-002',
            'product_id' => 2,
            'fg_qty' => 150,
            'uom' => 'PC/S',
            'location' => 'CIP1',
            'counted_by_user_id' => 1,
            'verified_by_user_id' => 2,
            'remarks' => null,
        ]);

        ActualInventory::create([
            'tag_number' => 'TAG-2024-003',
            'product_id' => 3,
            'fg_qty' => 5000,
            'uom' => 'PC/S',
            'location' => 'CIP3',
            'counted_by_user_id' => 3,
            'verified_by_user_id' => 1,
            'remarks' => 'Large quantity verified',
        ]);
    }

    private function seedDeliverySchedules(): void
    {
        DeliverySchedule::create([
            'ds_delivery_code' => '45995',
            'ds_status' => 'urgent',
            'date' => '2024-12-04',
            'jo_id' => 2,
            'po_number' => '84303',
            'product_id' => 3,
            'qty' => 40,
            'uom' => 'SET/S',
            'remarks' => 'W/ URGENT LIST 12/04-12NN',
            'pmp_commitment' => 'WITH RM COMPLETE',
            'ppqc_commitment' => null,
            'fg_stocks' => 0,
            'status' => 'Urgent',
            'delivery_remarks' => 'BACKLOG',
            'jo_remarks' => 'Balance',
            'ppqc_status' => 'Urgent',
            'jo_balance' => 90,
            'transfer' => 0,
            'delivered_dsd' => 0,
            'ds_qty' => 40,
            'week_num' => 49,
            'date_encoded' => '2024-12-02',
            'max_qty' => 40,
            'buffer_stocks' => 0,
            'backlog' => 0,
        ]);

        DeliverySchedule::create([
            'ds_delivery_code' => 'DS-2024-002',
            'ds_status' => 'complete',
            'date' => '2024-12-02',
            'jo_id' => 1,
            'po_number' => 'PO0000061501',
            'product_id' => 2,
            'qty' => 10,
            'uom' => 'PC/S',
            'remarks' => null,
            'pmp_commitment' => 'COMPLETED',
            'ppqc_commitment' => 'DONE',
            'fg_stocks' => 0,
            'status' => 'Complete',
            'delivery_remarks' => 'Delivered on time',
            'jo_remarks' => null,
            'ppqc_status' => 'Approved',
            'jo_balance' => 0,
            'transfer' => 10,
            'delivered_dsd' => 10,
            'ds_qty' => 10,
            'week_num' => 49,
            'date_encoded' => '2024-11-20',
            'max_qty' => 10,
            'buffer_stocks' => 5,
            'backlog' => 0,
        ]);

        DeliverySchedule::create([
            'ds_delivery_code' => 'DS-2024-003',
            'ds_status' => 'pending',
            'date' => '2024-12-05',
            'jo_id' => 3,
            'po_number' => 'PO0000062145',
            'product_id' => 1,
            'qty' => 77,
            'uom' => 'PC/S',
            'remarks' => 'FG EFREN',
            'pmp_commitment' => 'IN PROGRESS',
            'ppqc_commitment' => null,
            'fg_stocks' => 50,
            'status' => 'Pending',
            'delivery_remarks' => null,
            'jo_remarks' => 'Balance',
            'ppqc_status' => 'Pending',
            'jo_balance' => 357,
            'transfer' => 0,
            'delivered_dsd' => 0,
            'ds_qty' => 77,
            'week_num' => 49,
            'date_encoded' => '2024-11-28',
            'max_qty' => 77,
            'buffer_stocks' => 20,
            'backlog' => 0,
        ]);
    }

    private function seedTransfers(): void
    {
        Transfer::create([
            'ptt_number' => 'PTT 25-25422',
            'section' => 'IMPORTED',
            'date_transferred' => '2024-12-02',
            'jo_id' => 3,
            'qty' => 77,
            'delivery_date' => '2024-12-03',
            'remarks' => 'FG EFREN',
            'transfer_time' => '16:50:00',
            'transfer_status' => 'balance',
            'jo_balance' => 357,
            'product_id' => 1,
            'grade' => '2 WAY',
            'dimension' => 'AB-FLUTE O.D. 1130 X960mm',
            'received_by_user_id' => 2,
            'date_received' => '2024-12-03',
            'time_received' => '14:31:00',
            'qty_received' => 77,
            'jit_days' => 1,
            'ds_status' => 'Urgent',
            'week_num' => 49,
            'category' => 'IMPORTED',
            'selling_price' => 661.67,
            'total_amount' => 50948.59,
        ]);

        Transfer::create([
            'ptt_number' => 'PTT 25-25423',
            'section' => 'LOCAL',
            'date_transferred' => '2024-11-25',
            'jo_id' => 1,
            'qty' => 10,
            'delivery_date' => '2024-12-02',
            'remarks' => null,
            'transfer_time' => '10:30:00',
            'transfer_status' => 'complete',
            'jo_balance' => 0,
            'product_id' => 2,
            'grade' => 'A',
            'dimension' => '710X543X80mm',
            'received_by_user_id' => 3,
            'date_received' => '2024-11-25',
            'time_received' => '11:00:00',
            'qty_received' => 10,
            'jit_days' => 0,
            'ds_status' => 'Complete',
            'week_num' => 49,
            'category' => 'LOCAL',
            'selling_price' => 45.50,
            'total_amount' => 455.00,
        ]);

        Transfer::create([
            'ptt_number' => 'PTT 25-25424',
            'section' => 'IMPORTED',
            'date_transferred' => '2024-12-02',
            'jo_id' => 2,
            'qty' => 5000,
            'delivery_date' => '2024-12-04',
            'remarks' => 'Large order - urgent',
            'transfer_time' => '14:00:00',
            'transfer_status' => 'balance',
            'jo_balance' => 90,
            'product_id' => 3,
            'grade' => 'B',
            'dimension' => 'O.D. 630 X 420 X 755mm',
            'received_by_user_id' => 1,
            'date_received' => '2024-12-02',
            'time_received' => '15:30:00',
            'qty_received' => 5000,
            'jit_days' => 0,
            'ds_status' => 'Urgent',
            'week_num' => 49,
            'category' => 'IMPORTED',
            'selling_price' => 13.20,
            'total_amount' => 66000.00,
        ]);
    }
}