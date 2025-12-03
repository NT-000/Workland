<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function exchange(Job $job): JsonResponse
    {
        $toCurrency = request('currencyTo');
        $salary = $job->salary;
        $response = Http::get("https://api.frankfurter.dev/latest?amount={$salary}&from=NOK&to={$toCurrency}");
        return response()->json($response->json());
    }
}
