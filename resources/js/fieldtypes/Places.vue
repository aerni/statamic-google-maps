<template>
    <div class="wrapper">
        <div ref="map" class="map"></div>
        <div ref="autocomplete" class="autocomplete"></div>
    </div>
</template>

<style>
    .wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 0.25em;
        height: 600px;
        border: 1px solid rgb(196 204 212 / 1);
    }

    .map {
        height: 100%;
    }

    .autocomplete {
        background-color: #fff;
        border-radius: 5px;
        box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
        padding: 0;
        font-family: Inter, sans-serif;
        font-size: large;
        font-weight: bold;
        position: absolute;
        top: 10px;
        left: 10px;
        right: 10px;
        z-index: 1;
    }

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

<script>
	export default {
		mixins: [Fieldtype],

        data() {
            return {
                map: null,
                marker: null,
                infoWindow: null,
                placeAutocomplete: null,
            };
        },

		mounted() {
            this.init();
		},

		methods: {
            async init() {
                await google.maps.importLibrary("marker")
                await google.maps.importLibrary("places")

                this.initMap();
                this.initPlaceAutocomplete();

                if (this.value) {
                    const place = new google.maps.places.Place({ id: this.value });
                    await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location', 'viewport'] });

                    this.updateMapInfo(place);
                }
			},

            initMap() {
                this.map = new google.maps.Map(this.$refs.map, {
                    mapId: this.meta.map_id,
                    center: this.meta.center,
                    zoom: this.meta.zoom,
                    mapTypeControl: false,
                    fullscreenControl: false,
                    streetViewControl: false,
                    cameraControl: false,
                    zoomControl: true,
                });
            },

            initPlaceAutocomplete() {
                this.placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
                    includedRegionCodes: this.meta.countries,
                    locationBias: this.meta.center,
                });

                this.$refs.autocomplete.appendChild(this.placeAutocomplete);

                this.placeAutocomplete.addEventListener('gmp-select', async ({ placePrediction }) => {
                    const place = placePrediction.toPlace();
                    await place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location', 'viewport'] });

                    this.updateMapInfo(place);

                    this.update(place.id);
                });
            },

            updateMapInfo(place) {
                this.createMarker(place.location);
                this.createInfoWindow(place.displayName, place.formattedAddress, place.location);

                this.placeAutocomplete.locationBias = place.location;

                if (place.viewport) {
                    this.map.fitBounds(place.viewport);
                } else {
                    this.map.setCenter(place.location);
                    this.map.setZoom(17);
                }
            },

            createMarker(location) {
                if (this.marker) {
                    this.marker.map = null;
                }

                this.marker = new google.maps.marker.AdvancedMarkerElement({
                    map: this.map,
                });

                this.marker.position = location;
            },

            createInfoWindow(displayName, formattedAddress, location) {
                this.infoWindow = new google.maps.InfoWindow();

                this.infoWindow.setHeaderContent(displayName);
                this.infoWindow.setContent(formattedAddress);
                this.infoWindow.setPosition(location);

                this.infoWindow.open({
                    map: this.map,
                    anchor: this.marker,
                    shouldFocus: false,
                });

                this.infoWindow.addListener("closeclick", () => this.destroyInfoWindow());
            },

            destroyInfoWindow() {
                if (this.marker) {
                    this.marker.map = null;
                    this.update(null);
                }
            }
		},
	};
</script>
