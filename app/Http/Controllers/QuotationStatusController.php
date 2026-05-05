<?php

namespace App\Http\Controllers;

use App\Models\QuotationStatus;
use Illuminate\Http\Request;

class QuotationStatusController extends Controller
{
    public function getStatus()
    {
        $user_id = auth()->user();
        $status = QuotationStatus::where('user_id', $user_id)->get();
        return response()->json(['status' => 201, 'message' => 'All status', 'data' => $status]);
    }
}
