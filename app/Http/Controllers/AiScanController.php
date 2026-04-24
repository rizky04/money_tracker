<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|max:2048',
        ]);

        try {
            $image = $request->file('receipt');
            $base64Image = base64_encode(file_get_contents($image->getPathname()));
            $mimeType = $image->getMimeType();

            // Prompt khusus untuk struk Indonesia
           // Update prompt menjadi lebih fleksibel
// Update prompt untuk mendukung berbagai SPBU
$prompt = "Anda adalah AI khusus untuk membaca struk pengisian BBM dari berbagai SPBU/Stasiun Pengisian BBM di Indonesia.

Analisis gambar struk berikut dan ekstrak data dengan format JSON yang valid.

SPBU yang mungkin muncul:
- PERTAMINA (Pertalite, Pertamax, Pertamax Turbo, Dexlite, Pertamina Dex)
- SHELL (Shell Super, Shell V-Power, Shell Diesel)
- BP (BP 92, BP 95, BP Diesel)
- VIVO (Vivo Revvo 90, Vivo Revvo 95)
- Atau merek lainnya

Format struk yang umum:
- Tanggal: Biasanya format DD/MM/YYYY atau DD-MM-YYYY
- Nama produk: Nama BBM sesuai struk
- Harga/Liter: Angka setelah 'Harga/Liter : Rp.'
- Volume: Angka setelah 'Volume : (L)' atau 'Liter :'
- Total Harga: Angka setelah 'Total Harga : Rp.'

KEMBALIKAN HANYA JSON VALID tanpa teks lain, tanpa markdown, tanpa penjelasan.

Format JSON:
{
    \"date\": \"YYYY-MM-DD\",
    \"location_name\": \"Nama SPBU/Stasiun dan alamat\",
    \"fuel_type\": \"NAMA BBM PERSIS SEPERTI DI STRUK\",
    \"price_per_liter\": 10000,
    \"liters\": 3.00,
    \"total_price\": 30000
}

Aturan:
1. date: Konversi DD/MM/YYYY -> YYYY-MM-DD
2. price_per_liter & total_price: Hanya angka tanpa titik/koma
3. liters: Angka desimal dengan titik
4. fuel_type: Ambil NAMA PRODUK PERSIS seperti di struk (contoh: PERTALITE, SHELL SUPER, BP 92, VIVO REVVO 95)
5. location_name: Nama dan alamat SPBU/Stasiun

KEMBALIKAN HANYA JSON!";

            $apiKey = env('GEMINI_API_KEY');

            if (!$apiKey) {
                throw new \Exception("GEMINI_API_KEY tidak ditemukan");
            }

            // Gunakan model yang support vision
            $models = ['gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.0-flash-lite'];
            $result = null;
            $lastError = null;

            foreach ($models as $model) {
                try {
                    $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}";

                    $response = Http::timeout(30)->post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inline_data' => [
                                            'mime_type' => $mimeType,
                                            'data' => $base64Image
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'topP' => 0.95,
                            'maxOutputTokens' => 1024,
                        ]
                    ]);

                    if ($response->successful()) {
                        $result = $response->json();
                        Log::info("Success using model: {$model}");
                        break;
                    }

                    $lastError = $response->body();
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }
            }

            if (!$result) {
                throw new \Exception("Gagal memproses dengan semua model: " . $lastError);
            }

            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            if (empty($text)) {
                throw new \Exception("Tidak ada response dari AI");
            }

            // Bersihkan response
            $cleanJson = preg_replace('/```json\s*|\s*```/', '', $text);
            $cleanJson = trim($cleanJson);

            // Cari JSON object
            if (!str_starts_with($cleanJson, '{')) {
                preg_match('/\{[^{}]*\}/s', $cleanJson, $matches);
                if (isset($matches[0])) {
                    $cleanJson = $matches[0];
                }
            }

            $data = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Parse Error: ' . json_last_error_msg());
                Log::error('Raw text: ' . $text);

                // Fallback: ekstrak manual dari text
                $data = $this->manualExtractFromText($text);
            }

            // Validasi dan konversi data
            $data = $this->validateAndConvertData($data);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('AI Scan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Gagal memproses struk: ' . $e->getMessage()
            ], 500);
        }
    }

   private function manualExtractFromText($text)
{
    $data = [
        'date' => null,
        'location_name' => null,
        'fuel_type' => null,
        'price_per_liter' => null,
        'liters' => null,
        'total_price' => null
    ];

    // Extract tanggal (format: DD-MMM-YYYY seperti 30-Oct-2025)
    if (preg_match('/(\d{2})-([A-Za-z]{3})-(\d{4})/', $text, $matches)) {
        $monthMap = [
            'Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04',
            'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08',
            'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12'
        ];
        $month = $monthMap[$matches[2]] ?? '01';
        $data['date'] = "{$matches[3]}-{$month}-{$matches[1]}";
    }
    // Fallback format DD/MM/YYYY
    elseif (preg_match('/(\d{2})[\/\-](\d{2})[\/\-](\d{4})/', $text, $matches)) {
        $data['date'] = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
    }

    // Extract lokasi (BPAKR MINANGKABAU)
    if (preg_match('/([A-Z\s]+MINANGKABAU|[A-Z\s]+SPBU)/i', $text, $matches)) {
        $data['location_name'] = trim($matches[1]);
    }

    // Extract fuel type (BP 92)
    if (preg_match('/(BP\s*\d+)/i', $text, $matches)) {
        $data['fuel_type'] = trim($matches[1]);
    }

    // PERBAIKAN: Extract dari format tabel (Product | Qty | Price | Amount)
    // Qty: 3.702 (3 digit desimal), Price: 12.890, Amount: 47.719
    if (preg_match('/(?:BP\s*\d+|[A-Z\s]+)\s+([\d\.]+)\s+([\d\.]+)\s+([\d\.]+)/i', $text, $matches)) {
        // Liter (Qty) - pertahankan semua desimal
        $data['liters'] = (float) $matches[1];
        // Harga per liter (Price)
        $data['price_per_liter'] = str_replace('.', '', $matches[2]);
        // Total harga (Amount)
        $data['total_price'] = str_replace('.', '', $matches[3]);
    }

    // Fallback: Extract liters
    if (!$data['liters'] && preg_match('/(?:Qty|Volume|Liter)[:\s]+([\d\.]+)/i', $text, $matches)) {
        $data['liters'] = (float) $matches[1];
    }

    return $data;
}

