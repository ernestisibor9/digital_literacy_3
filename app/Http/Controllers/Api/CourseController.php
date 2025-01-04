<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        try {
            // Validate the request
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Create the course using the validated data
            $course = Course::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'created_at' => now(),
            ]);

            // Return a success response
            return response()->json([
                'course' => $course,
                'message' => 'Course created successfully',
            ], 201);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'errors' => $e->errors(), // Provides detailed validation error messages
            ], 422);
        } catch (\Exception $e) {
            // Handle general errors
            Log::error('Failed to create course: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the course.',
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
        try {
            // Attempt to find the course by ID
            $course = Course::findOrFail($id);

            // Validate the request data
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Update the course with validated data
            $course->update($validated);

            // Return the updated course as a JSON response
            return response()->json([
                'message' => 'Course updated successfully',
                'course' => $course,
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Handle case where the course is not found
            return response()->json([
                'message' => 'Course not found.',
            ], 404);
        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log and handle any other errors
            Log::error('An error occurred while updating the course: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the course.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Attempt to delete the course by ID
            $course = Course::find($id);

            // Check if course exists before deleting
            if (!$course) {
                return response()->json([
                    'message' => 'Course not found.',
                ], 404);
            }

            // Delete the course
            $course->delete();

            // Return success response
            return response()->json([
                'message' => 'Course deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to delete course: ' . $e->getMessage());

            // Return generic error response
            return response()->json([
                'message' => 'An error occurred while deleting the course.',
            ], 500);
        }
    }
}
