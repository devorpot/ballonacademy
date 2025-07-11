<?php

// app/Http/Controllers/Admin/PaymentTypeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;

class PaymentTypeController extends Controller
{
    public function options()
    {
        $types = PaymentType::orderBy('name')->get();

        return response()->json(
            $types->map(fn($type) => [
                'value' => $type->id,
                'label' => $type->name,
            ])
        );
    }
}
