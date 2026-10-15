<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ForwardGeocodeAddressRequest;
use App\Http\Requests\Customer\ReverseGeocodeAddressRequest;
use App\Services\Geocoding\AddressGeocodingService;
use Illuminate\Http\JsonResponse;

class CustomerAddressGeocodingController extends Controller
{
    public function search(
        ForwardGeocodeAddressRequest $request,
        AddressGeocodingService $geocoding,
    ): JsonResponse {
        $data = $request->validated();
        $result = $geocoding->searchAddress(
            $data['address'],
            isset($data['latitude']) ? (float) $data['latitude'] : null,
            isset($data['longitude']) ? (float) $data['longitude'] : null,
        );

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Layanan pencarian alamat sedang tidak tersedia.'], 503);
    }

    public function reverse(
        ReverseGeocodeAddressRequest $request,
        AddressGeocodingService $geocoding,
    ): JsonResponse {
        $data = $request->validated();
        $result = $geocoding->reverseCoordinates((float) $data['latitude'], (float) $data['longitude']);

        return $result
            ? response()->json($result)
            : response()->json(['message' => 'Layanan pencarian alamat sedang tidak tersedia.'], 503);
    }
}
