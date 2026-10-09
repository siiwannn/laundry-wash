<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ServiceCatalogService;
use Illuminate\Http\Response;

class CustomerCatalogController extends Controller
{
    public function index(ServiceCatalogService $catalog): Response
    {
        return response()
            ->view('customer.catalog', ['services' => $catalog->activeServices()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
