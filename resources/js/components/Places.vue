<script setup>
import { Fieldtype } from '@statamic/cms';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, isReadOnly } = Fieldtype.use(emit, props);
defineExpose(expose);

const mapEl = ref(null);
const autocompleteEl = ref(null);

let mapInstance = null;
let marker = null;
let infoWindow = null;
let placeAutocomplete = null;
let closeClickListener = null;

onMounted(() => {
    init();
});

onBeforeUnmount(() => {
    destroy();
});

watch(isReadOnly, (readOnly) => {
    setInteractive(!readOnly);
});

async function init() {
    await google.maps.importLibrary('marker');
    await google.maps.importLibrary('places');

    initMap();
    initPlaceAutocomplete();
    setInteractive(!isReadOnly.value);

    if (props.value) {
        const place = new google.maps.places.Place({ id: props.value });
        await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location', 'viewport'] });

        updateMapInfo(place);
    }
}

function initMap() {
    mapInstance = new google.maps.Map(mapEl.value, {
        mapId: props.meta.map_id,
        center: props.meta.center,
        zoom: props.meta.zoom,
        mapTypeControl: false,
        fullscreenControl: false,
        streetViewControl: false,
        cameraControl: false,
        zoomControl: true,
    });
}

function initPlaceAutocomplete() {
    placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
        includedRegionCodes: props.meta.countries,
        locationBias: props.meta.center,
    });

    autocompleteEl.value.appendChild(placeAutocomplete);

    placeAutocomplete.addEventListener('gmp-select', onPlaceSelect);
}

async function onPlaceSelect({ placePrediction }) {
    if (isReadOnly.value) {
        return;
    }

    const place = placePrediction.toPlace();
    await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location', 'viewport'] });

    updateMapInfo(place);

    update(place.id);
}

function updateMapInfo(place) {
    createMarker(place.location);
    createInfoWindow(place.displayName, place.formattedAddress, place.location);

    placeAutocomplete.locationBias = place.location;

    if (place.viewport) {
        mapInstance.fitBounds(place.viewport);
    } else {
        mapInstance.setCenter(place.location);
        mapInstance.setZoom(17);
    }
}

function createMarker(location) {
    if (marker) {
        marker.map = null;
    }

    marker = new google.maps.marker.AdvancedMarkerElement({
        map: mapInstance,
    });

    marker.position = location;
}

function createInfoWindow(displayName, formattedAddress, location) {
    clearInfoWindow();

    infoWindow = new google.maps.InfoWindow();

    infoWindow.setHeaderContent(displayName);
    infoWindow.setContent(formattedAddress);
    infoWindow.setPosition(location);

    infoWindow.open({
        map: mapInstance,
        anchor: marker,
        shouldFocus: false,
    });

    closeClickListener = infoWindow.addListener('closeclick', () => destroyInfoWindow());
}

function destroyInfoWindow() {
    if (isReadOnly.value) {
        return;
    }

    if (marker) {
        marker.map = null;
        marker = null;
    }

    clearInfoWindow();
    update(null);
}

function clearInfoWindow() {
    if (closeClickListener) {
        google.maps.event.removeListener(closeClickListener);
        closeClickListener = null;
    }

    if (infoWindow) {
        infoWindow.close();
        infoWindow = null;
    }
}

function setInteractive(interactive) {
    if (placeAutocomplete) {
        placeAutocomplete.disabled = !interactive;
    }

    if (mapInstance) {
        mapInstance.setOptions({
            gestureHandling: interactive ? 'auto' : 'none',
            zoomControl: interactive,
            keyboardShortcuts: interactive,
        });
    }
}

function destroy() {
    clearInfoWindow();

    if (placeAutocomplete) {
        placeAutocomplete.removeEventListener('gmp-select', onPlaceSelect);
        placeAutocomplete.remove();
        placeAutocomplete = null;
    }

    if (marker) {
        marker.map = null;
        marker = null;
    }

    mapInstance = null;
}
</script>

<template>
    <div class="relative overflow-hidden rounded-lg h-[600px] border border-gray-300" :class="{ 'pointer-events-none opacity-75': isReadOnly }">
        <div ref="mapEl" class="h-full"></div>
        <div ref="autocompleteEl" class="rounded-[3px] shadow-ui-sm border border-gray-300 absolute right-4 left-4 top-4"></div>
    </div>
</template>

<style>
    gmp-place-autocomplete {
        width: 100%;
    }

    /* Info Window Styles */
    .gm-style-iw .gm-style-iw-c {
        box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px !important;
    }

    .gm-style-iw {
        font-family: Inter, sans-serif;
    }

    .gm-style-iw-ch {
        font-size: 14px;
        font-weight: 500;
    }

    .gm-style-iw-d {
        font-size: 13px;
        color: rgb(115 128 140 / 1);
        font-weight: 400;
    }
</style>
