<?php

namespace App\Http\Controllers;

use App\Events\followUpCreated;
use App\Models\FollowUps;
use Illuminate\Http\Request;

class FollowUpsController extends Controller
{
    public function createFollowUp(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'contact_id' => 'required|integer',
            'type' => 'required|string|in:Call,Email,Meeting,Task',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:pending,snoozed,done',
            'notification_id' => 'nullable'
        ]);

        $formatted_date = \Carbon\Carbon::createFromFormat('m/d/Y h:i A', $validatedData['date'])
            ->format('Y-m-d H:i:s');

        // Create a new follow-up
        $followUp = FollowUps::create([
            'user_id' => auth()->id(),
            'title' => $validatedData['title'],
            'type' => $validatedData['type'],
            'date' => $formatted_date,
            'notes' => $validatedData['notes'] ?? null,
            'status' => $validatedData['status'] ?? 'pending',
            'contact_id' => $validatedData['contact_id'],
            'notification_id' => $validatedData['notification_id']
        ]);

        return response()->json(['status' => 201, 'message' => 'Follow-up created successfully', 'follow_up' => $followUp], 201);
    }

    public function getFollowUps()
    {
        $followUps = FollowUps::where('user_id', auth()->id())->get();
        return response()->json(['status' => 200, 'follow_ups' => $followUps], 200);
    }

    public function updateFollowUp(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'contact_id' => 'required|integer',
            'type' => 'sometimes|required|string|in:Call,Email,Meeting,Task',
            'date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:pending,snoozed,done',
            'notification_id' => 'nullable'
        ]);

        // Find the follow-up and ensure it belongs to the authenticated user
        $followUp = FollowUps::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$followUp) {
            return response()->json(['status' => 404, 'message' => 'Follow-up not found'], 200);
        }

        if ($request->has('date')) {
            try {
                $validatedData['date'] = \Carbon\Carbon::parse($request->date)
                    ->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Invalid date format'
                ], 422);
            }
        }

        // Update the follow-up with validated data
        $followUp->update($validatedData);

        // ✅ trigger event
        if ($followUp->status === 'done') {
            event(new followUpCreated($followUp));
        }

        return response()->json(['status' => 200, 'message' => 'Follow-up updated successfully', 'follow_up' => $followUp], 200);
    }

    public function deleteFollowUp($id)
    {
        // Find the follow-up and ensure it belongs to the authenticated user
        $followUp = FollowUps::where('id', $id)->where('user_id', auth()->id())->first();

        if (!$followUp) {
            return response()->json(['status' => 404, 'message' => 'Follow-up not found'], 200);
        }

        // Delete the follow-up
        $followUp->delete();

        return response()->json(['status' => 200, 'message' => 'Follow-up deleted successfully'], 200);
    }

    public function getFollowUpsByLead($leadId)
    {
        // Assuming there's a relationship between FollowUps and Leads
        $followUps = FollowUps::where('user_id', auth()->id())
            ->where("contact_id", $leadId)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['status' => 200, 'follow_ups' => $followUps], 200);
    }
}
