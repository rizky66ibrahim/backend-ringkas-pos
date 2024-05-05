<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

class AuthController extends BaseController
{
    // ! Register User
    public function register(Request $request)
    {
        try {
            // Validate Request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone_number' => 'required|string|unique:users',
                'address' => 'nullable|string',
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            // Check Validation
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // Upload Profile Picture
            $profile_picture = $this->uploadProfilePicture($request);

            // Normalize Phone Number
            $phone_number = $this->normalizePhoneNumber($request->phone_number);

            // Create User
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $phone_number,
                'address' => $request->address,
                'profile_picture' => $profile_picture,
            ]);

            // * Assign Role
            $role = Role::findByName('User');
            $user->assignRole($role);

            // * Create Token
            $token = $user->createToken('auth_token')->plainTextToken;

            // * Generate 6-digit OTP
            $otp = rand(100000, 999999);
            // * Save OTP to User
            $user->otp_code = $otp;
            $user->otp_created_at = Carbon::now();
            $user->save();

            // * Send OTP via Fonnte API
            $response = Http::withHeaders([
                'Authorization' => 'BHwjto1Nh6-1i+QuDsrR',
            ])->post('https://api.fonnte.com/send', [
                        'target' => $phone_number,
                        'message' => "Ini adalah kode rahasia untuk verifikasi email Anda. Mohon untuk tidak membagikan kode ini kepada siapapun. Gunakan kode ini di aplikasi kami: $otp"
                    ]);

            if ($response->status() == 200) {
                return $this->sendResponseWithToken($user, 'User registered successfully.', $token);
            } else {
                return $this->sendError('Error', 'Failed to send OTP');
            }
        } catch (\Exception $e) {
            return $this->sendError('Internal Server Error.', [$e->getMessage()]);
        }
    }

    // ! Verify Email
    public function verifyEmail(Request $request)
    {
        try {

            // * Validate Request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp_code' => 'required|numeric',
            ]);

            // * Check Validation
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // * Find User by Email
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError('User not found.', ['error' => 'Your email is not registered.'], 404);
            }

            // * Check if the User has already verified
            if ($user->email_verified !== null) {
                return $this->sendError('Email already verified.', ['error' => 'Email Already Verified'], 400);
            }

            // * Check OTP Code and OTP Code Expiry
            if ($user->otp_code !== $request->otp_code || $user->otp_created_at < Carbon::now()->subMinutes(5)) {
                return $this->sendError('Failed to verify email.', ['error' => 'Invalid OTP code or OTP code has expired.'], 400);
            }

            // * Update Verified Email
            $user->update([
                'email_verified_at' => Carbon::now(),
                'otp_code' => null,
                'otp_created_at' => null,
            ]);
            return $this->sendResponse('Email verified successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Internal Server Error.', ['error' => $e->getMessage()], 500);
        }
    }

    // ! Forgot Password
    public function forgotPassword(Request $request)
    {
        try {
            // * Validate Request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            // * Check Validation
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // * Find User by Email
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError('User not found.', ['error' => 'Your email is not registered.'], 404);
            }

            // * Generate 6-digit OTP
            $otp = rand(100000, 999999);
            // * Save OTP to User
            $user->otp_code = $otp;
            $user->otp_created_at = Carbon::now();
            $user->save();

            // * Send OTP via Fonnte API
            $response = Http::withHeaders([
                'Authorization' => 'BHwjto1Nh6-1i+QuDsrR',
            ])->post('https://api.fonnte.com/send', [
                        'target' => $user->phone_number,
                        'message' => "Ini adalah kode rahasia untuk reset password Anda. Mohon untuk tidak membagikan kode ini kepada siapapun. Gunakan kode ini di aplikasi kami: $otp"
                    ]);

            if ($response->status() == 200) {
                return $this->sendResponse('OTP sent successfully.');
            } else {
                return $this->sendError('Error', 'Failed to send OTP');
            }
        } catch (\Exception $e) {
            return $this->sendError('Internal Server Error.', ['error' => $e->getMessage()], 500);
        }
    }

    // ! Reset Password
    public function resetPassword(Request $request)
    {
        try {
            // * Validate Request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp_code' => 'required|numeric',
                'password' => 'required|string|min:6|confirmed',
            ]);

            // * Check Validation
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            }

            // * Find User by Email
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendError('User not found.', ['error' => 'Your email is not registered.'], 404);
            }

            // * Check OTP Code and OTP Code Expiry
            if ($user->otp_code !== $request->otp_code || $user->otp_created_at < Carbon::now()->subMinutes(5)) {
                return $this->sendError('Failed to reset password.', ['error' => 'Invalid OTP code or OTP code has expired.'], 400);
            }

            // * Update Password
            $user->update([
                'password' => Hash::make($request->password),
                'otp_code' => null,
                'otp_created_at' => null,
            ]);
            return $this->sendResponse('Password reset successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Internal Server Error.', ['error' => $e->getMessage()], 500);
        }
    }

    // ! Login
    public function login(Request $request)
    {
        try {
            // * Validate Request
            $validator = Validator::make($request->all(), [
                'credential' => 'required|string',
                'password' => 'required|string',
            ]);

            // * Check Validation
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 400);
            }

            $loginType = filter_var($request->credential, FILTER_VALIDATE_EMAIL) ? 'email' : (ctype_digit($request->credential) ? 'phone_number' : 'username');

            // Cek apakah input adalah nomor telepon dan normalisasi jika perlu
            if ($loginType === 'phone_number') {
                $normalizedPhoneNumber = $this->normalizePhoneNumber($request->credential);
                if ($normalizedPhoneNumber) {
                    // Jika nomor telepon sudah dinormalisasi, gunakan normalisasi tersebut
                    $request->merge(['credential' => $normalizedPhoneNumber]);
                } else {
                    // Jika tidak dapat dinormalisasi, gunakan input langsung
                    $request->merge(['credential' => $request->credential]);
                }
            }
            // * Otentikasi User
            if (Auth::attempt([$loginType => $request->credential, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('RingkasPOS')->plainTextToken;
                return $this->sendResponseWithToken($user, 'User login successfully.', $token);
            } else {
                return $this->sendError('Unauthorised.', ['error' => 'Unauthorised'], 401);
            }

        } catch (\Exception $e) {
            return $this->sendError('Internal Server Error.', ['error' => $e->getMessage()], 500);
        }
    }

    // ! Logout
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->sendResponse('User logged out successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Internal Server Error.', ['error' => $e->getMessage()], 500);
        }
    }

}