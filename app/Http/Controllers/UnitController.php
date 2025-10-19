<?php

namespace App\Http\Controllers;

use App\Helpers\UnitManager;
use App\Enums\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    /**
     * Get all unit categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = UnitManager::getCategories();
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get units by category
     */
    public function getUnitsByCategory(string $category): JsonResponse
    {
        $units = UnitManager::getUnitsInCategory($category);
        $unitOptions = UnitManager::getUnitOptions($category);
        
        return response()->json([
            'success' => true,
            'data' => [
                'units' => $units,
                'options' => $unitOptions,
                'category' => $category
            ]
        ]);
    }

    /**
     * Get unit information
     */
    public function getUnitInfo(string $unit): JsonResponse
    {
        $unitInfo = UnitManager::getComprehensiveUnitInfo($unit);
        
        return response()->json([
            'success' => true,
            'data' => $unitInfo
        ]);
    }

    /**
     * Get scale options for category
     */
    public function getScaleOptions(string $category): JsonResponse
    {
        $scaleOptions = UnitManager::getScaleOptions($category);
        
        return response()->json([
            'success' => true,
            'data' => [
                'scales' => $scaleOptions,
                'category' => $category
            ]
        ]);
    }

    /**
     * Get balance information for category
     */
    public function getBalanceInfo(string $category): JsonResponse
    {
        $balanceInfo = UnitManager::getBalanceInfo($category);
        
        return response()->json([
            'success' => true,
            'data' => $balanceInfo
        ]);
    }

    /**
     * Process unit selection
     */
    public function processUnitSelection(Request $request): JsonResponse
    {
        $request->validate([
            'unit' => 'required|string',
            'quantity' => 'nullable|numeric|min:0'
        ]);

        $unit = $request->input('unit');
        $quantity = $request->input('quantity', 1);

        $result = UnitManager::processUnitSelection($unit, $quantity);
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Calculate total price
     */
    public function calculateTotalPrice(Request $request): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'unit' => 'required|string'
        ]);

        $quantity = $request->input('quantity');
        $unitPrice = $request->input('unit_price');
        $unit = $request->input('unit');

        $totalPrice = UnitManager::calculateTotalPrice($quantity, $unitPrice, $unit);
        
        return response()->json([
            'success' => true,
            'data' => [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'unit' => $unit,
                'total_price' => $totalPrice,
                'formatted_quantity' => UnitManager::formatQuantity($quantity, $unit)
            ]
        ]);
    }

    /**
     * Convert quantity between units
     */
    public function convertQuantity(Request $request): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0',
            'from_unit' => 'required|string',
            'to_unit' => 'required|string'
        ]);

        $quantity = $request->input('quantity');
        $fromUnit = $request->input('from_unit');
        $toUnit = $request->input('to_unit');

        try {
            $convertedQuantity = UnitManager::convertQuantity($quantity, $fromUnit, $toUnit);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'original_quantity' => $quantity,
                    'original_unit' => $fromUnit,
                    'converted_quantity' => $convertedQuantity,
                    'converted_unit' => $toUnit,
                    'formatted_original' => UnitManager::formatQuantity($quantity, $fromUnit),
                    'formatted_converted' => UnitManager::formatQuantity($convertedQuantity, $toUnit)
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get unit selection data for forms
     */
    public function getUnitSelectionData(): JsonResponse
    {
        $data = UnitManager::getUnitSelectionData();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Validate quantity for category
     */
    public function validateQuantity(Request $request): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|numeric',
            'category' => 'required|string'
        ]);

        $quantity = $request->input('quantity');
        $category = $request->input('category');

        $isValid = UnitManager::validateQuantity($quantity, $category);
        $validationRules = UnitManager::getValidationRules($category);
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_valid' => $isValid,
                'quantity' => $quantity,
                'category' => $category,
                'validation_rules' => $validationRules
            ]
        ]);
    }

    /**
     * Get all available units
     */
    public function getAllUnits(): JsonResponse
    {
        $allUnits = ProductUnit::values();
        $unitOptions = ProductUnit::arabicOptions();
        
        return response()->json([
            'success' => true,
            'data' => [
                'units' => $allUnits,
                'options' => $unitOptions
            ]
        ]);
    }
}
