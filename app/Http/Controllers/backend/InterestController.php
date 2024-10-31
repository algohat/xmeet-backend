<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\Request;

class InterestController extends Controller
{
    public function index()
    {
        $interests = Interest::all();
        return view('backend.interests.index', compact('interests'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        Interest::create($request->all());
        return response()->json(['success' => 'Interest added successfully.']);
    }

    public function update(Request $request, Interest $interest)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $interest->update($request->all());
        return response()->json(['success' => 'Interest updated successfully.']);
    }

    public function destroy(Interest $interest)
    {
        $interest->delete();
        return response()->json(['success' => 'Interest deleted successfully.']);
    }
}
