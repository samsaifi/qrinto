<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = ['admin', 'store_admin', 'staff', 'customer'];

foreach ($roles as $role) {
    $u = new \App\Models\User(['role' => $role]);
    \Illuminate\Support\Facades\Auth::setUser($u);
    
    $user = auth()->user();
    if ($user->role === 'admin') {
        $target = '/admin/dashboard';
    } elseif (in_array($user->role, ['store_admin', 'storeadmin'])) {
        $target = '/store/dashboard';
    } elseif ($user->role === 'staff') {
        $target = '/staff/dashboard';
    } else {
        $target = route('customer.dashboard');
    }
    
    echo "Role: {$role} => Redirect Target: {$target}\n";
}
