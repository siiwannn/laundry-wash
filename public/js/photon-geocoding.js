export function buildAddressLookupUrl(endpoint, address, { latitude, longitude } = {}, baseUrl = 'http://localhost') {
    const url = new URL(endpoint, baseUrl);
    url.searchParams.set('address', address.trim());

    if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
        url.searchParams.set('latitude', String(latitude));
        url.searchParams.set('longitude', String(longitude));
    }

    return url.toString();
}

export function buildPinLookupUrl(endpoint, latitude, longitude, baseUrl = 'http://localhost') {
    const url = new URL(endpoint, baseUrl);
    url.searchParams.set('latitude', String(latitude));
    url.searchParams.set('longitude', String(longitude));
    url.searchParams.set('lang', 'id');
    return url.toString();
}

export function getPhotonCoordinates(feature) {
    const coordinates = feature?.geometry?.coordinates;
    if (!Array.isArray(coordinates) || coordinates.length < 2) return null;
    if (coordinates[0] === null || coordinates[1] === null || coordinates[0] === '' || coordinates[1] === '') return null;

    const [longitude, latitude] = coordinates.map(Number);
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return null;
    if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return null;

    return { latitude, longitude };
}

export function formatPhotonAddress(feature) {
    const properties = feature?.properties ?? {};
    const street = [properties.housenumber, properties.street].filter(Boolean).join(' ');
    const parts = [
        street || properties.name,
        properties.suburb,
        properties.district,
        properties.city || properties.county,
        properties.state,
        properties.postcode,
        properties.country,
    ];

    return [...new Set(parts.map(part => String(part ?? '').trim()).filter(Boolean))].join(', ');
}
