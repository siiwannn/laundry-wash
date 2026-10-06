<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ServiceCatalogService;
use Illuminate\View\View;

class CustomerCatalogController extends Controller
{
    public function index(ServiceCatalogService $catalog): View
    {
        return view('customer.catalog', ['services' => $catalog->activeServices()]);
    }
}
