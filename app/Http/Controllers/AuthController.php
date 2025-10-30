<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request) {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
        ]);
        return response()->json($user, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['message' => 'Invalid Credentials'], 401);
        }

        return response()->json([
            'token' => $token,
            'user'  => auth()->user()
        ]);
    }

    /**
     * Update company information for authenticated user
     */
    public function updateCompanyInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_address' => 'sometimes|string|max:1000',
            'zip_code' => 'sometimes|string|max:20',
            'company_phone' => 'sometimes|string|max:20',
            'website' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            $updateData = $request->only([
                'company_name',
                'company_address',
                'zip_code', 
                'company_phone',
                'website'
            ]);

            // Handle logo if provided
            if ($request->has('company_logo') && $request->company_logo) {
                $logoData = $request->company_logo;
                
                // Check if it's a base64 image
                if (preg_match('/^data:image\/(\w+);base64,/', $logoData, $type)) {
                    $imageData = substr($logoData, strpos($logoData, ',') + 1);
                    $imageType = strtolower($type[1]); // jpg, png, gif
                    
                    // Check if image type is valid
                    if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        return response()->json([
                            'message' => 'Invalid image type. Only JPG, PNG and GIF are allowed.'
                        ], 422);
                    }
                    
                    $imageData = base64_decode($imageData);
                    
                    if ($imageData === false) {
                        return response()->json([
                            'message' => 'Invalid image data'
                        ], 422);
                    }
                    
                    // Generate unique filename
                    $filename = 'company-logo-' . $user->id . '-' . Str::random(10) . '.' . $imageType;
                    $directory = public_path('company-logos');
                    $filePath = $directory . '/' . $filename;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    
                    // Delete old logo if exists
                    if ($user->company_logo) {
                        $oldFilePath = public_path($user->company_logo);
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                    
                    // Store new logo in public folder
                    file_put_contents($filePath, $imageData);
                    
                    // Store relative path in database
                    $updateData['company_logo'] = 'company-logos/' . $filename;
                } else {
                    // If it's not base64, assume it's already a file path/URL
                    $updateData['company_logo'] = $logoData;
                }
            } elseif ($request->has('company_logo') && empty($request->company_logo)) {
                // If company_logo is empty string, remove the logo
                if ($user->company_logo) {
                    $oldFilePath = public_path($user->company_logo);
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                $updateData['company_logo'] = null;
            }

            // Update user data
            $user->update($updateData);
            $user->refresh();

            // Prepare response with full logo URL if exists
            $userData = $user->toArray();
            if ($user->company_logo) {
                $userData['company_logo_url'] = url($user->company_logo);
            }

            return response()->json([
                'message' => 'Company information updated successfully',
                'user' => $userData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update company information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user with company information
     */
    public function getCurrentUser()
    {
        $user = auth()->user();
        return response()->json(['user' => $user], 200);
    }
}