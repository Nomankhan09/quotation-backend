<?php

namespace App\Http\Controllers;

use App\Models\QuotationStatus;
use Illuminate\Http\Request;

class QuotationStatusController extends Controller
{
    public function getStatus()
    {
        $user_id = auth()->user();
        $status = QuotationStatus::get();
        return response()->json(['status' => 201, 'message' => 'All status', 'data' => $status]);
    }
}
