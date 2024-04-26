<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CadastraOportunidadeController extends Controller
{
    function Index(Request $request) {
        // Retorna os dados em formato JSON
        Log::info('Request received: {mess}', ['mess' => $request]);
        echo $request;
    }
}
