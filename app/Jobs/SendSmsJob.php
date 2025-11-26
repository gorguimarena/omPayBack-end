<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Twilio\Rest\Client;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    protected $to;
    protected $body;
    protected $twilio;

    /**
     * Create a new job instance.
     */
    public function __construct($to, $body)
    {
        $this->to = $to;
        $this->body = $body;
        $this->twilio = new Client(
            env('TWILIO_SID'),
            env('TWILIO_TOKEN')
        );
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $from = config('services.twilio.from');

        $this->twilio->messages->create($this->to, [
            'from' => $from,
            'body' => $this->body
        ]);
    }
}
