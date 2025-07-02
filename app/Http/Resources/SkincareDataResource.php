<?php

namespace App\Http\Resources;

use App\Models\Criteria;
use Illuminate\Http\Resources\Json\JsonResource;

class SkincareDataResource extends JsonResource
{

    public function toArray($request)
    {
        $criteriaList = $this->ref_criteria;

        return [
            'id' => $this->id,
            'name' => $this->skincare_name,
            'type' => $this->typeWrapper($criteriaList),
            'concern' => $this->concernWrapper($criteriaList),
            'price' => $this->priceRateValue($this->min_price, $this->max_price),
            'recommend' => $this->recommendRateValue($this->total_recommend),
        ];
    }

    function typeWrapper($criterias)
    {
        $result = [];
        foreach ($criterias as $cr) {
            $criteriaDetail = Criteria::where('id', $cr->criteria_id)->first();
            if ($criteriaDetail->type === 'Skin/Hair Type' && $criteriaDetail->criteria_value) {
                $result[] = [
                    'id' => $criteriaDetail->id,
                    'name' => $criteriaDetail->criteria_name,
                    'value' => $criteriaDetail->criteria_value
                ];
            }
        }
        return $result;
    }

    function concernWrapper($criterias)
    {
        $result = [];
        foreach ($criterias as $cr) {
            $criteriaDetail = Criteria::where('id', $cr->criteria_id)->first();
            if ($criteriaDetail->type === 'Concern' && $criteriaDetail->criteria_value) {
                $result[] = [
                    'id' => $criteriaDetail->id,
                    'name' => $criteriaDetail->criteria_name,
                    'value' => $criteriaDetail->criteria_value
                ];
            }
        }
        return $result;
    }

    function priceRateValue($from, $to)
    {
        $result = 0;
        $med = ($from + $to) / 2;
        if ($med < 30000) {
            $result = 6;
        } else if ($med <= 50000) {
            $result = 7;
        } else if ($med <= 100000) {
            $result = 9;
        } else {
            $result = 8;
        }
        return $result;
    }

    function recommendRateValue($value)
    {
        $result = 0;
        if ($value < 10) {
            $result = 5;
        } else if ($value <= 50) {
            $result = 6;
        } else if ($value <= 100) {
            $result = 7;
        } else if ($value <= 100) {
            $result = 8;
        } else {
            $result = 9;
        }
        return $result;
    }
}
