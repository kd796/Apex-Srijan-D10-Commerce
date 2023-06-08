<template>
    <div class="geoui" :class="classes" ref="main">
        <transition>
            <div class="geoui-interstitial" v-if="!hasRequiredFilters">
                <div class="geoui-interstitial-content">
                    <slot name="interstitial" v-bind="scope"></slot>
                </div>
            </div>
        </transition>
        <div class="geoui-filters" v-if="hasRequiredFilters">
            <slot name="filters" v-bind="scope"></slot>
        </div>
        <!-- <div id="map" class="geoui-map">
            <google-map :zoom="zoom" :center="center" :options="mapOptions" ref="map">
                <slot name="markers" v-bind="scope">
                    <google-marker v-for="item in data" v-if="getItemPosition(item)" :position="getItemPosition(item)" :icon="markerIcon" :clickable="true" :key="item.id" @click="openInfowindow(item)"></google-marker>
                </slot>

                <google-infowindow :options="infowindowOptions" :position="infowindowPosition" :opened="infowindowOpen" @closeclick="closeInfowindow">
                    <div class="infowindow">
                        <slot name="infowindow" v-bind="scope"></slot>
                    </div>
                </google-infowindow>
            </google-map>
        </div> -->
        <div id="list" class="geoui-list" v-if="hasRequiredFilters">
            <slot name="list" v-bind="scope"></slot>
        </div>
    </div>
</template>
<script>
    import {gmapApi} from "vue2-google-maps";
    import NetworkedComponentMixin from "../mixins/networked-component-mixin";

    export default {
        mixins: [NetworkedComponentMixin],
        props: {
            requiredFilters: {
                type: Array,
                default: () => []
            }
        },
        data() {
            return {
                center: {lat: 39.83, lng: -98.58},
                zoom: 4,
                selected: null,
            }
        },
        computed: {
            google: gmapApi,
            scope() {
                return {
                    // Properties
                    state: this.state,
                    data: this.data,
                    filters: this.filters,
                    requiredFilters: this.requiredFilters,
                    selected: this.selected,

                    // Methods
                    select: this.select,
                    setState: this.setState,
                    openInfowindow: this.openInfowindow,
                    closeInfowindow: this.closeInfowindow,
                    formatAddress: this.formatAddress,
                    groupedData: this.groupedData,
                }
            },
            classes() {
                return {
                    "geoui--loading": this.querying,
                }
            },
            mapOptions() {
                return {
                    streetViewControl: false,
                    scaleControl: false,
                    mapTypeControl: false,
                    fullscreenControl: false,
                    maxZoom: 16,
                }
            },
            markerIcon() {
                return {
                    url: "/themes/custom/cleco/dist/img/marker.svg",
                    size: this.google ? new this.google.maps.Size(45, 45) : null,
                    anchor: this.google ? new this.google.maps.Point(22, 13) : null,
                }
            },
            infowindowOptions() {
                return {
                    pixelOffset: {
                        width: 0,
                        height: -5,
                    }
                }
            },
            infowindowOpen() {
                return this.selected !== null;
            },
            infowindowPosition() {
                if (this.selected) {
                    return this.getItemPosition(this.selected);
                }

                return null;
            },
            hasRequiredFilters() {
                let passes = true;
                this.requiredFilters.forEach((filter) => {
                    if (passes) {
                        passes = Boolean(this.state[filter]);
                    }
                });

                return passes;
            }
        },
        methods: {
            select(row) {
                this.selected = row;
            },
            closeInfowindow() {
                this.selected = null;
            },
            openInfowindow(row) {
                this.select(row);
            },
            getItemPosition(item) {
                if (!item.attributes.field_geographic_data) {
                    return null;
                }

                return {
                    lat: item.attributes.field_geographic_data.lat,
                    lng: item.attributes.field_geographic_data.lon,
                }
            },
            fitToMarkers() {
                this.$gmapApiPromiseLazy().then(() => {
                    this.$refs.map && this.$refs.map.$mapPromise.then((map) => {
                        if (this.data && this.data.length > 0) {
                            let bounds = new this.google.maps.LatLngBounds();
                            this.data.forEach((item) => {
                                let position = this.getItemPosition(item);
                                if (position) {
                                    bounds.extend(new this.google.maps.LatLng(position));
                                }
                            });
                            map.fitBounds(bounds);
                        }
                    });
                });
            },
            groupedData(path) {
                return this.$options.filters.groupBy(this.data, path);
            },
            validateQuery() {
                return this.hasRequiredFilters;
            },
            handleQueryValidationError() {
                this.data = [];
            }
        },
        watch: {
            data() {
                if (this.$refs.map) {
                    this.fitToMarkers();
                }
            }
        }
    }
</script>
