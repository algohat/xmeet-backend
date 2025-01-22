<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Libraries\Membership;
use App\Models\Package;
use App\Models\PackageFeature;
use App\Models\UserPackage;
use App\Models\UserPackageFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PackageFeatureController extends Controller
{
    public function list()
    {
        $features = PackageFeature::with('package')->where('package_id', request()->id)->get();
        $data['package'] = Package::find(request()->id);
        $data['features'] = $features;
        return view('backend.packages.feature-index', $data);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'feature_type' => 'required|string',
            'title' => 'nullable',
            'value' => 'nullable',
            'time_limit' => 'nullable',
            'time_option' => 'nullable|string',
            'description' => 'nullable',
        ]);

        try {
            $feature = PackageFeature::create($validatedData);
            try {
                $userPackages = UserPackage::where('package_id', $feature->package_id)->get();

                foreach ($userPackages as $userPackage) {
                    try {
                        $userPackageFeature = new UserPackageFeature();
                        $userPackageFeature->user_package_id = $userPackage->id;
                        $userPackageFeature->package_feature_id = $feature->id;
                        $userPackageFeature->feature_type = $feature->feature_type ?? null;
                        $userPackageFeature->description = $feature->description ?? null;
                        $userPackageFeature->value = $feature->value ?? null;
                        $userPackageFeature->time_limit = $feature->time_limit ?? null;
                        $userPackageFeature->time_option = $feature->time_option ?? null;
                        $userPackageFeature->expiration_date_time = (new Membership())->getExpiredTime($feature->time_limit, strtolower($feature->time_option));
                        $userPackageFeature->save();
                    } catch (\Exception $e) {
                        return response()->json([
                            'error' => 'Failed to add the feature for all UserPackages. Please try again later.',
                            'message' => $e->getMessage(),
                        ], 500);
                    }
                }

            } catch (\Exception $e) {
               return response()->json([
                    'error' => 'Failed to add the feature for all UserPackages. Please try again later.',
                   'message' => $e->getMessage(),
                ], 500);
            }
            return response()->json(['success' => 'Feature added successfully.'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add the feature. Please try again later.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'id' => 'required|exists:package_features,id',
            'feature_type' => 'required|string',
            'title' => 'nullable|string',
            'value' => 'nullable',
            'time_limit' => 'nullable',
            'time_option' => 'nullable|string',
            'description' => 'nullable',
        ]);

        try {
            $item = PackageFeature::findOrFail($validatedData['id']);
            try {
                $userPackageFeatureUpdateData = array();
                if ($item->value != $request->value) {
                    $userPackageFeatureUpdateData['value'] = $request->value ?? null;
                }
                if ($item->tag != $request->tag) {
                    $userPackageFeatureUpdateData['feature_type'] = $request->feature_type ? $request->feature_type : $item->feature_type;
                }
                if ($item->time_limit != $request->time_limit) {
                    $userPackageFeatureUpdateData['time_limit'] = $request->time_limit ?? null;
                }
                if ($item->time_option != $request->time_option) {
                    $userPackageFeatureUpdateData['time_option'] = $request->time_option ?? null;
                }
                if (count($userPackageFeatureUpdateData) > 0) {
                    UserPackageFeature::where('package_feature_id', $item->id)->update($userPackageFeatureUpdateData);
                }
            } catch (\Exception $exception) {

            }
            $item->update($validatedData);
            return response()->json(['success' => 'Feature updated successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update the feature. Please try again later.',
            ], 500);
        }
    }

    public function status($id)
    {
        $feature = Package::find($id);
        $feature->status = !$feature->status;
        $feature->save();
        return redirect()->back()->with('success', 'Feature status updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $feature = PackageFeature::findOrFail($id);
            UserPackageFeature::where('package_feature_id', $feature->id)->delete();
            $feature->delete();
            return response()->json(['success' => 'Feature deleted successfully.'], 200);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to delete the feature. Please try again later.',
            ], 500);
        }
    }
}
