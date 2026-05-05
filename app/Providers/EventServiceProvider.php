<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // \App\Events\FollowUpCreated::class => [
        //     \App\Listeners\CreateActivityForLeadFollowUp::class,
        // ],
        // \App\Events\LeadNoteCreated::class => [
        //     \App\Listeners\CreateActivityForLeadNote::class,
        // ],
    ];

    public function boot(): void
    {
        //
    }
}