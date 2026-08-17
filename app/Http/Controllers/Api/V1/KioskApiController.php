<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kiosk;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class KioskApiController extends Controller
{
    /**
     * Helper method to format a single Kiosk record
     */
    private function formatKiosk(Kiosk $kiosk): array
    {
        $fileUrl = null;
        if ($kiosk->file_path) {
            $fileUrl = \Illuminate\Support\Str::startsWith($kiosk->file_path, ['http://', 'https://'])
                ? $kiosk->file_path
                : asset('storage/' . ltrim($kiosk->file_path, '/'));
        }

        return [
            'id'           => (int) $kiosk->id,
            'store_id'     => $kiosk->store_id ? (int) $kiosk->store_id : null,
            'store'        => $kiosk->store ? [
                'id'         => (int) $kiosk->store->id,
                'store_name' => $kiosk->store->store_name,
                'store_code' => $kiosk->store->store_code,
            ] : null,
            'product_id'   => $kiosk->product_id ? (int) $kiosk->product_id : null,
            'product'      => $kiosk->product ? [
                'id'   => (int) $kiosk->product->id,
                'name' => $kiosk->product->name,
                'slug' => $kiosk->product->slug,
            ] : null,
            'kiosk'        => (bool) $kiosk->kiosk,
            'file_path'    => $kiosk->file_path,
            'file_url'     => $fileUrl,
            'quantity'     => (int) $kiosk->quantity,
            'price'        => (float) $kiosk->price,
            'total_amount' => (float) $kiosk->total_amount,
            'currency'     => $kiosk->currency ?? 'INR',
            'printer_tray' => $kiosk->printer_tray,
            'print_size'   => $kiosk->print_size,
            'created_at'   => $kiosk->created_at ? $kiosk->created_at->toISOString() : null,
            'updated_at'   => $kiosk->updated_at ? $kiosk->updated_at->toISOString() : null,
        ];
    }

    /**
     * GET /api/v1/kiosks
     * Get multiple kiosk records with search, filter, and pagination
     */
    public function index(Request $request)
    {
        $query = Kiosk::with(['store', 'product']);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('kiosk') && $request->kiosk !== null && $request->kiosk !== '') {
            $query->where('kiosk', filter_var($request->kiosk, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('printer_tray')) {
            $query->where('printer_tray', 'like', "%{$request->printer_tray}%");
        }

        if ($request->filled('print_size')) {
            $query->where('print_size', 'like', "%{$request->print_size}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('file_path', 'like', "%{$search}%")
                  ->orWhere('printer_tray', 'like', "%{$search}%")
                  ->orWhere('print_size', 'like', "%{$search}%")
                  ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);

        if ($perPage === -1) {
            $kiosks = $query->latest()->get();
            return response()->json([
                'success' => true,
                'count'   => $kiosks->count(),
                'data'    => $kiosks->map(fn($k) => $this->formatKiosk($k)),
            ]);
        }

        $paginated = $query->latest()->paginate($perPage)->withQueryString();

        return response()->json([
            'success'    => true,
            'data'       => collect($paginated->items())->map(fn($k) => $this->formatKiosk($k)),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ]
        ]);
    }

    /**
     * GET /api/v1/kiosks/{id}
     * Get single kiosk record by ID
     */
    public function show($id)
    {
        $kiosk = Kiosk::with(['store', 'product'])->find($id);

        if (!$kiosk) {
            return response()->json([
                'success' => false,
                'message' => 'Kiosk record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatKiosk($kiosk),
        ]);
    }

    /**
     * POST /api/v1/kiosks
     * Submit kiosk record(s) - Supports both single item submission and batch/multiple submissions
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // Check if multiple items are passed as an array or under 'items' key
        $isBatch = false;
        $itemsToProcess = [];

        if ($request->has('items') && is_array($request->input('items'))) {
            $isBatch = true;
            $itemsToProcess = $request->input('items');
        } elseif (isset($input[0]) && is_array($input[0])) {
            $isBatch = true;
            $itemsToProcess = $input;
        }

        if ($isBatch) {
            return $this->storeBatch($request, $itemsToProcess);
        }

        return $this->storeSingle($request);
    }

    /**
     * Process single kiosk submission
     */
    private function storeSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id'     => 'required|exists:stores,id',
            'product_id'   => 'nullable|exists:products,id',
            'kiosk'        => 'nullable|boolean',
            'file_path'    => 'nullable|string|max:255',
            'file'         => 'nullable|file|mimes:pdf,jpeg,jpg,png,zip,doc,docx|max:30720',
            'quantity'     => 'nullable|integer|min:1',
            'price'        => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'currency'     => 'nullable|string|max:3',
            'printer_tray' => 'nullable|string|max:255',
            'print_size'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('kiosks', 'public');
            $validated['file_path'] = $path;
        }

        $validated['kiosk'] = $request->has('kiosk') ? $request->boolean('kiosk') : true;
        $validated['quantity'] = (int) ($validated['quantity'] ?? 1);
        $validated['price'] = (float) ($validated['price'] ?? 0.00);
        $validated['currency'] = strtoupper($validated['currency'] ?? 'INR');

        if (!isset($validated['total_amount']) || $validated['total_amount'] === null || $validated['total_amount'] === '') {
            $validated['total_amount'] = $validated['quantity'] * $validated['price'];
        } else {
            $validated['total_amount'] = (float) $validated['total_amount'];
        }

        unset($validated['file']);

        $kiosk = Kiosk::create($validated);
        $kiosk->load(['store', 'product']);

        return response()->json([
            'success' => true,
            'message' => 'Kiosk record submitted successfully.',
            'data'    => $this->formatKiosk($kiosk),
        ], 201);
    }

    /**
     * Process batch/multiple kiosk submission
     */
    private function storeBatch(Request $request, array $items)
    {
        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No items provided for batch submission.',
            ], 422);
        }

        $createdKiosks = [];
        $errors = [];

        foreach ($items as $index => $itemData) {
            $validator = Validator::make($itemData, [
                'store_id'     => 'required|exists:stores,id',
                'product_id'   => 'nullable|exists:products,id',
                'kiosk'        => 'nullable|boolean',
                'file_path'    => 'nullable|string|max:255',
                'quantity'     => 'nullable|integer|min:1',
                'price'        => 'nullable|numeric|min:0',
                'total_amount' => 'nullable|numeric|min:0',
                'currency'     => 'nullable|string|max:3',
                'printer_tray' => 'nullable|string|max:255',
                'print_size'   => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors();
                continue;
            }

            $data = $validator->validated();
            $data['kiosk'] = isset($data['kiosk']) ? (bool) $data['kiosk'] : true;
            $data['quantity'] = (int) ($data['quantity'] ?? 1);
            $data['price'] = (float) ($data['price'] ?? 0.00);
            $data['currency'] = strtoupper($data['currency'] ?? 'INR');

            if (!isset($data['total_amount']) || $data['total_amount'] === null || $data['total_amount'] === '') {
                $data['total_amount'] = $data['quantity'] * $data['price'];
            } else {
                $data['total_amount'] = (float) $data['total_amount'];
            }

            $kiosk = Kiosk::create($data);
            $kiosk->load(['store', 'product']);
            $createdKiosks[] = $this->formatKiosk($kiosk);
        }

        if (!empty($errors) && empty($createdKiosks)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed for all batch items.',
                'errors'  => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($createdKiosks) . ' kiosk record(s) submitted successfully.',
            'count'   => count($createdKiosks),
            'data'    => $createdKiosks,
            'errors'  => $errors ?: null,
        ], 201);
    }

    /**
     * PUT/PATCH /api/v1/kiosks/{id}
     * Update an existing kiosk record
     */
    public function update(Request $request, $id)
    {
        $kiosk = Kiosk::find($id);

        if (!$kiosk) {
            return response()->json([
                'success' => false,
                'message' => 'Kiosk record not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'store_id'     => 'sometimes|required|exists:stores,id',
            'product_id'   => 'nullable|exists:products,id',
            'kiosk'        => 'nullable|boolean',
            'file_path'    => 'nullable|string|max:255',
            'file'         => 'nullable|file|mimes:pdf,jpeg,jpg,png,zip,doc,docx|max:30720',
            'quantity'     => 'nullable|integer|min:1',
            'price'        => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'currency'     => 'nullable|string|max:3',
            'printer_tray' => 'nullable|string|max:255',
            'print_size'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('file')) {
            if ($kiosk->file_path && Storage::disk('public')->exists($kiosk->file_path)) {
                Storage::disk('public')->delete($kiosk->file_path);
            }
            $path = $request->file('file')->store('kiosks', 'public');
            $validated['file_path'] = $path;
        }

        if ($request->has('kiosk')) {
            $validated['kiosk'] = $request->boolean('kiosk');
        }

        if (isset($validated['currency'])) {
            $validated['currency'] = strtoupper($validated['currency']);
        }

        $quantity = isset($validated['quantity']) ? (int) $validated['quantity'] : $kiosk->quantity;
        $price = isset($validated['price']) ? (float) $validated['price'] : $kiosk->price;

        if (isset($validated['total_amount'])) {
            $validated['total_amount'] = (float) $validated['total_amount'];
        } elseif (isset($validated['quantity']) || isset($validated['price'])) {
            $validated['total_amount'] = $quantity * $price;
        }

        unset($validated['file']);

        $kiosk->update($validated);
        $kiosk->load(['store', 'product']);

        return response()->json([
            'success' => true,
            'message' => 'Kiosk record updated successfully.',
            'data'    => $this->formatKiosk($kiosk),
        ]);
    }

    /**
     * DELETE /api/v1/kiosks/{id}
     * Delete a kiosk record
     */
    public function destroy($id)
    {
        $kiosk = Kiosk::find($id);

        if (!$kiosk) {
            return response()->json([
                'success' => false,
                'message' => 'Kiosk record not found.',
            ], 404);
        }

        if ($kiosk->file_path && Storage::disk('public')->exists($kiosk->file_path)) {
            Storage::disk('public')->delete($kiosk->file_path);
        }

        $kiosk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kiosk record deleted successfully.',
        ]);
    }
}
