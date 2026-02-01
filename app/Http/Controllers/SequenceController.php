<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JobOrder;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\DeliverySchedule;
use App\Models\ActualInventory;

class SequenceController extends Controller
{
    public function next(Request $request)
    {
        $type = $request->query('type', 'all');
        $date = $request->query('date', null);

        $data = [];

        if ($type === 'product' || $type === 'all') {
            $data['product_code'] = Product::nextProductCode();
        }

        if ($type === 'job_order' || $type === 'all') {
            $data['jo_number'] = JobOrder::nextJoNumber();
            $data['po_number'] = JobOrder::nextPoNumber($date);
        }

        if ($type === 'ds' || $type === 'all') {
            $data['delivery_code'] = DeliverySchedule::nextDeliveryCode();
        }

        if ($type === 'ptt' || $type === 'all') {
            $data['ptt_number'] = Transfer::nextPttNumber();
        }

        if ($type === 'tag' || $type === 'all') {
            $data['tag_number'] = ActualInventory::nextTagNumber();
        }

        return response()->json($data);
    }
}
