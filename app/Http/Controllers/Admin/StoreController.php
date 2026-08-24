<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreUserWelcomeMail;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    /**
     * Display listing with server-side DataTables.
     */
    public function index(Request $request)
    {
        // Server-side DataTables via AJAX — detect by 'draw' parameter DataTables sends
        if ($request->has('draw')) {
            $query = Store::query()->withCount('orders');

            // Filters
            if ($request->filled('filter_city')) {
                $query->where('city', $request->filter_city);
            }
            if ($request->filled('filter_printer_type')) {
                $query->where('printer_type', $request->filter_printer_type);
            }
            if ($request->filled('filter_status')) {
                $query->where('is_active', (int) $request->filter_status);
            }

            // Search
            if ($request->filled('search.value')) {
                $search = $request->input('search.value');
                $query->where(function ($q) use ($search) {
                    $q->where('store_name', 'like', "%{$search}%")
                      ->orWhere('store_code', 'like', "%{$search}%")
                      ->orWhere('owner_name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%")
                      ->orWhere('printer_ip_address', 'like', "%{$search}%");
                });
            }

            $totalRecords = Store::count();
            $filteredRecords = $query->count();

            // Sorting
            $orderColumn = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');
            $columns = ['store_name', 'store_code', 'owner_name', 'phone', 'city', 'printer_ip_address', 'orders_count', 'is_active', 'id'];
            $sortBy = $columns[$orderColumn] ?? 'store_name';
            $query->orderBy($sortBy, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $stores = $query->skip($start)->take($length)->get();

            $data = $stores->map(function ($store) {
                return [
                    'store_name'         => $store->store_name,
                    'store_code'         => $store->store_code,
                    'owner_name'         => $store->owner_name,
                    'phone'              => $store->phone,
                    'city'               => $store->city,
                    'printer_ip_address' => $store->printer_ip_address,
                    'is_active'          => $store->is_active,
                    'is_test'            => (bool)$store->is_test,
                    'orders_count'       => $store->orders_count,
                    'id'                 => $store->id,
                ];
            });

            return response()->json([
                'draw'            => intval($request->draw),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $data,
            ]);
        }

        // Get unique cities for filter dropdown
        $cities = Store::distinct()->pluck('city')->filter()->sort()->values();

        return view('admin.stores.index', compact('cities'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        return view('admin.stores.form');
    }

    /**
     * Store a newly created store.
     */
    public function store(StoreStoreRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $data['is_test']   = $request->has('is_test');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        $store = Store::create($data);

        $msg = 'Store created successfully.';

        if (!empty($request->password) && !empty($request->email)) {
            $userExists = User::where('email', $request->email)->exists();
            if (!$userExists) {
                User::create([
                    'name'     => $data['owner_name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($request->password),
                    'role'     => 'store_admin',
                    'phone'    => $data['phone'] ?? null,
                    'store_id' => $store->id,
                    'is_active'=> true
                ]);
                $msg = 'Store and Store Admin user created successfully.';
            } else {
                $msg = 'Store created successfully, but user creation failed (email already exists).';
            }
        }

        return redirect()->route('admin.stores.index')->with('success', $msg);
    }

    public function edit($id)
    {
        // Permission Check
        if (!auth()->user()->isAdmin() && auth()->user()->store_id != $id) {
            abort(403, 'Unauthorized access to this store.');
        }

        $store = Store::with('users')->findOrFail($id);
        return view('admin.stores.form', compact('store'));
    }

    public function update(UpdateStoreRequest $request, $id)
    {
        // Permission Check
        if (auth()->user()->isStaff() && !auth()->user()->isStoreAdmin() && !auth()->user()->isAdmin()) {
             abort(403, 'Staff cannot update store details.');
        }
        if (!auth()->user()->isAdmin() && auth()->user()->store_id != $id) {
            abort(403, 'Unauthorized access to this store.');
        }

        $store = Store::findOrFail($id);
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $data['is_test']   = $request->has('is_test');

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('stores/logos', 'public');
        }

        $store->update($data);

        $msg = 'Store updated successfully.';

        if (!empty($request->password)) {
            $userToUpdate = User::where('store_id', $store->id)
                ->where('role', 'store_admin')
                ->where('email', $request->email ?? '')
                ->first();

            if (!$userToUpdate) {
                 $userToUpdate = User::where('store_id', $store->id)
                     ->where('role', 'store_admin')
                     ->first();
            }

            if ($userToUpdate) {
                $userToUpdate->update([
                    'password' => Hash::make($request->password)
                ]);
                $msg = 'Store and Admin Password updated successfully.';
            } else if (!empty($request->email)) {
                $userExists = User::where('email', $request->email)->exists();
                if (!$userExists) {
                    User::create([
                        'name'     => $request->owner_name,
                        'email'    => $request->email,
                        'password' => Hash::make($request->password),
                        'role'     => 'store_admin',
                        'phone'    => $request->phone ?? null,
                        'store_id' => $store->id,
                        'is_active'=> true
                    ]);
                    $msg = 'Store updated and new Admin user created successfully.';
                }
            }
        }

        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.stores.index')->with('success', $msg);
        }
        
        return redirect()->back()->with('success', $msg);
    }

    /**
     * Soft delete the store.
     */
    public function destroy($id)
    {
        $store = Store::findOrFail($id);
        $store->delete();

        return response()->json([
            'success' => true,
            'message' => 'Store deleted successfully.',
        ]);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id)
    {
        $store = Store::findOrFail($id);
        $store->update(['is_active' => !$store->is_active]);

        return response()->json([
            'success'   => true,
            'message'   => 'Store status updated.',
            'is_active' => $store->is_active,
        ]);
    }

    /**
     * AJAX search endpoint for the print modal.
     */
    public function searchStores(Request $request)
    {
        $query = Store::active();

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('store_name', 'like', "%{$term}%")
                  ->orWhere('store_code', 'like', "%{$term}%")
                  ->orWhere('city', 'like', "%{$term}%");
            });
        }

        $stores = $query->select('id', 'store_name', 'city', 'printer_ip_address', 'is_active')
                        ->orderBy('store_name')
                        ->limit(20)
                        ->get();

        return response()->json([
            'success' => true,
            'stores'  => $stores,
        ]);
    }

    /**
     * Store (create/update) a user linked to this store.
     */
    public function storeUser(Request $request, $id)
    {
        $store = Store::findOrFail($id);

        $request->validate([
            'user_id'  => 'nullable|exists:users,id',
            'name'     => 'required|string|max:255',
            'email'    => [
                'required', 'email', 'max:255',
                $request->user_id ? Rule::unique('users')->ignore($request->user_id) : 'unique:users'
            ],
            'password' => $request->user_id ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,staff,store_admin',
            'phone'    => 'nullable|string|max:20',
            'is_active'=> 'nullable|boolean'
        ]);

        $userData = [
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'phone'    => $request->phone,
            'store_id' => $store->id,
            'is_active'=> $request->has('is_active')
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->user_id) {
            $user = User::findOrFail($request->user_id);
            $user->update($userData);
            $msg = 'User updated successfully.';
        } else {
            $user = User::create($userData);
            
            // Send Welcome Email
            try {
                Mail::to($user->email)->send(new StoreUserWelcomeMail($user, $request->password));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Welcome email failed: ' . $e->getMessage());
            }

            $msg = 'User created and welcome email sent successfully.';
        }

        return redirect()->route('admin.stores.edit', $store->id)
            ->with('success', $msg);
    }

    /**
     * Delete a store user.
     */
    public function deleteUser($storeId, $userId)
    {
        $user = User::where('store_id', $storeId)->findOrFail($userId);
        $user->delete();

        return redirect()->route('admin.stores.edit', $storeId)
            ->with('success', 'User removed successfully.');
    }

    /**
     * Test the FTP connection for a store's printer.
     */
    public function testFtp($id)
    {
        $store = Store::findOrFail($id);
        $result = $store->testFtpConnection();

        return response()->json($result);
    }
}
