<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    // 1. Tampilkan Halaman
    public function index()
    {
        // Ambil data struk beserta barang-barangnya
        $expenses = Expense::with('items')
                    ->where('user_id', Auth::id())
                    ->orderBy('date', 'desc')
                    ->get();

        return view('dashboard.money', compact('expenses'));
    }

    // 2. Scan Gambar dengan Gemini (Ekstrak Array)
    public function scan(Request $request)
    {
        $request->validate(['receipt' => 'required|image|max:5120']);

        try {
            $image = $request->file('receipt');
            $base64Image = base64_encode(file_get_contents($image->path()));
            $mimeType = $image->getMimeType();

            // Prompt khusus Itemizer
            $prompt = "Kamu adalah sistem kasir otomatis. Ekstrak struk ini menjadi JSON valid:
            {
                \"date\": \"YYYY-MM-DD\",
                \"merchant\": \"Nama Toko/Merchant\",
                \"items\": [
                    { \"name\": \"Nama Barang\", \"qty\": 1, \"price\": 10000, \"subtotal\": 10000 }
                ],
                \"total_amount\": integer (total semua belanjaan tanpa titik/koma)
            }";

            $apiKey = env('GEMINI_API_KEY');
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}";

            $response = Http::post($url, [
                'contents' => [
                    ['parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64Image]]
                    ]]
                ]
            ]);

            if ($response->failed()) throw new \Exception($response->body());

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

            $cleanJson = trim(str_replace(['```json', '```'], '', $text));
            $data = json_decode($cleanJson, true);

            if (!$data) return response()->json(['error' => 'Gagal membaca isi struk.'], 422);

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('AI Scan Array Error: ' . $e->getMessage());
            return response()->json(['error' => 'Koneksi AI gagal.'], 500);
        }
    }

    // 3. Simpan Data (Parent + Multiple Children)
    public function store(Request $request)
    {
        // Validasi format JSON yang dikirim dari Alpine.js
        $validated = $request->validate([
            'date' => 'required|date',
            'merchant' => 'required|string|max:255',
            'total_amount' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|integer|min:0',
            'items.*.subtotal' => 'required|integer|min:0',
        ]);

        // Gunakan DB Transaction agar aman
        DB::beginTransaction();
        try {
            // A. Simpan Parent (Expense / Struknya)
            $expense = Expense::create([
                'user_id' => Auth::id(),
                'date' => $validated['date'],
                'merchant' => $validated['merchant'],
                'total_amount' => $validated['total_amount'],
            ]);

            // B. Simpan Children (Banyak Barang)
            $itemsData = [];
            foreach ($validated['items'] as $item) {
                $itemsData[] = [
                    'expense_id' => $expense->id,
                    'name' => $item['name'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert masal agar query database lebih cepat
            ExpenseItem::insert($itemsData);

            DB::commit();
            return response()->json(['message' => 'Data berhasil disimpan!'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save Expense Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menyimpan ke database'], 500);
        }
    }
}