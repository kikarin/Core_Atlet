<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => 'success',
            'message' => $this->message ?? 'Success',
            'data' => $this->data ?? $this->resource,
            'meta' => $this->when($this->meta, $this->meta),
            'errors' => $this->when($this->errors, $this->errors),
        ];
    }

    /**
     * Create a success response
     */
    public static function success($data = null, $message = 'Success', $meta = null)
    {
        return new static([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    /**
     * Create an error response
     */
    public static function error($message = 'Error', $errors = null, $status = 400)
    {
        return new static([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ]);
    }
}
