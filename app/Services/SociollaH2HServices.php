<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\Criteria;
use App\Models\Skincare;
use App\Models\SkincareCat;
use App\Models\SkincareCriteria;
use Illuminate\Support\Facades\Http;

class SociollaH2HServices
{

    protected $sociollaUrl1;
    protected $sociollaUrl4;
    protected $token;

    public function __construct()
    {
        $this->sociollaUrl1 = env('SOCIOLLA_API_BASE_URL_CAT_1');
        $this->sociollaUrl4 = env('SOCIOLLA_API_BASE_URL_CAT_4');
        $this->token = env('SOCIOLLA_API_TOKEN');
    }

    public function getTotalData()
    {
        $headers = [
            'Accept' => 'application/json, text/plain, */*',
            'Authorization' => 'Bearer ' . $this->token,
        ];
        $endpoint = 'search/count?';
        $queryParams = [
            'filter' => '{"categories.id":"5d3d50276b24d01599516819","classification":"sellable_products","brand.country_tag_id":{"$in":["17759"]}}',
        ];
        $response = Http::withHeaders($headers)->withOptions(['verify' => false])->get($this->sociollaUrl4 . $endpoint . http_build_query($queryParams))->json();
        $totalData = $response['data'];
        return $totalData;
    }

    public function getSkincareDetail($perPage, $totalData)
    {
        $headers = [
            'Accept' => 'application/json, text/plain, */*',
            'Authorization' => 'Bearer ' . $this->token,
        ];

        $repetition = ceil($totalData / $perPage);
        $totScInsert = 0;
        $totCatInsert = 0;
        $totTagInsert = 0;
        // $skinCareResult = [];

        for ($i = 0; $i < $repetition; $i++) {
            $endpoint = 'search?';
            $queryParams = [
                'limit' => $perPage,
                'skip' => $i * $perPage,
                'sort' => '+total_reviews',
                'filter' => '{"categories.id":"5d3d50276b24d01599516819","classification":"sellable_products","brand.country_tag_id":{"$in":["17759"]}}',
            ];
            $responseList = Http::withHeaders($headers)->withOptions(['verify' => false])->get($this->sociollaUrl4 . $endpoint . http_build_query($queryParams));
            if ($responseList->successful()) {
                $jsonResp = $responseList->json();
                $scList = $jsonResp['data'];
                foreach ($scList as $dt) {
                    $endpointDetail = 'v3/products/' . $dt['slug'];
                    $responseDetail = Http::withHeaders($headers)->withOptions(['verify' => false])->get($this->sociollaUrl1 . $endpointDetail);
                    if ($responseDetail->successful()) {
                        $jsRespDetail = $responseDetail->json();
                        $scDetail = $jsRespDetail['data'];

                        $categories = [];
                        foreach ($scDetail['categories'] as $cat) {
                            $each = [
                                'id' => $cat['id'],
                                'category_name' => $cat['name'],
                            ];
                            $categories[] = $each;
                            $isExist = Categories::where('id', $cat['id'])->first();
                            if (!$isExist) {
                                $catInsert = Categories::create([
                                    'id' => $cat['id'],
                                    'category_name' => $cat['name'],
                                ]);
                                if ($catInsert) {
                                    $totCatInsert += 1;
                                }
                            }
                        }

                        $tags = [];
                        foreach ($scDetail['tags'] as $tag) {
                            if (array_key_exists('level_name', $tag)) {
                                $levelName = $tag['level_name'] ? $tag['level_name'] : 'uncat';
                                if (in_array($levelName, ['Concern', 'Skin/Hair Type'])) {
                                    $each = [
                                        'id' => $tag['id'],
                                        'criteria_name' => $tag['name'],
                                        'type' => $levelName,
                                        'criteria_key' => $tag['name'],
                                    ];
                                    $tags[] = $each;
                                    $isExist = Criteria::where('id', $tag['id'])->first();
                                    if (!$isExist) {
                                        $tagInsert = Criteria::create([
                                            'id' => $tag['id'],
                                            'criteria_name' => $tag['name'],
                                            'type' => $levelName,
                                            'criteria_key' => $tag['name'],
                                        ]);
                                        if ($tagInsert) {
                                            $totTagInsert += 1;
                                        }
                                    }
                                }
                            }
                        }
                        $each = [
                            'id' => $scDetail['id'],
                            'skincare_name' => $scDetail['name'],
                            'img_url' => $scDetail['images'][0]['url'],
                            'min_price' => $scDetail['min_price'],
                            'max_price' => $scDetail['max_price'],
                            'rating' => $scDetail['review_stats']['average_rating'],
                            'total_reviews' => $scDetail['review_stats']['total_reviews'],
                            'total_recommend' => $scDetail['review_stats']['total_recommended_count'],
                            'sociolla_url' => $scDetail['url_sociolla'],
                            'description' => $scDetail['description'],
                            'how_to_use' => $scDetail['how_to_use'],
                            'ingredients' => $scDetail['ingredients'],
                            'slug' => $scDetail['slug'],
                        ];
                        $isExist = Skincare::where('id', $each['id'])->first();
                        if (!$isExist) {
                            $scInsert = Skincare::create($each);
                            if ($scInsert) {
                                $totScInsert += 1;
                            }
                        }

                        foreach ($categories as $consCat) {
                            $each = [
                                'skincare_id' => $scDetail['id'],
                                'category_id' => $consCat['id'],
                            ];
                            $consInsert = SkincareCat::create($each);
                        }

                        foreach ($tags as $consTag) {
                            $each = [
                                'skincare_id' => $scDetail['id'],
                                'criteria_id' => $consTag['id'],
                            ];
                            $consInsert = SkincareCriteria::create($each);
                        }
                    }
                }
            }
        }
        $result = [
            'skincare_added' => $totScInsert,
            'categories_added' => $totCatInsert,
            'criteria_added' => $totTagInsert,
        ];
        return $result;
    }
}
