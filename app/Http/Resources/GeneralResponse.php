<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;

class GeneralResponse extends JsonResource
{
    public $status;
    public $message;
    public $resource;
    protected $resourceType;

    /**
     * __construct
     *
     * @param  mixed $status
     * @param  mixed $message
     * @param  mixed $resource
     * @param  mixed $resourceType
     * @return void
     */
    public function __construct($status, $message, $resource, $resourceType = null)
    {
        parent::__construct($resource);
        $this->status = $status;
        $this->message = $message;
        $this->resourceType = $resourceType;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'success' => $this->status,
            'message' => $this->message,
        ];

        if ($this->resource instanceof AbstractPaginator) {
            $resourceCollection = $this->resourceType
                ? call_user_func([$this->resourceType, 'collection'], $this->resource->items())
                : $this->resource->items();

            $data['data'] = $resourceCollection;
            $data['meta'] = [
                'current_page' => $this->resource->currentPage(),
                'from' => $this->resource->firstItem(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'to' => $this->resource->lastItem(),
                'total' => $this->resource->total(),
            ];
        } else {
            $data['data'] = $this->resourceType
                ? new $this->resourceType($this->resource)
                : $this->resource;
        }

        return $data;
    }
}
