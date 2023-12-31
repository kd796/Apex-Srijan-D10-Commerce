import Vue from "vue";
// Vue.config.devtools = true;
// Vendors
import * as VueGoogleMaps from "vue2-google-maps";
import {TableColumn, TableComponent} from "vue-table-component";
import {Tab, Tabs} from "vue-tabs-component";
// Custom
import Accordion from "./components/accordion";
import BasicCard from "./components/basic-card";
import BasicCards from "./components/basic-cards";
import Carousel from "./components/carousel";
import CarouselItem from "./components/carousel-item";
import Catalog from "./components/catalog";
import CatalogFilter from "./components/catalog-filter";
import CatalogResults from "./components/catalog-results";
import ComparisonTable from "./components/comparison-table";
import DownloadsCatalog from "./components/downloads-catalog";
import Draggable from "./components/draggable";
import Dropdown from "./components/dropdown";
import ScopedForm from "./components/scoped-form";
import FilterBarItem from "./components/filter-bar-item";
import FloatLabel from "./components/float-label";
import FloatLabelAuto from "./components/float-label-auto";
import FormSelector from "./components/form-selector";
import GeoUI from "./components/geoui";
import Listing from "./components/listing";
import Listings from "./components/listings";
import ModelViewer from "./components/model-viewer";
import Notice from "./components/notice";
import Pagination from "./components/pagination";
import PaginationList from "./components/pagination-list";
import ProductCatalog from "./components/product-catalog";
import ProductFeatures from "./components/product-features";
import ProductComparisonTable from "./components/product-comparison-table";
import SearchCatalog from "./components/search-catalog";
import ShareDropdown from "./components/share-dropdown";
import TableHeaderGroups from "./components/table-header-groups";
import Testimonials from "./components/testimonials";
import ThrottledInput from "./components/throttled-input";
import TransparentImage from "./components/transparent-image";
import VideoFeature from "./components/video-feature";
import VideoPlayer from "./components/video-player";
import VideoThumb from "./components/video-thumb";
import VideoButton from "./components/video-button";
import Zoomable from "./components/zoomable";

// Register
Vue.component("accordion", Accordion);
Vue.component("basic-card", BasicCard);
Vue.component("basic-cards", BasicCards);
Vue.component("carousel", Carousel);
Vue.component("carousel-item", CarouselItem);
Vue.component("catalog", Catalog);
Vue.component("catalog-filter", CatalogFilter);
Vue.component("catalog-results", CatalogResults);
Vue.component("comparison-table", ComparisonTable);
Vue.component("downloads-catalog", DownloadsCatalog);
Vue.component("draggable", Draggable);
Vue.component("dropdown", Dropdown);
Vue.component("filter-bar-item", FilterBarItem);
Vue.component("float-label", FloatLabel);
Vue.component("float-label-auto", FloatLabelAuto);
Vue.component("form-selector", FormSelector);
Vue.component("geoui", GeoUI);
Vue.component("google-infowindow", VueGoogleMaps.InfoWindow);
Vue.component("google-map", VueGoogleMaps.Map);
Vue.component("google-marker", VueGoogleMaps.Marker);
Vue.component("listing", Listing);
Vue.component("listings", Listings);
Vue.component("model-viewer", ModelViewer);
Vue.component("notice", Notice);
Vue.component("pagination", Pagination);
Vue.component("pagination-list", PaginationList);
Vue.component("product-catalog", ProductCatalog);
Vue.component("product-features", ProductFeatures);
Vue.component("product-comparison-table", ProductComparisonTable);
Vue.component("scoped-form", ScopedForm);
Vue.component("search-catalog", SearchCatalog);
Vue.component("share-dropdown", ShareDropdown);
Vue.component("tab", Tab);
Vue.component("table-column", TableColumn);
Vue.component("table-component", TableComponent);
Vue.component("table-header-groups", TableHeaderGroups);
Vue.component("tabs", Tabs);
Vue.component("testimonials", Testimonials);
Vue.component("throttled-input", ThrottledInput);
Vue.component("transparent-image", TransparentImage);
Vue.component("video-feature", VideoFeature);
Vue.component("video-player", VideoPlayer);
Vue.component("video-thumb", VideoThumb);
Vue.component("video-button", VideoButton);
Vue.component("zoomable", Zoomable);