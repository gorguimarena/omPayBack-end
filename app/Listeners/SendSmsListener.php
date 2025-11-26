<?php

namespace App\Listeners;

use App\Events\CreateCompteEven;
use App\Jobs\SendSmsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSmsListener implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(CreateCompteEven $event): void
    {
        SendSmsJob::dispatch($event->compte->telephone, 'Ahoy 👋');
    }
}
