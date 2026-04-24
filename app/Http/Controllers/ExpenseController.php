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
    // 1. Dashboard Expense
    public function index()
    {
        $userId = Auth::id();
        $now = \Carbon\Carbon::now();
        $today = \Carbon\Carbon::today();

        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth()->toDateString();

        $thisMonth = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();
        $totalThisMonth = $thisMonth->sum('total_amount');
        $countThisMonth = $thisMonth->count();
        $avgThisMonth = $countThisMonth > 0 ? $totalThisMonth / $countThisMonth : 0;

        $totalLastMonth = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->sum('total_amount');

        $diffPercent = $totalLastMonth > 0
            ? (($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100
            : 0;

        $dayNames = [
            'Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab',
            'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab',
        ];
        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dailyTotal = Expense::where('user_id', $userId)
                ->whereDate('date', $date->toDateString())
                ->sum('total_amount');
            $chartData->push([
                'label' => $dayNames[$date->format('D')] ?? '',
                'total' => $dailyTotal,
            ]);
        }
        $maxDay = $chartData->max('total');
        $chartData = $chartData->map(function ($day) use ($maxDay) {
            return [
                'label' => $day['label'],
                'total' => $day['total'],
                'percentage' => $maxDay > 0 ? max(($day['total'] / $maxDay) * 100, 4) : 4,
            ];
        });

        $recentExpenses = Expense::with('items')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $grandTotal = Expense::where('user_id', $userId)->sum('total_amount');
        $totalExpenses = Expense::where('user_id', $userId)->count();

        $monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $thisMonthLabel = $monthNames[$now->month - 1] . ' ' . $now->year;

        return view('dashboard.money', compact(
            'totalThisMonth', 'countThisMonth', 'avgThisMonth',
            'totalLastMonth', 'diffPercent', 'chartData',
            'recentExpenses', 'grandTotal', 'totalExpenses',
            'thisMonthLabel'
        ));
    }

    // Halaman form scan & simpan
    public function create()
    {
        return view('dashboard.money_create');
    }

    // Halaman history + filter tanggal
    public function history(Request $request)
    {
        $userId = Auth::id();
        $now = \Carbon\Carbon::now();

        $preset = $request->get('preset', 'this_month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Custom range overrides preset
        if ($startDate && $endDate) {
            $preset = 'custom';
            try {
                $from = \Carbon\Carbon::parse($startDate)->startOfDay();
                $to = \Carbon\Carbon::parse($endDate)->endOfDay();
            } catch (\Exception $e) {
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $preset = 'this_month';
            }
        } else {
            [$from, $to] = match ($preset) {
                'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'last_7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
                'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
                'all' => [null, null],
                default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            };
            $startDate = $from?->toDateString();
            $endDate = $to?->toDateString();
        }

        $query = Expense::with('items')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($from && $to) {
            $query->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        }

        $expenses = $query->paginate(15)->withQueryString();

        $summaryQuery = Expense::where('user_id', $userId);
        if ($from && $to) {
            $summaryQuery->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        }
        $summaryTotal = (clone $summaryQuery)->sum('total_amount');
        $summaryCount = (clone $summaryQuery)->count();
        $summary = [
            'total' => $summaryTotal,
            'count' => $summaryCount,
            'avg' => $summaryCount > 0 ? $summaryTotal / $summaryCount : 0,
        ];

        $presetLabels = [
            'today' => 'Hari Ini',
            'last_7_days' => '7 Hari',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'all' => 'Semua',
            'custom' => 'Custom',
        ];

        return view('dashboard.money_history', compact(
            'expenses', 'summary', 'preset', 'startDate', 'endDate', 'presetLabels'
        ));
    }

    // 2. Scan Gambar dengan Gemini (Ekstrak Array)
    public function scan(Request $request)
    {
        $request->validate(['receipt' => 'required|image|max:5120']);

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error('GEMINI_API_KEY tidak ditemukan di .env');
            return response()->json(['error' => 'GEMINI_API_KEY belum diset di .env'], 500);
        }

        try {
            $image = $request->file('receipt');
            $base64Image = base64_encode(file_get_contents($image->path()));
            $mimeType = $image->getMimeType();

            $prompt = "Kamu adalah sistem kasir otomatis. Ekstrak struk belanja ini ke JSON valid.

Format JSON WAJIB:
{
    \"date\": \"YYYY-MM-DD\",
    \"merchant\": \"Nama Toko\",
    \"items\": [
        { \"name\": \"Nama Barang\", \"qty\": 1, \"price\": 10000, \"subtotal\": 10000 }
    ],
    \"total_amount\": 100000
}

Aturan KETAT:
- date: format YYYY-MM-DD. Kalau tanggal di struk DD/MM/YYYY atau DD-MM-YYYY, konversi. Kalau tidak ada, pakai tanggal hari ini.
- merchant: nama toko, string kosong jika tidak terbaca.
- qty: INTEGER minimal 1.
- price, subtotal, total_amount: INTEGER Rupiah POLOS tanpa titik, koma, atau simbol Rp. Contoh 10000 bukan 10.000 atau \"Rp 10.000\".
- subtotal = qty * price.
- items: array, boleh kosong jika tidak terbaca.

BALAS HANYA JSON VALID. Tidak boleh ada markdown, tidak boleh ada teks penjelasan.";

            $models = ['gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-2.0-flash-lite'];
            $rawText = null;
            $lastError = null;

            foreach ($models as $model) {
                try {
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                    $response = Http::timeout(45)->post($url, [
                        'contents' => [[
                            'parts' => [
                                ['text' => $prompt],
                                ['inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image,
                                ]],
                            ],
                        ]],
                        'generationConfig' => [
                            'temperature' => 0.1,
                            'topK' => 1,
                            'topP' => 1,
                            'maxOutputTokens' => 4096,
                            'responseMimeType' => 'application/json',
                        ],
                    ]);

                    if (!$response->successful()) {
                        $lastError = 'HTTP ' . $response->status() . ': ' . $response->body();
                        Log::warning("Gagal model {$model}: {$lastError}");
                        continue;
                    }

                    $result = $response->json();
                    $candidateText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!$candidateText) {
                        $lastError = "Response kosong dari model {$model}";
                        Log::warning($lastError);
                        continue;
                    }

                    $rawText = $candidateText;
                    Log::info("Scan berhasil dengan model {$model}");
                    break;
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning("Error model {$model}: {$lastError}");
                    continue;
                }
            }

            if (!$rawText) {
                throw new \Exception('Semua model AI gagal. ' . ($lastError ?? ''));
            }

            $data = $this->extractJson($rawText);
            if (!$data) {
                Log::error('AI output bukan JSON valid: ' . $rawText);
                throw new \Exception('AI tidak mengembalikan JSON yang valid.');
            }

            return response()->json($this->normalizeExpenseData($data));

        } catch (\Exception $e) {
            Log::error('AI Scan Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Scan gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function extractJson(string $text): ?array
    {
        $clean = trim($text);
        $clean = preg_replace('/```(?:json)?\s*/i', '', $clean);
        $clean = str_replace('```', '', $clean);
        $clean = trim($clean);

        $data = json_decode($clean, true);
        if (is_array($data)) {
            return $data;
        }

        // Fallback: cari objek JSON pertama di dalam teks
        if (preg_match('/\{.*\}/s', $clean, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    private function normalizeExpenseData(array $data): array
    {
        $rawDate = $data['date'] ?? null;
        $ts = $rawDate ? strtotime($rawDate) : false;
        $data['date'] = $ts ? date('Y-m-d', $ts) : date('Y-m-d');

        $data['merchant'] = isset($data['merchant']) ? trim((string) $data['merchant']) : '';

        $items = $data['items'] ?? [];
        if (!is_array($items)) {
            $items = [];
        }

        $normalizedItems = [];
        foreach ($items as $item) {
            $name = isset($item['name']) ? trim((string) $item['name']) : '';
            if ($name === '') {
                continue;
            }

            $qty = max(1, $this->cleanNumber($item['qty'] ?? 1));
            $price = max(0, $this->cleanNumber($item['price'] ?? 0));
            $subtotal = $this->cleanNumber($item['subtotal'] ?? 0);
            if ($subtotal <= 0) {
                $subtotal = $qty * $price;
            }

            $normalizedItems[] = [
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $subtotal,
            ];
        }
        $data['items'] = $normalizedItems;

        $total = $this->cleanNumber($data['total_amount'] ?? 0);
        if ($total <= 0) {
            $total = array_sum(array_column($normalizedItems, 'subtotal'));
        }
        $data['total_amount'] = $total;

        return $data;
    }

    private function cleanNumber($value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (int) round($value);
        }
        if (!is_string($value)) {
            return 0;
        }
        $digits = preg_replace('/[^\d]/', '', $value);
        return $digits === '' ? 0 : (int) $digits;
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
            return response()->json(['error' => 'Gagal menyimpan ke database: ' . $e->getMessage()], 500);
        }
    }
}