<?php

namespace App\Listeners;

use App\Events\DataSyncCompleted;
use App\Notifications\SyncCompletedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSyncNotification implements ShouldQueue
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
    public function handle(DataSyncCompleted $event): void
    {
        // Admin kullanıcıları bul (örneğin: email'de 'admin' geçenler veya role kontrolü)
        // Basit örnek: İlk kullanıcıya gönder
        $adminUsers = User::where('email', 'like', '%admin%')->get();
        
        // Eğer admin yoksa, ilk kullanıcıya gönder (test için)
        if ($adminUsers->isEmpty()) {
            $adminUsers = User::take(1)->get();
        }

        // Her admin kullanıcıya bildirim gönder
        foreach ($adminUsers as $admin) {
            $admin->notify(new SyncCompletedNotification(
                $event->totalItems,
                $event->status,
                $event->errorMessage
            ));
        }

        // Log kaydı
        Log::info('Sync notification sent to admins', [
            'total_items' => $event->totalItems,
            'status' => $event->status,
            'admin_count' => $adminUsers->count()
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(DataSyncCompleted $event, \Throwable $exception): void
    {
        Log::error('Failed to send sync notification', [
            'error' => $exception->getMessage(),
            'total_items' => $event->totalItems
        ]);
    }
}
