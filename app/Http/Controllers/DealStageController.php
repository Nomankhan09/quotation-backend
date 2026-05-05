<?php

namespace App\Http\Controllers;

use App\Models\DealStage;
use Illuminate\Http\Request;

class DealStageController extends Controller
{
    public function getDealStage(Request $req)
    {
        $deal_stage = DealStage::get();

        return response()->json([
            'status' => 200,
            'message' => 'All deal stages',
            'data' => $deal_stage
        ]);
    }
}
