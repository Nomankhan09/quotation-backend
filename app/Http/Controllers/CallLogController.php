<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use Illuminate\Http\Request;

class CallLogController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'duration' => 'nullable|integer',
            'type' => 'required|in:INCOMING,OUTGOING,MISSED',
            'lead_id' => 'required|integer',
            'timestamp' => 'nullable|date',
        ]);

        $log = CallLog::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);


        return response()->json([
            'message' => 'Call log created',
            'data' => $log
        ], 201);
    }

    public function index(Request $request)
    {
        $query = CallLog::query();

        if ($request->lead_id) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }
 
        return response()->json(
            $query->latest()->get()
        );
    }

    // DELETE
    public function destroy($id)
    {
        $log = CallLog::findOrFail($id);
        $log->delete();

        return response()->json([
            'message' => 'Call log deleted successfully'
        ]);
    }
}
