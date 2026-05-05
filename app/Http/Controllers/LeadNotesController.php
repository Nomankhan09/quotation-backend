<?php

namespace App\Http\Controllers;

use App\Events\LeadNoteCreated;
use App\Models\LeadNotes;
use Illuminate\Http\Request;

class LeadNotesController extends Controller
{
    public function createLeadNote(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'lead_id' => 'required|integer',
            'note' => 'required|string',
        ]);

        // Create a new note
        $note = LeadNotes::create([
            'lead_id' => $validatedData['lead_id'],
            'user_id' => auth()->id(),
            'note' => $validatedData['note'],
        ]);

        // ✅ trigger event
        event(new LeadNoteCreated($note));

        return response()->json(['status' => 201, 'message' => 'Note added successfully', 'note' => $note], 201);
    }

    public function getNotesByLead($lead_id)
    {
        $user_id = auth()->id();
        $leadNotes = LeadNotes::where('user_id', $user_id)->where('lead_id', $lead_id)
            ->orderBy('id', 'desc')
            ->get();
        return response()->json(['status' => 200, 'notes' => $leadNotes], 200);
    }

    public function updateLeadNote(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'lead_id' => 'required|integer',
            'note' => 'required|string',
        ]);

        // Create a new note
        $note = LeadNotes::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $note->update($validatedData);

        return response()->json(['status' => 201, 'message' => 'Note updated successfully', 'note' => $note], 201);
    }

    public function deleteLeadNote($id)
    {
        $leadNote =  LeadNotes::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $leadNote->delete();

        return response()->json(['status' => 200, 'message' => 'Notes deleted successfully', 'note' => $leadNote]);
    }
}
