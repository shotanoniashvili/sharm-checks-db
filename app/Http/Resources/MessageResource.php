<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class MessageResource implements Responsable
{
    public $message;
    public $success;
    public $data;
    public $total;

    protected $responseCode;

    public function __construct(string $message, bool $success = true, $data = [], $total = 0)
    {
        $this->message = $message;
        $this->success = $success;
        $this->data    = $data;
        $this->total   = $total;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        $data = [
            'success' => $this->success,
            'message' => $this->message,
            'data'    => $this->data
        ];

        if($this->total != 0) $data['total'] = $this->total;

        return response()->json($data, $this->getResponseCode());
    }


    /**
     * @return int
     */
    private function getResponseCode() {
        if($this->success) return Response::HTTP_OK;

        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }
}
