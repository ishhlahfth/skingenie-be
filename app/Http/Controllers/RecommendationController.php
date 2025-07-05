<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneralResponse;
use App\Http\Resources\SkincareDataResource;
use App\Models\Criteria;
use App\Models\RecommendSubmission;
use App\Models\Skincare;
use App\Models\SkincareCat;
use App\Models\SubmissionUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    public function submitRecommendation(Request $request)
    {
        try {
            $category_id = $request->input('category_id');
            $tipeInp = $request->input('tipe_id');
            $concernInp = $request->input('concern_id');
            $price_from = $request->input('price_from');
            $price_to = $request->input('price_to');
            $recommend = $request->input('recommend');

            $user_name = $request->input('user_name');
            $user_email = $request->input('user_email');
            $user_phone = $request->input('user_phone');

            $tipeDet = Criteria::where('id', $tipeInp)->first();
            $concernDet = Criteria::where('id', $concernInp)->first();


            $data = Skincare::bycat($category_id)->with('ref_criteria')
                ->get();

            $skincareRaw = SkincareDataResource::collection($data);

            $cleaned = [];

            foreach ($skincareRaw as $raw) {
                $r = json_decode(json_encode($raw));
                $type = $r->type;
                $concern = $r->concern;
                if (count($type) > 0 && count($concern) > 0) {
                    foreach ($type as $tp) {
                        foreach ($concern as $cn) {
                            $cleaned[] = [
                                'id' => $r->id,
                                'type' => $tp->value,
                                'concern' => $cn->value,
                                'price' => $r->price,
                                'recommend' => $r->recommend
                            ];
                        }
                    }
                }
            }

            $idList = [];
            $typeList = [];
            $concernList = [];
            $priceList = [];
            $recommendList = [];

            foreach ($cleaned as $cl) {
                $idList[] = $cl['id'];
                $typeList[] = $cl['type'];
                $concernList[] = $cl['concern'];
                $priceList[] = $cl['price'];
                $recommendList[] = $cl['recommend'];
            }

            $request = [
                'products' => [
                    'id' => $idList,
                    'type' => $typeList,
                    'concern' => $concernList,
                    'price' => $priceList,
                    'recommend' => $recommendList
                ],
                'user-input' => [
                    'type' => $tipeDet['criteria_value'],
                    'concern' => $concernDet['criteria_value'],
                    'price' => $this->priceRateValue($price_from, $price_to),
                    'recommend' => $this->recommendRateValue($recommend),
                ],
                'weights' => [
                    'type' => [
                        5,
                        6,
                        7,
                        8,
                        9
                    ],
                    'concern' => [
                        6,
                        7,
                        8,
                        9,
                        10
                    ],
                    'price' => [
                        6,
                        7,
                        8,
                        9
                    ],
                    'recommend' => [
                        5,
                        6,
                        7,
                        8,
                        9
                    ],
                ]
            ];
            $urlFlask = env('FLASK_API_BASE_URL');
            $headers = [
                'Accept' => 'application/json, text/plain, */*',
                'Authorization' => 'Bearer eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7InVzZXIiOnsiX2lkIjoiNjQ2MzM0NzkxZTJmZWFmOTNkYjY2Y2QyIiwiaWQiOjUxNDU3ODAsImVtYWlsIjoiaXNobGFoZmF0aEBnbWFpbC5jb20iLCJyb2xlIjoiY29tbXVuaXR5IiwibG9jYWxlIjoiaWQiLCJpc19vdHBfYWRtaW4iOmZhbHNlLCJzb2Npb2xsYSI6eyJpZCI6IjUxNDU3ODAiLCJ0b2tlbiI6ImV5SmhiR2NpT2lKSVV6VXhNaUlzSW5SNWNDSTZJa3BYVkNKOS5leUprWVhSaElqcDdJbWxrSWpvaU5URTBOVGM0TUNJc0luVnpaWElpT25zaWFXUWlPaUkxTVRRMU56Z3dJbjE5TENKcFlYUWlPakUzTkRrNU5qSTFORFlzSW01aVppSTZNVGMwT1RrMk1qVTFOaXdpWlhod0lqb3hOemd4TkRrNE5UUTJMQ0pwYzNNaU9pSnpjMjh1YzI5amJ5NXBaQ0lzSW1wMGFTSTZJakF5Tm1Kak1tSTRNRGRoTmpVNVlqbGhaall5WkRJNE9EazNNalF6TXpNM0luMC5YcVVYa05PS1hwZFZ1Z2xibEc3WDJIRWItMG9DVTFsUjlSRUM0Q0xSY2NLSEtLUmhKdHJHelJUTjVMUDZQQ1U4NER2WFU3c2JZMEJUTEV5ekU2eHhBQSJ9fX0sImlhdCI6MTc0OTk2MjU0NiwibmJmIjoxNzQ5OTYyNTU2LCJleHAiOjE3ODE0OTg1NDYsImlzcyI6InNzby5zb2NvLmlkIiwianRpIjoiMDI2YmMyYjgwN2E2NTliOWFmNjJkMjg4OTcyNDMzMzcifQ.yM-EcRm_ht5eKyH3MNwx-oNj53ZL8EPy-rGN5i6Y90XU-00d7_iLBt-1ZgsZAv8SqZqjAE0wVxe-8ixOQnuYBw',
            ];
            $response = Http::withHeaders($headers)->withOptions(['verify' => false])->post($urlFlask . 'submission/process', $request);
            if (!$response->successful()) {
                return new GeneralResponse(false, 'Recommendation Submission ', $response->json());
            }

            $response = $response->json();

            $productDetail = Skincare::where('id', $response['data']['recommended_id'])->first();

            $others = [];

            foreach ($response['data']['other'] as $each) {
                $eachDetail = Skincare::where('id', $each)->first();
                $others[] = $eachDetail;
            }

            $postUser = SubmissionUser::create([
                'full_name' => $user_name,
                'email' => $user_email,
                'phone' => $user_phone,
            ]);
            if ($postUser) {
                $postSubmission = RecommendSubmission::create([
                    'category_uuid' => $category_id,
                    'price_start' => $price_from,
                    'price_to' => $price_to,
                    'user_uuid' => $postUser->uuid,
                    'rec_skincare_uuid' => $productDetail->id,
                ]);
                if (!$postSubmission) {
                    return new GeneralResponse(false, 'Failed to save record', []);
                }
            } else {
                return new GeneralResponse(false, 'Failed to save user', []);
            }

            $response = [
                'recommended' => $productDetail,
                'others' => $others,
            ];

            return new GeneralResponse(true, 'Recommendation Submission ', $response);
        } catch (\Throwable $th) {
            return new GeneralResponse(false, 'Recommendation Submission ' . $th->getMessage(), []);
        }
    }

    public function testApi(Request $request)
    {
        $category_id = $request->input('category_id');
        $data = Skincare::bycat($category_id)->with('ref_criteria')
            ->get();

        $response = [
            'count' => count($data),
            'data' => $data,
        ];
        return new GeneralResponse(true, 'Recommendation Submission ', $response);
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
        } else if ($value <= 150) {
            $result = 8;
        } else {
            $result = 9;
        }
        return $result;
    }
}
