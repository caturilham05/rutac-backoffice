<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeFeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Models\ConfigFee;

class ShopeeFeeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Backoffice/Configuration/ShopeeFee', [
            'setting' => ConfigFee::first()
        ]);
    }

    public function store(ShopeeFeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        ConfigFee::shopeeFeeAction($data);
        return Redirect::back();
    }
}
