<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    // Store Module
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module = Module::create($validated);

        return response()->json($module, 201);
    }

    // Delete a module
    public function destroy($id)
    {
        Module::destroy($id);
        return response()->json(['message' => 'Module deleted successfully.']);
    }
}
