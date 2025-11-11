<?php

// tests/Unit/SyncLogicUnitTest.php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use App\Models\Capsule;
use App\Console\Commands\SyncSpaceXData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncLogicUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Loglama ve Event tetiklemesinin doğru çalıştığını kontrol eder.
     */
    public function komut_basarili_oldugunda_log_kaydi_olusturulur()
    {
        // 1. Log'u sahte (spy) hale getir - gerçekte çalışır ama takip edilir
        Log::spy();
        
        // 2. Event'i fake et
        Event::fake();
        
        // 3. Mock veriyi oluştur (API'den gelmiş gibi)
        $mockResponse = [
            [
                "capsule_serial" => "C101",
                "status" => "active",
                "missions" => ["mission1"],
                "details" => "Test details",
                "capsule_id" => "dragon1",
                "original_launch" => "2010-12-08T15:43:00.000Z"
            ]
        ];

        // 4. Http isteğini sahte veri ile yanıt vermesi için ayarla
        Http::fake([
            'https://api.spacexdata.com/v3/capsules' => Http::response($mockResponse, 200),
        ]);

        // 5. Komutu çalıştır
        $this->artisan('spacex:sync')
             ->assertSuccessful();

        // 6. Loglama yapıldığını kontrol et
        Log::shouldHaveReceived('info')
            ->once()
            ->with('✅ SpaceX Data Sync Completed.', ['total_items' => 1]);
        
        // 7. Event dispatch edildiğini kontrol et
        Event::assertDispatched(\App\Events\DataSyncCompleted::class, function ($event) {
            return $event->totalItems === 1 
                && $event->status === 'success' 
                && $event->errorMessage === null;
        });
        
        // 8. Veritabanına kaydedildiğini kontrol et
        $this->assertDatabaseHas('capsules', [
            'capsule_serial' => 'C101',
            'status' => 'active',
            'missions_count' => 1
        ]);
    }

    /**
     * @test
     * API yanıt vermediğinde hata yönetimini test eder
     */
    public function api_basarisiz_oldugunda_hata_mesaji_gosterilir()
    {
        // Event'i fake et
        Event::fake();
        
        // API'nin başarısız yanıt vermesini simüle et
        Http::fake([
            'https://api.spacexdata.com/v3/capsules' => Http::response([], 500),
        ]);

        // Komutu çalıştır ve başarısız olmasını bekle
        $this->artisan('spacex:sync')
             ->expectsOutput('❌ Hata: SpaceX API\'den veri çekilemedi!')
             ->assertFailed();

        // Event dispatch edildiğini kontrol et (hata durumunda)
        Event::assertDispatched(\App\Events\DataSyncCompleted::class, function ($event) {
            return $event->totalItems === 0 
                && $event->status === 'failed' 
                && $event->errorMessage === 'API request failed';
        });

        // Veritabanına hiçbir şey eklenmediğini kontrol et
        $this->assertDatabaseCount('capsules', 0);
    }

    /**
     * @test
     * Tarih dönüşümünün doğru çalıştığını test eder
     */
    public function tarih_donusumu_dogru_yapilir()
    {
        $mockResponse = [
            [
                "capsule_serial" => "C102",
                "status" => "retired",
                "missions" => [],
                "details" => null,
                "capsule_id" => "dragon2",
                "original_launch" => "2015-04-14T20:10:00.000Z" // ISO 8601 format
            ]
        ];

        Http::fake([
            'https://api.spacexdata.com/v3/capsules' => Http::response($mockResponse, 200),
        ]);

        $this->artisan('spacex:sync')->assertSuccessful();

        // Tarihin MySQL formatına dönüştüğünü kontrol et
        $capsule = Capsule::where('capsule_serial', 'C102')->first();
        $this->assertNotNull($capsule->original_launch);
        $this->assertEquals('2015-04-14 20:10:00', $capsule->original_launch);
    }

    /**
     * @test
     * updateOrCreate metodunun doğru çalıştığını test eder
     */
    public function ayni_capsule_serial_icin_guncelleme_yapilir()
    {
        // İlk senkronizasyon
        $firstResponse = [
            [
                "capsule_serial" => "C103",
                "status" => "active",
                "missions" => ["mission1"],
                "details" => "First version",
                "capsule_id" => "dragon3",
                "original_launch" => "2016-07-18T04:45:00.000Z"
            ]
        ];

        Http::fake([
            'https://api.spacexdata.com/v3/capsules' => Http::response($firstResponse, 200),
        ]);

        $this->artisan('spacex:sync')->assertSuccessful();
        
        $this->assertDatabaseCount('capsules', 1);
        $this->assertDatabaseHas('capsules', [
            'capsule_serial' => 'C103',
            'status' => 'active',
            'details' => 'First version'
        ]);

        // İkinci senkronizasyon için HTTP fake'i yeniden ayarla
        Http::swap(new \Illuminate\Http\Client\Factory());
        
        $updatedResponse = [
            [
                "capsule_serial" => "C103",
                "status" => "retired",
                "missions" => ["mission1", "mission2"],
                "details" => "Updated version",
                "capsule_id" => "dragon3",
                "original_launch" => "2016-07-18T04:45:00.000Z"
            ]
        ];

        Http::fake([
            'https://api.spacexdata.com/v3/capsules' => Http::response($updatedResponse, 200),
        ]);

        $this->artisan('spacex:sync')->assertSuccessful();
        
        // Hala 1 kayıt olmalı (yeni eklenmemeli, güncellenmeli)
        $this->assertDatabaseCount('capsules', 1);
        
        // Güncellenmiş veriler kontrol edilmeli
        $this->assertDatabaseHas('capsules', [
            'capsule_serial' => 'C103',
            'status' => 'retired',
            'details' => 'Updated version',
            'missions_count' => 2
        ]);
    }
}
