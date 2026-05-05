<?php

namespace App\Listeners;

use App\Events\LeadNoteCreated;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateActivityForLeadNote
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LeadNoteCreated $event): void
    {
        $note = $event->note;

        Activity::create([
            'user_id' => $note->user_id,
            'lead_id' => $note->lead_id,
            'title' => 'Note Added',
            'type' => 'note',
            'notes' => $note->note,
        ]);
    }
}
