<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Models\Activity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateActivityForLeadTask
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
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;

        Activity::create([
            'user_id' => $task->user_id,
            'lead_id' => $task->contact_id,
            'title' => 'Task Added',
            'type' => 'task',
            'notes' => $task->notes,
        ]);
    }
}
