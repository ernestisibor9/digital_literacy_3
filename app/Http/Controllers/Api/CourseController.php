<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            // Check if the authenticated user is an admin
            if (!auth()->user() || auth()->user()->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized. Only admins can create courses.'], 403);
            }

            // Validate the request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Create the course
            $course = Course::create($validated);

            return response()->json([
                'course' => $course,
                'message' => 'Course created successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return response()->json([
                'error' => 'Validation error.',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::error('Error creating course: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            // Return a generic error response
            return response()->json([
                'error' => 'An unexpected error occurred while creating the course. Please try again later.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
