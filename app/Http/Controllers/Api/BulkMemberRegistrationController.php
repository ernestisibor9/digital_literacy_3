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
    //
    // app/Http/Controllers/BulkuserRegistrationController.php
public function uploadCSV(Request $request)
{
    // Validate the file input
    $validator = Validator::make($request->all(), [
        'file' => 'required|mimes:csv,txt|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()]);
    }

    // Process the uploaded CSV file
    $file = $request->file('file');
    $filePath = $file->getRealPath();
    $data = array_map('str_getcsv', file($filePath));

    // Extract header row
    $header = array_shift($data);
    $emails = [];

    // Register the users and send emails
    foreach ($data as $row) {
        $userData = array_combine($header, $row);
        $password = Str::random(10); // Generate random password if not provided

        $user = User::create([
            'firstname' => $userData['firstname'],
            'lastname' => $userData['lastname'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password'] ?? $password),
            'phone' => $userData['phone'],
        ]);

        // Send authentication email
        Mail::to($user->email)->send(new MemberAuthenticationMail($user, $userData['password'] ?? $password));

        $emails[] = $user->email;
    }

    return response()->json(['message' => 'users registered successfully!', 'emails' => $emails]);
}

public function verifyEmail($email, $password)
{
    $user = User::where('email', $email)->first();

    if (!$user || !Hash::check($password, $user->password)) {
        return response()->json(['error' => 'Invalid verification details.'], 400);
    }

    // Debugging to check the current state of the user
    Log::info('User Found:', $user->toArray());

    // Update email_verified_at
    $user->update(['email_verified_at' => now()]);

    // Debugging to check if the update happened
    Log::info('User After Update:', $user->fresh()->toArray());

    return response()->json(['message' => 'Email verified successfully!']);
}



}
