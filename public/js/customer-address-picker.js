(() => {
function buildAddressLookupUrl(endpoint, address, { latitude, longitude } = {}) {
    const url = new URL(endpoint, window.location.href);
    url.searchParams.set('address', address.trim());
    if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
        url.searchParams.set('latitude', String(latitude));
        url.searchParams.set('longitude', String(longitude));
    }
    return url.toString();
}

function buildPinLookupUrl(endpoint, latitude, longitude) {
    const url = new URL(endpoint, window.location.href);
    url.searchParams.set('latitude', String(latitude));
    url.searchParams.set('longitude', String(longitude));
    url.searchParams.set('lang', 'id');
    return url.toString();
}

function getGeocodingCoordinates(feature) {
    const coordinates = feature?.geometry?.coordinates;
    if (!Array.isArray(coordinates) || coordinates.length < 2) return null;
    const [longitude, latitude] = coordinates.map(Number);
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) return null;
    if (latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) return null;
    return { latitude, longitude };
}

function formatGeocodingAddress(feature) {
    const properties = feature?.properties ?? {};
    const street = [properties.housenumber, properties.street].filter(Boolean).join(' ');
    const parts = [street || properties.name, properties.suburb, properties.district,
        properties.city || properties.county, properties.state, properties.postcode, properties.country];
    return [...new Set(parts.map(part => String(part ?? '').trim()).filter(Boolean))].join(', ');
}

