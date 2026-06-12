<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientStockException;
use Illuminate\Http\Request;
use Tests\TestCase;

class InsufficientStockExceptionTest extends TestCase
{
    public function test_it_renders_a_409_with_the_shortfall_details(): void
    {
        $exception = new InsufficientStockException([
            ['product_id' => 7, 'requested' => 5, 'available' => 2],
        ]);

        $response = $exception->render(Request::create('/api/orders', 'POST'));
        $payload = $response->getData(true);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame('One or more items are out of stock.', $payload['message']);
        $this->assertSame(7, $payload['errors']['items'][0]['product_id']);
        $this->assertSame(5, $payload['errors']['items'][0]['requested']);
        $this->assertSame(2, $payload['errors']['items'][0]['available']);
    }
}
