<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    $request = Request::create('/pc/checkout/cash', 'POST', [
        'product_id' => 1,
        'quantity' => 1,
        'pickup_name' => 'Test',
        'pickup_email' => 'test@test.com',
        'contact_number' => '123456',
    ]);
    // Simulate CSRF by disabling middleware or providing token
    // For testing controller logic, we can just call the method
    
    $request->headers->set('Accept', 'application/json');
    $response = $kernel->handle($request);
    
    echo "STATUS: " . $response->status() . "\n";
    $data = json_decode($response->getContent(), true);
    if (isset($data['message'])) {
        echo "MESSAGE: " . $data['message'] . "\n";
    }
    if (isset($data['errors'])) {
        echo "ERRORS: " . json_encode($data['errors']) . "\n";
    }
    if ($response->status() != 200 && !isset($data['message'])) {
        echo "BODY: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
