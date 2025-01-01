<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\MemberAuthenticationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BulkMemberRegistrationController extends Controller
{
    public function uploadCSV(Request $request)
    {
        // Validate the file input
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $file = $request->file('file');
        $errors = [];

        try {
            // Ensure the file is a valid CSV
            $filePath = $file->getRealPath();
            $data = array_map('str_getcsv', file($filePath));

            // Check if the file is empty or invalid
            if (empty($data) || count($data) < 2) { // Includes header row
                return response()->json(['error' => 'The uploaded file is either empty or not properly formatted as a CSV.'], 400);
            }

            // Extract header row
            $header = array_shift($data);
            $emails = [];

            // Register the users and send emails
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // Adjust for header row
                try {
                    $userData = array_combine($header, $row);

                    // Validate email format
                    if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'error' => 'Invalid email format.',
                        ];
                        continue;
                    }

                    $password = Str::random(10); // Generate random password if not provided

                    // Create the user
                    $user = User::create([
                        'firstname' => $userData['firstname'] ?? null,
                        'lastname' => $userData['lastname'] ?? null,
                        'email' => $userData['email'],
                        'password' => Hash::make($userData['password'] ?? $password),
                        'phone' => $userData['phone'] ?? null,
                    ]);

                    // Send authentication email
                    Mail::to($user->email)->send(new MemberAuthenticationMail($user, $userData['password'] ?? $password));

                    $emails[] = $user->email;
                } catch (\Exception $e) {
                    // Log the error and add it to the errors array
                    Log::error("Error processing row $rowNumber: " . $e->getMessage());
                    $errors[] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'message' => 'Processing completed.',
                'successful_emails' => $emails,
                'errors' => $errors,
            ], count($errors) > 0 ? 206 : 200); // HTTP 206 Partial Content if there are errors
        } catch (\Exception $e) {
            // Log the file-level error
            Log::error("File upload error: " . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing the file.'], 400);
        }
    }



    public function verifyEmail($email, $password)
    {
        try {
            // Fetch user by email
            $user = User::where('email', $email)->first();

            // Check if user exists and password is valid
            if (!$user || !Hash::check($password, $user->password)) {
                return response()->json(['error' => 'Invalid verification details.'], 400);
            }

            // Debugging: Log current user state
            Log::info('User Found:', $user->toArray());

            // Update email_verified_at
            $user->update(['email_verified_at' => now()]);

            // Debugging: Log user after the update
            Log::info('User After Update:', $user->fresh()->toArray());

            return response()->json(['message' => 'Email verified successfully!']);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error verifying email: ' . $e->getMessage(), [
                'email' => $email,
                'password' => $password,
            ]);

            // Return error response
            return response()->json([
                'error' => 'An unexpected error occurred while verifying the email. Please try again later.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
