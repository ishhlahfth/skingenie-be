<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneralResponse;
use App\Models\Categories;
use App\Models\Criteria;

class RefOptionController extends Controller
{
    public function getCategoryOption() {
        try {
            $data = Categories::orderBy('created_at')->where('is_active', 1)->get();
            $result = [];
            foreach($data as $dt) {
                $result[] = [
                    'label' => $dt['category_name'],
                    'value' => $dt['id'],
                    'key' => $dt['id'],
                ];
            }
            return new GeneralResponse(true, 'Category Options', $result);
        } catch (\Throwable $th) {
            return new GeneralResponse(false, 'Category Options', $th->getMessage(), []);
        }
    }

    public function getSkinTypeOption() {
        try {
            $data = Criteria::where('type', 'Skin/Hair Type')->where('is_active', 1)->orderBy('created_at')->get();
            $result = [];
            foreach($data as $dt) {
                $result[] = [
                    'label' => $dt['criteria_name'],
                    'value' => $dt['id'],
                    'key' => $dt['id'],
                ];
            }
            return new GeneralResponse(true, 'Skin Type Options', $result);
        } catch (\Throwable $th) {
            return new GeneralResponse(false, 'Skin Type Options', $th->getMessage(), []);
        }
    }

    public function getSkinConcernOption() {
        try {
            $data = Criteria::where('type', 'Concern')->where('is_active', 1)->orderBy('created_at')->get();
            $result = [];
            foreach($data as $dt) {
                $result[] = [
                    'label' => $dt['criteria_name'],
                    'value' => $dt['id'],
                    'key' => $dt['id'],
                ];
            }
            return new GeneralResponse(true, 'Skin Type Options', $result);
        } catch (\Throwable $th) {
            return new GeneralResponse(false, 'Skin Type Options', $th->getMessage(), []);
        }
    }
}