private function validateAndConvertData($data)
{
    // Konversi date ke format YYYY-MM-DD
    if ($data['date']) {
        try {
            $date = \Carbon\Carbon::parse($data['date']);
            $data['date'] = $date->format('Y-m-d');
        } catch (\Exception $e) {
            $data['date'] = date('Y-m-d');
        }
    } else {
        $data['date'] = date('Y-m-d');
    }

    // Fuel type
    if (empty($data['fuel_type'])) {
        $data['fuel_type'] = 'Tidak diketahui';
    } else {
        $data['fuel_type'] = trim($data['fuel_type']);
        $data['fuel_type'] = ucwords(strtolower($data['fuel_type']));
    }

    // Parse price (hapus titik)
    if ($data['price_per_liter']) {
        if (is_string($data['price_per_liter'])) {
            $cleaned = str_replace('.', '', $data['price_per_liter']);
            $data['price_per_liter'] = (int) $cleaned;
        } else {
            $data['price_per_liter'] = (int) $data['price_per_liter'];
        }
    }

    // Parse total price
    if ($data['total_price']) {
        if (is_string($data['total_price'])) {
            $cleaned = str_replace('.', '', $data['total_price']);
            $data['total_price'] = (int) $cleaned;
        } else {
            $data['total_price'] = (int) $data['total_price'];
        }
    }

    // PERBAIKAN: Parse liters dengan presisi penuh (3 desimal)
    if ($data['liters']) {
        if (is_string($data['liters'])) {
            // Ganti koma dengan titik jika ada
            $liters = str_replace(',', '.', $data['liters']);
            $data['liters'] = (float) $liters;
        } else {
            $data['liters'] = (float) $data['liters'];
        }
    }

    // Hitung total jika tidak ada
    if (!$data['total_price'] && $data['price_per_liter'] && $data['liters']) {
        $data['total_price'] = (int) round($data['price_per_liter'] * $data['liters']);
    }

    return $data;
}

}
