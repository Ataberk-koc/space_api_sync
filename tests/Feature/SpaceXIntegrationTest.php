<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Capsule;
use Illuminate\Support\Facades\Http;

class SpaceXIntegrationTest extends TestCase
{
    use RefreshDatabase; // Her testten sonra veritabanını temizler

    /** @test */
    public function artisan_komutu_basariyla_calisir_ve_veriyi_senkronize_eder()
    {
        // 1. API'yi Sahteleme (Gereksinim: Dış API'ye bağımlı kalmamak)
        $mockData = [
            [
                "capsule_serial" => "C101",
                "status" => "retired",
                "missions" => ["mission1", "mission2"],
                // ... diğer veriler ...
            ],
            [
                "capsule_serial" => "C102",
                "status" => "active",
                "missions" => ["mission3"],
                // ... diğer veriler ...
            ]
        ];
        
        // API isteği yapıldığında sahte veriyi döndürmesini sağla
        Http::fake([
            'https://api.spacexdata.com/v3/capsules' => Http::response($mockData, 200),
        ]);

        // 2. Komutu Çalıştırma
        $this->artisan('spacex:sync')
             ->assertSuccessful(); // Komutun başarılı (exit code 0) bittiğini kontrol et

        // 3. Veritabanı Kontrolü
        // Veritabanında 2 satır oluşmuş mu?
        $this->assertDatabaseCount('capsules', 2); 
        
        // Belirli bir verinin doğru yazılıp yazılmadığını kontrol et
        $this->assertDatabaseHas('capsules', [
            'capsule_serial' => 'C101',
            'status' => 'retired',
            'missions_count' => 2, // Missions dizisinin boyutunu doğru kaydettiğimizi kontrol ediyoruz
        ]);
        
        // Loglama ve Event Kontrolü (Opsiyonel ama önerilir)
        // Log::shouldReceive('info')->once();
        // Event::fake(); 
        // Event::assertDispatched(DataSyncCompleted::class);
    }

    /** @test */
    public function api_tum_kapsulleri_listeler_ve_basarili_doner()
    {
        // Test verisini oluştur (DB'ye yaz)
        Capsule::factory()->create(['capsule_serial' => 'API01', 'status' => 'active']);
        Capsule::factory()->create(['capsule_serial' => 'API02', 'status' => 'retired']);

        $response = $this->withoutMiddleware()->getJson('/api/capsules'); // API rotasına istek gönder

        $response->assertStatus(200) // Başarılı (OK) dönmeli
                 ->assertJsonCount(2, 'data') // 2 kayıt döndüğünü kontrol et (pagination'da data içinde gelir)
                 ->assertJsonFragment(['capsule_serial' => 'API01']); // Belirli bir veriyi içerdiğini kontrol et
    }

    /** @test */
    public function api_status_filtresini_uygular()
    {
        // Test verisini oluştur
        Capsule::factory()->create(['capsule_serial' => 'F01', 'status' => 'active']);
        Capsule::factory()->create(['capsule_serial' => 'F02', 'status' => 'retired']);

        // Sadece 'active' filtrelenen isteği gönder
        $response = $this->withoutMiddleware()->getJson('/api/capsules?status=active'); 

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data') // Sadece 1 kayıt dönmeli
                 ->assertJsonFragment(['capsule_serial' => 'F01'])
                 ->assertJsonMissing(['capsule_serial' => 'F02']);
    }

    /** @test */
    public function api_detay_endpointi_veri_dondurur()
    {
        // Test verisini oluştur
        Capsule::factory()->create(['capsule_serial' => 'DETAY01', 'status' => 'active']);

        $response = $this->withoutMiddleware()->getJson('/api/capsules/DETAY01'); 

        $response->assertStatus(200)
                 ->assertJsonFragment(['capsule_serial' => 'DETAY01']);
    }

    /** @test */
    public function api_detay_endpointi_bulunamayan_icin_404_dondurur()
    {
        $response = $this->withoutMiddleware()->getJson('/api/capsules/NONEXISTENT'); 
        
        $response->assertStatus(404);
    }
}
