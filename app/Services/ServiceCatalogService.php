<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceCatalogService
{
    /** @return Collection<int, Service> */
    public function activeServices(): Collection
    {
        return Service::query()->where('is_active', true)->orderBy('price_per_unit')->orderBy('name')->get();
    }
}
