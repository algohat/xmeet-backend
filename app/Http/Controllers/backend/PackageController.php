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
}
