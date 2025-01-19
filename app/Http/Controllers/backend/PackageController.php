<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Package;
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
        $request->validate(['name' => 'required|string|max:255']);

        Package::create($request->all());
        return response()->json(['success' => 'Package added successfully.']);
    }

    public function update(Request $request, Package $package)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $package->update($request->all());
        return response()->json(['success' => 'Package updated successfully.']);
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return response()->json(['success' => 'Package deleted successfully.']);
    }
}
