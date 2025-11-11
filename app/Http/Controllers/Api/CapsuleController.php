<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Capsule;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SpaceX Capsule API",
 *     description="SpaceX API'den senkronize edilen kapsül verilerine erişim sağlar.",
 *     @OA\Contact(
 *         email="admin@example.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="SpaceX API Endpointleri"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="passport",
 *     type="oauth2",
 *     @OA\Flow(
 *         flow="password",
 *         tokenUrl="http://127.0.0.1:8000/oauth/token",
 *         scopes={}
 *     )
 * )
 */
class CapsuleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/capsules",
     *     summary="Kapsül listesini getir",
     *     description="Tüm kapsülleri listeler ve status parametresi ile filtreleme yapılabilir",
     *     operationId="getCapsules",
     *     tags={"Capsules"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Kapsül durumu (active, retired, destroyed, unknown)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active", "retired", "destroyed", "unknown"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Sayfa numarası",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="capsule_serial", type="string", example="C101"),
     *                     @OA\Property(property="capsule_id", type="string", example="dragon1"),
     *                     @OA\Property(property="status", type="string", example="retired"),
     *                     @OA\Property(property="original_launch", type="string", format="date-time", example="2010-12-08 15:43:00"),
     *                     @OA\Property(property="missions_count", type="integer", example=1),
     *                     @OA\Property(property="details", type="string", example="Reentered after three weeks in orbit")
     *                 )
     *             ),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="total", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Capsule::query();

        // Status filtresi
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Pagination ile sonuçları döndür
        $capsules = $query->paginate(15);

        return response()->json($capsules);
    }

    /**
     * @OA\Get(
     *     path="/api/capsules/{capsule_serial}",
     *     summary="Kapsül detayını getir",
     *     description="Belirtilen serial numarasına sahip kapsülün detaylarını getirir",
     *     operationId="getCapsuleBySerial",
     *     tags={"Capsules"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="capsule_serial",
     *         in="path",
     *         description="Kapsül seri numarası",
     *         required=true,
     *         @OA\Schema(type="string", example="C101")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı işlem",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="capsule_serial", type="string", example="C101"),
     *             @OA\Property(property="capsule_id", type="string", example="dragon1"),
     *             @OA\Property(property="status", type="string", example="retired"),
     *             @OA\Property(property="original_launch", type="string", format="date-time", example="2010-12-08 15:43:00"),
     *             @OA\Property(property="missions_count", type="integer", example=1),
     *             @OA\Property(property="details", type="string", example="Reentered after three weeks in orbit"),
     *             @OA\Property(property="raw_data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kapsül bulunamadı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Capsule not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Yetkisiz erişim"
     *     )
     * )
     */
    public function show(string $capsule_serial)
    {
        $capsule = Capsule::where('capsule_serial', $capsule_serial)->first();

        if (!$capsule) {
            return response()->json(['message' => 'Capsule not found'], 404);
        }

        return response()->json($capsule);
    }
}
