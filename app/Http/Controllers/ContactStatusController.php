<?php

namespace App\Http\Controllers;

use App\Models\ContactStatus;

class ContactStatusController extends Controller
{
    public function getStatus()
    {
        $status = ContactStatus::get();
        return response()->json(['status' => 201, 'message' => 'All status', 'data' => $status]);
    }
}
