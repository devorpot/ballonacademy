<?php

// app/Http/Controllers/Admin/PaymentStatusController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentStatus;

class PaymentStatusController extends Controller
{
    public function options()
    {
        return response()->json(
            PaymentStatus::orderBy('name')->get()->map(fn($status) => [
                'value' => $status->id,
                'label' => $status->name,
            ])
        );
    }
}
