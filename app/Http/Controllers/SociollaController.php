<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneralResponse;
use Illuminate\Support\Facades\Http;

class SociollaController extends Controller
{
    public function fetchData()
    {
        $url4 = env('SOCIOLLA_API_BASE_URL_CAT_4');
        $url1 = env('SOCIOLLA_API_BASE_URL_CAT_4');
        $token = env('SOCIOLLA_API_TOKEN');
        $headers = [
            'Accept' => 'application/json, text/plain, */*',
            'Authorization' => 'Bearer eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJkYXRhIjp7InVzZXIiOnsiX2lkIjoiNjQ2MzM0NzkxZTJmZWFmOTNkYjY2Y2QyIiwiaWQiOjUxNDU3ODAsImVtYWlsIjoiaXNobGFoZmF0aEBnbWFpbC5jb20iLCJyb2xlIjoiY29tbXVuaXR5IiwibG9jYWxlIjoiaWQiLCJpc19vdHBfYWRtaW4iOmZhbHNlLCJzb2Npb2xsYSI6eyJpZCI6IjUxNDU3ODAiLCJ0b2tlbiI6ImV5SmhiR2NpT2lKSVV6VXhNaUlzSW5SNWNDSTZJa3BYVkNKOS5leUprWVhSaElqcDdJbWxrSWpvaU5URTBOVGM0TUNJc0luVnpaWElpT25zaWFXUWlPaUkxTVRRMU56Z3dJbjE5TENKcFlYUWlPakUzTkRrNU5qSTFORFlzSW01aVppSTZNVGMwT1RrMk1qVTFOaXdpWlhod0lqb3hOemd4TkRrNE5UUTJMQ0pwYzNNaU9pSnpjMjh1YzI5amJ5NXBaQ0lzSW1wMGFTSTZJakF5Tm1Kak1tSTRNRGRoTmpVNVlqbGhaall5WkRJNE9EazNNalF6TXpNM0luMC5YcVVYa05PS1hwZFZ1Z2xibEc3WDJIRWItMG9DVTFsUjlSRUM0Q0xSY2NLSEtLUmhKdHJHelJUTjVMUDZQQ1U4NER2WFU3c2JZMEJUTEV5ekU2eHhBQSJ9fX0sImlhdCI6MTc0OTk2MjU0NiwibmJmIjoxNzQ5OTYyNTU2LCJleHAiOjE3ODE0OTg1NDYsImlzcyI6InNzby5zb2NvLmlkIiwianRpIjoiMDI2YmMyYjgwN2E2NTliOWFmNjJkMjg4OTcyNDMzMzcifQ.yM-EcRm_ht5eKyH3MNwx-oNj53ZL8EPy-rGN5i6Y90XU-00d7_iLBt-1ZgsZAv8SqZqjAE0wVxe-8ixOQnuYBw',
        ];

        $endpoint = 'search/count?';
        $queryParams = [
            'limit' => 10,
            'skip' => 0,
            'sort' => '+total_views',
            'filter' => '{"categories.id":"5d3d50276b24d01599516819","classification":"sellable_products","brand.country_tag_id":{"$in":["17759"]}}',
        ];
        $response = Http::withHeaders($headers)->withOptions(['verify' => false])->get($url4 . $endpoint . http_build_query($queryParams));
        $jsonResponse = $response->json();
        $totalData = $jsonResponse['data'];
        $perPage = 10;
        $repetition = ceil($totalData / $perPage);
        $skincareResult = [];
        $repetition = 1;

        for ($i = 1; $i <= $repetition; $i++) {
            $endpoint = 'search?';
            $queryParams = [
                'limit' => 10,
                'skip' => 20,
                'sort' => '+total_reviews',
                'filter' => '{"categories.id":"5d3d50276b24d01599516819","classification":"sellable_products","brand.country_tag_id":{"$in":["17759"]}}',
            ];
            $responseList = Http::withHeaders($headers)->withOptions(['verify' => false])->get($url4 . $endpoint . http_build_query($queryParams));
            if ($responseList->successful()) {
                $jsonResp = $responseList->json();
                $scList = $jsonResp['data'];
                foreach ($scList as $dt) {
                    $endpoint = 'v3/products/' . $dt['slug'];
                    $responseDetail = Http::withHeaders($headers)->withOptions(['verify' => false])->get($url1 . $endpoint);
                    if ($responseDetail->successful()) {
                        $jsRespDetail = $responseDetail->json();
                        $scDetail = $jsRespDetail['data'];
                        $categories = [];
                        foreach($scDetail['categories'] as $cat) {
                            $eachCat = [
                                'id' => $cat['id'],
                                'name' => $cat['name'],
                                'slug' => $cat['slug'],
                            ];
                            $categories[] = $eachCat;
                        }

                        $tags = [];
                        foreach($scDetail['tags'] as $tag) {
                            if(in_array($tag['level_name'],['Concern','Skin/Hair Type'])) {
                                $eachTag = [
                                    'id' => $tag['id'],
                                    'type' => $tag['level_name'],
                                    'name' => $tag['name'],
                                ];
                                $tags[] = $eachTag;
                            }
                        }
                        $each = [
                            'id' => $scDetail['id'],
                            'name' => $scDetail['name'],
                            'image' => $scDetail['images'][0]['url'],
                            'min_price' => $scDetail['min_price'],
                            'max_price' => $scDetail['max_price'],
                            'ratings' => $scDetail['review_stats']['average_rating'],
                            'total_reviews' => $scDetail['review_stats']['total_reviews'],
                            'total_recommend' => $scDetail['review_stats']['total_recommended_count'],
                            'sociolla_url' => $scDetail['url_sociolla'],
                            'description' => $scDetail['description'],
                            'how_to_use' => $scDetail['how_to_use'],
                            'ingredients' => $scDetail['ingredients'],
                            'slug' => $scDetail['slug'],
                            'categories' => $categories,
                            'tags' => $tags,
                        ];
                        $skincareResult[] = $each;
                    }
                }
            }
        }


        return new GeneralResponse(true, "Success", $skincareResult);

        // return response()->json([
        //     'error' => 'Failed to fetch data',
        //     'status' => $response->status(),
        // ], $response->status());
    }

    public function test(){
        return 'asdasd';
    }
}
