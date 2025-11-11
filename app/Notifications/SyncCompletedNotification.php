<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyncCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $totalItems;
    public string $status;
    public ?string $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $totalItems, string $status = 'success', ?string $errorMessage = null)
    {
        $this->totalItems = $totalItems;
        $this->status = $status;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('SpaceX Veri Senkronizasyonu ' . ($this->status === 'success' ? 'Başarılı' : 'Başarısız'));

        if ($this->status === 'success') {
            $mailMessage
                ->greeting('🚀 Senkronizasyon Tamamlandı!')
                ->line("SpaceX API'den toplam {$this->totalItems} kapsül verisi başarıyla senkronize edildi.")
                ->line('Tüm veriler veritabanına kaydedildi.')
                ->action('API Belgelerini Görüntüle', url('/api/documentation'))
                ->line('Teşekkür ederiz!');
        } else {
            $mailMessage
                ->error()
                ->greeting('❌ Senkronizasyon Başarısız!')
                ->line('SpaceX API ile senkronizasyon sırasında bir hata oluştu.')
                ->line("Hata Detayı: {$this->errorMessage}")
                ->line('Lütfen log dosyalarını kontrol edin.')
                ->action('Log Dosyalarını Görüntüle', url('/'))
                ->line('Sistem yöneticisi bilgilendirilmiştir.');
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'total_items' => $this->totalItems,
            'status' => $this->status,
            'error_message' => $this->errorMessage,
            'synced_at' => now()->toDateTimeString(),
        ];
    }
}
