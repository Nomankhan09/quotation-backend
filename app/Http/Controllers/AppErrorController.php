<?php

namespace App\Http\Controllers;

use App\Models\AppError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AppErrorController extends Controller
{
    public function store(Request $request)
    {
        // Don’t over-validate; logs must not fail
        $data = [
            'user_id' => auth()->id(),
            'message' => $request->input('message'),
             'stack' => is_array($request->input('stack'))
                ? json_encode($request->input('stack'))
                : $request->input('stack'),
            'screen' => $request->input('screen'),
            'platform' => $request->input('platform'),
            'is_fatal' => (bool) $request->input('is_fatal'),
            'app_version' => $request->input('app_version'),
        ];

        // 1) Save to DB
        AppError::create($data);

        // 2) Also write to Laravel logs
        Log::error('APP_ERROR', $data);

        return response()->json(['ok' => true]);
    }
}
