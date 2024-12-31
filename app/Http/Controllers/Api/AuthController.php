<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    //
    // Register user
    public function register(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/',
            'role' => 'required|in:admin,instructor,learner',
        ]);

        try {
            // Create the user
            $user = User::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);

            // Trigger email verification
            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'User registered successfully, a verification email has been sent.',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while registering the user.'], 500);
        }
    }
        /**
     * Resend the email verification link.
     */
    public function resendVerificationEmail(Request $request)
    {
        Log::info('Resend verification email initiated for: ' . optional($request->user())->email);

        if (!$request->user()) {
            return response()->json(['message' => 'User is not authenticated.'], 401);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            Log::info('Verification email sent to: ' . $request->user()->email);
            return response()->json(['message' => 'Verification email resent successfully.']);
        } catch (\Exception $e) {
            Log::error('Error sending verification email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to resend verification email.'], 500);
        }
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        // Check if user is fetched correctly
        $user = $request->user();

        // If the user is null, output an error message
        if (!$user) {
            return response()->json([
                'message' => 'User not found or the verification URL is invalid.'
            ], 400);
        }

        // Fulfill the email verification
        $request->fulfill();

        return response()->json([
            'message' => 'Email verified successfully!'
        ]);
    }
    // Login user
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while registering the user.'], 500);
        }
    }
    // Logout user
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while logging out the user.'], 500);
        }
    }
    // Forgotten password
    public function forgotPassword(Request $request)
    {
        Log::info('Forgot password request received: ', $request->all());

        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        Log::info('Validation passed');

        $status = Password::sendResetLink($validated);

        Log::info('Password reset status: ' . $status);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent successfully.'], 200);
        }

        return response()->json(['message' => 'Failed to send password reset link.'], 400);
    }

    //
    // Handle the reset password process
    public function resetPassword(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed', // Password confirmation
        ]);

        // Attempt to reset the password
        $status = Password::reset(
            $validated,
            function ($user) use ($validated) {
                // Set the new password
                $user->password = Hash::make($validated['password']);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.'], 200);
        }

        return response()->json(['message' => 'Failed to reset password.'], 400);
    }
}
