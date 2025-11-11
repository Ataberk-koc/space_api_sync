<?php

namespace App\Console\Commands;

use App\Models\Capsule;
use App\Events\DataSyncCompleted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncSpaceXData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'spacex:sync';
    protected $description = 'SpaceX API verilerini çeker ve senkronize eder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 SpaceX veri senkronizasyonu başlatılıyor...');

        $response = Http::withoutVerifying()->get('https://api.spacexdata.com/v3/capsules');

        if ($response->failed()) {
            $this->error('❌ Hata: SpaceX API\'den veri çekilemedi!');
            
            // Hata durumunda event dispatch et
            DataSyncCompleted::dispatch(0, 'failed', 'API request failed');
            
            return Command::FAILURE;
        }

        $capsules = $response->json();

        foreach ($capsules as $capsule) {
            // updateOrCreate: Veri varsa günceller, yoksa oluşturur (Senkronizasyonun temeli)
            Capsule::updateOrCreate(
                ['capsule_serial' => $capsule['capsule_serial']],
                [
                    'capsule_id' => $capsule['capsule_id'] ?? null,
                    'status' => $capsule['status'] ?? null,
                    'original_launch' => isset($capsule['original_launch']) 
                        ? date('Y-m-d H:i:s', strtotime($capsule['original_launch'])) 
                        : null,
                    'missions_count' => count($capsule['missions'] ?? []) ,
                    'details' => $capsule['details'] ?? null,
                    'raw_data' => json_encode($capsule), // Tüm JSON'u kaydetme
                ]
            );
        }

        // Görev gereği: Günlük Kaydı (Log)
        Log::info('✅ SpaceX Data Sync Completed.', ['total_items' => count($capsules)]);
        
        // Başarılı senkronizasyon sonrası event dispatch et
        DataSyncCompleted::dispatch(count($capsules), 'success');
        
        $this->info('✅ Senkronizasyon başarıyla tamamlandı. Log kontrolü yapınız.');
        return Command::SUCCESS;
    }
}
