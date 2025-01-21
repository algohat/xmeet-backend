<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageFeature;
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
            ], 500);
        }
    }


    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:packages,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
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

    public function featureList()
    {
        $features = PackageFeature::where('package_id', request()->id)->get();
        return view('backend.packages.feature-index', compact('features'));
    }

    public function featureStore(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:0',
            'is_paid' => 'required|boolean',
        ]);

        try {
            Package::create($validatedData);
            return response()->json(['success' => 'Feature added successfully.'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add the feature. Please try again later.',
            ], 500);
        }
    }

    public function featureUpdate(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|exists:packages,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:0',
            'is_paid' => 'required|boolean',
        ]);

        try {
            $feature = Package::findOrFail($validatedData['id']);
            $feature->update($validatedData);
            return response()->json(['success' => 'Feature updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update the feature. Please try again later.',
            ], 500);
        }
    }

    public function featureStatus($id)
    {
        $feature = Package::find($id);
        $feature->status = !$feature->status;
        $feature->save();
        return redirect()->back()->with('success', 'Feature status updated successfully.');
    }

    public function featureDestroy($id)
    {
        try {
            $feature = Package::findOrFail($id);
            $feature->delete();
            return response()->json(['success' => 'Feature deleted successfully.'], 200);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to delete the feature. Please try again later.',
            ], 500);
        }
    }
}
