<?php

namespace App\Listeners;

use App\Events\followUpCreated;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateActivityForLeadFollowUp
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
    public function handle(followUpCreated $event): void
    {
        $followUp = $event->followUp;

        Activity::create([
            'user_id' => $followUp->user_id,
            'lead_id' => $followUp->contact_id,
            'title' => $this->follow_up_title_for_activity($followUp->type),
            'type' => $followUp->type,
            'notes' => $followUp->notes,
        ]);
    }

    private function follow_up_title_for_activity($type)
    {
        switch ($type) {
            case 'Call':
                return 'Phone call';
                break;
            case 'Email':
                return 'Email sent';
                break;
            case 'Meeting':
                return 'Meeting done';
                break;
        }
    }
}
