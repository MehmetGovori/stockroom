<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(public readonly array $shortfalls)
    {
        parent::__construct('One or more items are out of stock.');
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'items' => $this->shortfalls,
            ],
        ], 409);
    }
}
