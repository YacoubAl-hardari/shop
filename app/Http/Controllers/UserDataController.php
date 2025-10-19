<?php

namespace App\Http\Controllers;

use App\Services\UserDataExportService;
use App\Services\UserDataDeletionService;
use App\Services\UserDataImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserDataController extends Controller
{
    public function __construct(
        protected UserDataExportService $exportService,
        protected UserDataDeletionService $deletionService,
        protected UserDataImportService $importService
    ) {}

    /**
     * Export user data as JSON file
     */
    public function export()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $data = $this->exportService->exportUserData($user);
            
            $filename = 'user_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            return response()->json($data)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account permanently
     */
    public function deleteAccount(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Require password confirmation for security
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password is required',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password'
                ], 403);
            }

            // Delete user account
            $this->deletionService->deleteUserAccount($user);

            // Logout user
            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import user data from JSON file
     */
    public function import(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Validate uploaded file
            $validator = Validator::make($request->all(), [
                'data_file' => 'required|file|mimes:json|max:10240', // Max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Read and parse JSON file
            $file = $request->file('data_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid JSON file'
                ], 422);
            }

            // Validate data structure
            if (!isset($data['user']) || !isset($data['merchants'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data structure'
                ], 422);
            }

            // Import data
            $this->importService->importUserData($user, $data);

            return response()->json([
                'success' => true,
                'message' => 'Data imported successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to import data: ' . $e->getMessage()
            ], 500);
        }
    }
}

