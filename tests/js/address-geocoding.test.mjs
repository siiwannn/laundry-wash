import test from 'node:test';
import assert from 'node:assert/strict';
import {
    buildAddressLookupUrl,
    buildPinLookupUrl,
    formatGeocodingAddress,
    getGeocodingCoordinates,
} from '../../public/js/address-geocoding.js';

test('forward lookup targets the same-origin proxy with address and map bias', () => {
    const url = new URL(buildAddressLookupUrl('/customer/addresses/geocode/search', 'Jl. Mawar 5, Jakarta', {
        latitude: -6.2,
        longitude: 106.8,
    }, 'http://127.0.0.1:8000/customer/addresses/edit'));

    assert.equal(url.origin, 'http://127.0.0.1:8000');
    assert.equal(url.pathname, '/customer/addresses/geocode/search');
    assert.equal(url.searchParams.get('address'), 'Jl. Mawar 5, Jakarta');
    assert.equal(url.searchParams.get('latitude'), '-6.2');
    assert.equal(url.searchParams.get('longitude'), '106.8');
});

test('reverse lookup targets the same-origin proxy with pin coordinates', () => {
    const url = new URL(buildPinLookupUrl(
        '/customer/addresses/geocode/reverse',
        -6.2,
        106.8,
        'http://127.0.0.1:8000/customer/addresses/edit',
    ));

    assert.equal(url.origin, 'http://127.0.0.1:8000');
    assert.equal(url.searchParams.get('latitude'), '-6.2');
    assert.equal(url.searchParams.get('longitude'), '106.8');
    assert.equal(url.searchParams.get('lang'), 'id');
});

test('geocoding coordinates are converted from longitude-latitude order', () => {
    assert.deepEqual(getGeocodingCoordinates({ geometry: { coordinates: [106.8, -6.2] } }), {
        latitude: -6.2,
        longitude: 106.8,
    });
    assert.equal(getGeocodingCoordinates({ geometry: { coordinates: [200, -6.2] } }), null);
});

test('reverse result address is readable and omits duplicate components', () => {
    assert.equal(formatGeocodingAddress({ properties: {
        housenumber: '45',
        street: 'Jl. Mawar',
        suburb: 'Cilandak',
        city: 'Jakarta Selatan',
        state: 'DKI Jakarta',
        country: 'Indonesia',
    } }), '45 Jl. Mawar, Cilandak, Jakarta Selatan, DKI Jakarta, Indonesia');
});
