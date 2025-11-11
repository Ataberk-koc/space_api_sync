<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataSyncCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $totalItems;
    public string $status;
    public ?string $errorMessage;

    /**
     * Create a new event instance.
     */
    public function __construct(int $totalItems, string $status = 'success', ?string $errorMessage = null)
    {
        $this->totalItems = $totalItems;
        $this->status = $status;
        $this->errorMessage = $errorMessage;
    }
}