function initializeAddressPicker() {
    const mapElement = document.getElementById('addressPickerMap');
    if (!mapElement) return;

    const addressInput = document.getElementById('address');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const locationConfirmedInput = document.getElementById('location_confirmed');
    const addressForm = document.getElementById('addressEditForm');
    const pinWarning = document.getElementById('addressPinWarning');
    const lookupStatus = document.getElementById('addressLookupStatus');
    const initialAddress = mapElement.dataset.initialAddress.trim();
    const latitude = Number.parseFloat(latitudeInput.value);
    const longitude = Number.parseFloat(longitudeInput.value);
    const mapLat = Number.isFinite(latitude) ? latitude : -6.2088;
    const mapLng = Number.isFinite(longitude) ? longitude : 106.8456;

    let confirmedAddress = initialAddress;
    let lookupRequestId = 0;
    let activeController = null;
    let pendingLookup = null;
    let submitAfterLookup = false;
    let resubmittingAfterLookup = false;
    const reverseLookupCache = new Map();

    if (!window.L) {
        lookupStatus.className = 'alert alert-danger py-2 small';
        lookupStatus.textContent = 'Peta gagal dimuat. Muat ulang halaman atau periksa koneksi internet.';
        return;
    }

    let map;
    try {
        map = L.map('addressPickerMap').setView([mapLat, mapLng], 14);
    } catch (error) {
        lookupStatus.className = 'alert alert-danger py-2 small';
        lookupStatus.textContent = 'Peta gagal diinisialisasi. Muat ulang halaman.';
        console.error('Address picker map initialization failed:', error);
        return;
    }
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const marker = L.marker([mapLat, mapLng], { draggable: true }).addTo(map);

    function setStatus(type, message) {
        lookupStatus.className = `alert alert-${type} py-2 small`;
        lookupStatus.textContent = message;
    }

    function cancelLookup() {
        lookupRequestId++;
        window.clearTimeout(pendingLookup);
        pendingLookup = null;
        activeController?.abort();
        activeController = null;
    }

    async function requestFeature(url, requestId) {
        activeController = new AbortController();
        const controller = activeController;
        const timeoutId = window.setTimeout(() => controller.abort(), 5000);

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            const result = await response.json();
            if (!response.ok) {
                const validationMessage = Object.values(result.errors ?? {})[0]?.[0];
                throw new Error(validationMessage || result.message || 'Layanan pencarian alamat tidak merespons.');
            }
            if (requestId !== lookupRequestId) return null;

            const feature = result.features?.[0];
            if (!feature) throw new Error('Alamat tidak ditemukan. Tambahkan nama jalan atau kelurahan.');
            return feature;
        } finally {
            window.clearTimeout(timeoutId);
            if (activeController === controller) activeController = null;
        }
    }

    async function lookupAddress() {
        window.clearTimeout(pendingLookup);
        pendingLookup = null;
        const query = addressInput.value.trim();
        if (query.length < 5) {
            setStatus('warning', 'Masukkan alamat yang lebih lengkap agar titik peta dapat dicari.');
            return;
        }

        cancelLookup();
        const requestId = lookupRequestId;
        locationConfirmedInput.value = '0';
        pinWarning.classList.remove('d-none');
        setStatus('info', 'Mencari titik dari alamat…');

        try {
            const feature = await requestFeature(buildAddressLookupUrl(mapElement.dataset.forwardUrl, query, {
                latitude: map.getCenter().lat,
                longitude: map.getCenter().lng,
            }), requestId);
            if (!feature || requestId !== lookupRequestId) return;

            const point = getGeocodingCoordinates(feature);
            if (!point) throw new Error('Titik dari layanan peta tidak valid. Coba periksa alamat.');

            marker.setLatLng([point.latitude, point.longitude]);
            map.setView([point.latitude, point.longitude], Math.max(map.getZoom(), 16));
            latitudeInput.value = point.latitude.toFixed(7);
            longitudeInput.value = point.longitude.toFixed(7);
            confirmedAddress = query;
            locationConfirmedInput.value = '1';
            pinWarning.classList.add('d-none');
            setStatus('success', 'Pin mengikuti hasil pencarian alamat. Periksa posisi pin sebelum menyimpan.');
            continueSubmitAfterLookup();
        } catch (error) {
            if (requestId !== lookupRequestId) return;
            submitAfterLookup = false;
            locationConfirmedInput.value = '0';
            setStatus('warning', error.name === 'AbortError'
                ? 'Pencarian terlalu lama. Periksa koneksi, lalu coba lagi.'
                : (error.message || 'Titik alamat belum ditemukan.'));
        }
    }

    async function lookupPin(pointLatitude, pointLongitude) {
        cancelLookup();
        const requestId = lookupRequestId;
        const cacheKey = `${pointLatitude.toFixed(5)},${pointLongitude.toFixed(5)}`;
        latitudeInput.value = pointLatitude.toFixed(7);
        longitudeInput.value = pointLongitude.toFixed(7);
        locationConfirmedInput.value = '0';
        confirmedAddress = null;
        pinWarning.classList.remove('d-none');
        setStatus('info', 'Mencari alamat dari posisi pin…');

        try {
            let placeName = reverseLookupCache.get(cacheKey);
            if (!placeName) {
                const feature = await requestFeature(
                    buildPinLookupUrl(mapElement.dataset.reverseUrl, pointLatitude, pointLongitude),
                    requestId,
                );
                if (!feature || requestId !== lookupRequestId) return;

                placeName = formatGeocodingAddress(feature);
                if (!placeName) throw new Error('Alamat tidak ditemukan di posisi pin ini.');
                reverseLookupCache.set(cacheKey, placeName);
            }

            if (requestId !== lookupRequestId) return;
            addressInput.value = placeName;
            confirmedAddress = placeName;
            locationConfirmedInput.value = '1';
            pinWarning.classList.add('d-none');
            setStatus('success', 'Alamat sudah disesuaikan dengan posisi pin.');
            continueSubmitAfterLookup();
        } catch (error) {
            if (requestId !== lookupRequestId) return;
            submitAfterLookup = false;
            locationConfirmedInput.value = '0';
            setStatus('warning', error.name === 'AbortError'
                ? 'Pencarian terlalu lama. Coba geser pin lagi.'
                : (error.message || 'Alamat tidak berhasil dicari.'));
        }
    }

    function handleAddressInput() {
        cancelLookup();
        const query = addressInput.value.trim();
        if (query === confirmedAddress) {
            locationConfirmedInput.value = '1';
            pinWarning.classList.add('d-none');
            lookupStatus.classList.add('d-none');
            return;
        }

        locationConfirmedInput.value = '0';
        pinWarning.classList.remove('d-none');
        if (query.length < 5) {
            setStatus('info', 'Masukkan alamat lebih lengkap untuk memindahkan pin.');
            return;
        }

        setStatus('info', 'Pin akan menyesuaikan otomatis setelah Anda berhenti mengetik sebentar.');
        pendingLookup = window.setTimeout(lookupAddress, 700);
    }

    addressInput.addEventListener('input', () => handleAddressInput());

    function continueSubmitAfterLookup() {
        if (!submitAfterLookup) return;
        submitAfterLookup = false;
        resubmittingAfterLookup = true;
        addressForm.requestSubmit();
    }

    addressForm.addEventListener('submit', event => {
        if (resubmittingAfterLookup) {
            resubmittingAfterLookup = false;
            return;
        }

        if (addressInput.value.trim() !== confirmedAddress) {
            event.preventDefault();
            submitAfterLookup = true;
            if (activeController) {
                window.clearTimeout(pendingLookup);
                pendingLookup = null;
            } else {
                lookupAddress();
            }
        }
    });

    marker.on('dragend', () => {
        const point = marker.getLatLng();
        lookupPin(point.lat, point.lng);
    });

    map.on('click', event => {
        marker.setLatLng(event.latlng);
        lookupPin(event.latlng.lat, event.latlng.lng);
    });

    window.getCurrentLocation = () => {
        if (!('geolocation' in navigator)) {
            setStatus('warning', 'Browser ini tidak menyediakan deteksi lokasi.');
            return;
        }

        navigator.geolocation.getCurrentPosition(position => {
            const pointLatitude = position.coords.latitude;
            const pointLongitude = position.coords.longitude;
            marker.setLatLng([pointLatitude, pointLongitude]);
            map.setView([pointLatitude, pointLongitude], 16);
            lookupPin(pointLatitude, pointLongitude);
        }, error => {
            setStatus('warning', `Gagal mendeteksi lokasi: ${error.message}`);
        }, { enableHighAccuracy: true, timeout: 10000 });
    };

    if (addressInput.value.trim() !== initialAddress) {
        lookupAddress();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAddressPicker, { once: true });
} else {
    initializeAddressPicker();
}
})();
