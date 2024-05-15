<?php

namespace Noorfarooqy\EasyNotifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $level;
    public $channel_name;
    public $error_message;
    public $error_description;
    public function __construct($error_message, $error_description = '', $channel_name = 'single', $level = 'info')
    {
        $this->error_message = $error_message;
        $this->error_description = $error_description;
        $this->channel_name = $channel_name;
        $this->level = $level;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        switch ($this->level) {
            case 'info':
                Log::channel($this->channel_name)->info('-----------------LOG ON ' . $this->channel_name . '-------------------');
                Log::channel($this->channel_name)->info(json_encode($this->error_message));
                Log::channel($this->channel_name)->info(json_encode($this->error_description));
                break;
            case 'error':
                Log::channel($this->channel_name)->error('-----------------LOG ON ' . $this->channel_name . '-------------------');
                Log::channel($this->channel_name)->error(json_encode($this->error_message));
                Log::channel($this->channel_name)->error(json_encode($this->error_description));
                break;
            case 'warning':
                Log::channel($this->channel_name)->warning('-----------------LOG ON ' . $this->channel_name . '-------------------');
                Log::channel($this->channel_name)->warning(json_encode($this->error_message));
                Log::channel($this->channel_name)->warning(json_encode($this->error_description));
                break;
            case 'debug':
                Log::channel($this->channel_name)->debug('-----------------LOG ON ' . $this->channel_name . '-------------------');
                Log::channel($this->channel_name)->debug(json_encode($this->error_message));
                Log::channel($this->channel_name)->debug(json_encode($this->error_description));
                break;
            default:
                Log::channel($this->channel_name)->info('-----------------LOG ON ' . $this->channel_name . '-------------------');
                Log::channel($this->channel_name)->info(json_encode($this->error_message));
                Log::channel($this->channel_name)->info(json_encode($this->error_description));
                break;


        }


    }
}
