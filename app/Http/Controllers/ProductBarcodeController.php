<?php

namespace App\Http\Controllers;

use App\Models\MerchantProduct;
use App\Models\Team;
use Illuminate\Http\Request;

class ProductBarcodeController extends Controller
{
    public function print(Request $request, $tenantSlug)
    {
        // Resolve tenant
        $tenant = Team::where('slug', $tenantSlug)->firstOrFail();

        // Validate IDs
        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            abort(400, 'Invalid product IDs');
        }

        // Get label size configuration
        $size = $request->input('size', '50x30');
        $dimensions = explode('x', $size);
        $width = isset($dimensions[0]) ? (int) $dimensions[0] : 50;
        $height = isset($dimensions[1]) ? (int) $dimensions[1] : 30;

        if ($width <= 0 || $height <= 0) {
            $width = 50;
            $height = 30;
        }

        // Quantity per barcode
        $qty = (int) $request->input('qty', 1);
        if ($qty <= 0) {
            $qty = 1;
        }

        // Fetch products, scoped to the tenant
        $products = MerchantProduct::where('team_id', $tenant->id)
            ->whereIn('id', $ids)
            ->get();

        if ($products->isEmpty()) {
            abort(404, 'No products found');
        }

        // Prepare products items data
        $items = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                // Default to SKU or product ID if barcode is missing, to avoid empty barcodes
                'barcode' => filled($product->barcode) ? $product->barcode : ($product->sku ?: str_pad($product->id, 8, '0', STR_PAD_LEFT)),
            ];
        });

        return view('print.barcodes', [
            'items' => $items,
            'width' => $width,
            'height' => $height,
            'qty' => $qty,
        ]);
    }
}
