<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageFeature;
use App\Models\Subscription;
use App\Models\UserPackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::all();
        return view('backend.packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'tag' => 'nullable',
            'validity' => 'nullable',
            'validity_type' => 'nullable',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:0',
            'is_paid' => 'required|boolean',
        ]);

        try {
            Package::create($validatedData);
            return response()->json(['success' => 'Package added successfully.'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add the package. Please try again later.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:packages,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'tag' => 'nullable',
            'validity' => 'nullable',
            'validity_type' => 'nullable',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:0',
            'is_paid' => 'required|boolean',
        ]);

        try {
            $package = Package::findOrFail($validatedData['id']);
            $package->update($validatedData);
            return response()->json(['success' => 'Package updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update the package. Please try again later.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    public function status($id)
    {
        $package = Package::find($id);
        $package->status = !$package->status;
        $package->save();
        return redirect()->back()->with('success', 'Package status updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $package = Package::findOrFail($id);
            $package->delete();
            return response()->json(['success' => 'Package deleted successfully.'], 200);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to delete the package. Please try again later.',
            ], 500);
        }
    }

    public function memberShipList(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        $status = $request->input('status');
        $package_id = $request->input('package_id');
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $items = UserPackage::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhereHas('user', function ($q) use ($value) {
                        $q->where('name', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%")
                            ->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    });
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $items = new UserPackage();
        }

        // package filter
        if ($request->has('package_id') && $package_id) {
            $items->where('package_id', $package_id);
            $query_param['package_id'] = $package_id;
        }
        // Status filter
        if ($request->has('status') && $status) {
            $items->where('status', $status);
            $query_param['status'] = $status;
        }

        $items = $items->where('package_id', '!=', 1)
            ->with(['package', 'userPackageFeature'])
            ->latest()
            ->paginate(10)
            ->appends($query_param);

        // Fetch invoice paths
        foreach ($items as $item) {
            $subscription = Subscription::where('user_id', $item->user_id)
                ->where('package_id', $item->package_id)
                ->first();
            $item->invoice_path = $subscription ? $subscription->invoice_path : null;
        }

        $packages = Package::where('id', '!=', 1)->get();

        $data['items'] = $items;
        $data['search'] = $search;
        $data['packages'] = $packages;
        return view('backend.packages.membership-list', $data);
    }

    public function salesIndex()
    {
        $totalSales = UserPackage::where('package_id', '!=', 1)
            ->where('payment_status', '=', 2)
            ->sum('price');

        $packageSales = UserPackage::where('package_id', '!=', 1)
            ->with('package')
            ->where('status', '!=', 7)
            ->get()
            ->groupBy('package_id')
            ->map(function ($items) {
                $totalSales = $items->sum('price');
                return [
                    'package_name' => $items->first()->package->name,
                    'package_type' => $items->first()->package->type,
                    'package_tag' => $items->first()->package->tag,
                    'total_sales' => $totalSales,
                    'validation_time' => $items->first()->package_validity.' '.$items->first()->package_validity_type,
                    'count' => $items->count()
                ];
            })
            ->values();
        $data['totalSales'] = $totalSales ?? 0;
        $data['packageSales'] = $packageSales ?? [];
        return view('backend.packages.sales-index', $data);
    }
}
