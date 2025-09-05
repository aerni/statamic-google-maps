const { createElementBlock, openBlock, createElementVNode } = window.Vue;
const {
  Fieldtype,
  IndexFieldtype,
  FieldtypeMixin,
  IndexFieldtypeMixin,
  DateFormatter,
  ItemActions,
  requireElevatedSession,
  requireElevatedSessionIf
} = __STATAMIC__.core;
const _export_sfc = (sfc, props) => {
  const target = sfc.__vccOpts || sfc;
  for (const [key, val] of props) {
    target[key] = val;
  }
  return target;
};
const _sfc_main = {
  mixins: [FieldtypeMixin],
  data() {
    return {
      map: null,
      marker: null,
      infoWindow: null,
      placeAutocomplete: null
    };
  },
  mounted() {
    this.init();
  },
  methods: {
    async init() {
      await google.maps.importLibrary("marker");
      await google.maps.importLibrary("places");
      this.initMap();
      this.initPlaceAutocomplete();
      if (this.value) {
        const place = new google.maps.places.Place({ id: this.value });
        await place.fetchFields({ fields: ["displayName", "formattedAddress", "location", "viewport"] });
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
        zoomControl: true
      });
    },
    initPlaceAutocomplete() {
      this.placeAutocomplete = new google.maps.places.PlaceAutocompleteElement({
        includedRegionCodes: this.meta.countries,
        locationBias: this.meta.center
      });
      this.$refs.autocomplete.appendChild(this.placeAutocomplete);
      this.placeAutocomplete.addEventListener("gmp-select", async ({ placePrediction }) => {
        const place = placePrediction.toPlace();
        await place.fetchFields({ fields: ["displayName", "formattedAddress", "location", "viewport"] });
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
        map: this.map
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
        shouldFocus: false
      });
      this.infoWindow.addListener("closeclick", () => this.destroyInfoWindow());
    },
    destroyInfoWindow() {
      if (this.marker) {
        this.marker.map = null;
        this.update(null);
      }
    }
  }
};
const _hoisted_1 = { class: "gm:relative gm:overflow-hidden gm:rounded-lg gm:h-[600px] gm:border gm:border-gray-300" };
const _hoisted_2 = {
  ref: "map",
  class: "gm:h-full"
};
const _hoisted_3 = {
  ref: "autocomplete",
  class: "autocomplete"
};
const _hoisted_4 = {
  ref: "autocomplete",
  class: "gm:rounded-[3px] shadow-ui-sm gm:border gm:border-gray-300 gm:absolute gm:right-4 gm:left-4 gm:top-4"
};
function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return openBlock(), createElementBlock("div", _hoisted_1, [
    createElementVNode("div", _hoisted_2, null, 512),
    createElementVNode("div", _hoisted_3, null, 512),
    createElementVNode("div", _hoisted_4, null, 512)
  ]);
}
const Places = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
Statamic.booting(() => {
  Statamic.$components.register("places-fieldtype", Places);
});
//# sourceMappingURL=google-maps-BP6NHCj2.js.map
