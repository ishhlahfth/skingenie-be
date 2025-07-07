<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneralResponse;
use App\Models\RecommendSubmission;
use App\Models\Skincare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $usage = RecommendSubmission::count();
            $product = Skincare::count();
            $mostProduct = RecommendSubmission::with(['ref_skincare'])->selectRaw('rec_skincare_uuid, COUNT(rec_skincare_uuid) as total')->groupBy('rec_skincare_uuid')->orderBy('total', 'DESC')->first();
            $mostCategory = RecommendSubmission::with(['ref_category'])->selectRaw('category_uuid, COUNT(category_uuid) as total')->groupBy('category_uuid')->orderBy('total', 'DESC')->first();

            $response = [
                'usage' => $usage,
                'product' => $product,
                'most_product' => $mostProduct,
                'most_category' => $mostCategory,
            ];
            return new GeneralResponse(true, 'Dashboard ', $response);
        } catch (\Throwable $th) {
            return new GeneralResponse(false, 'Dashboard ' . $th->getMessage(), []);
        }
    }
}
