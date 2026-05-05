<?php

namespace App\Http\Controllers;

use App\Models\ContactStatus;

class ContactStatusController extends Controller
{
    public function getStatus()
    {
        $user_id = auth()->id();
        $status = ContactStatus::where('user_id', $user_id)->get();
        return response()->json(['status' => 201, 'message' => 'All status', 'data' => $status]);
    }
}
