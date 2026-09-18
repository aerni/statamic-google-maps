<?php

namespace Aerni\GoogleMaps\Fieldtypes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use SKAgarwal\GoogleApi\PlacesNew\GooglePlaces;
use Statamic\Dictionaries\Countries;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class Places extends Fieldtype
{
    protected $icon = 'earth';

    protected $keywords = ['google', 'maps', 'google maps'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'map_id' => [
                        'display' => 'Map ID',
                        'instructions' => 'Specify the Map ID to use for the map.',
                        'type' => 'text',
                    ],
                    'center' => [
                        'display' => 'Center',
                        'instructions' => 'Specify the Map ID to use for the map.',
                        'type' => 'group',
                        'fields' => [
                            [
                                'handle' => 'lat',
                                'field' => [
                                    'display' => 'Latitude',
                                    'type' => 'float',
                                    'width' => 50,
                                ],
                            ],
                            [
                                'handle' => 'lng',
                                'field' => [
                                    'display' => 'Longitude',
                                    'type' => 'float',
                                    'width' => 50,
                                ],
                            ],
                        ],
                    ],
                    'zoom' => [
                        'display' => 'Zoom',
                        'instructions' => 'Specify the Map ID to use for the map.',
                        'type' => 'integer',
                    ],
                    'countries' => [
                        'display' => 'Countries',
                        'instructions' => 'Restrict the search results to specific countries.',
                        'type' => 'dictionary',
                        'dictionary' => [
                            'type' => 'countries',
                            'emojis' => true,
                        ],
                    ],
                    'fields' => [
                        'display' => 'Fields',
                        'instructions' => 'Restrict the fields you want to fetch from the Google Places API.',
                        'type' => 'select',
                        'multiple' => true,
                        // Available options: https://developers.google.com/maps/documentation/places/web-service/place-details
                        'options' => [
                            'attributions',
                            'id',
                            'name',
                            'photos',
                            'addressComponents',
                            'addressDescriptor',
                            'adrFormatAddress',
                            'formattedAddress',
                            'location',
                            'plusCode',
                            'postalAddress',
                            'shortFormattedAddress',
                            'types',
                            'viewport',
                            'accessibilityOptions',
                            'businessStatus',
                            'containingPlaces',
                            'displayName',
                            'googleMapsLinks',
                            'googleMapsUri',
                            'iconBackgroundColor',
                            'iconMaskBaseUri',
                            'primaryType',
                            'primaryTypeDisplayName',
                            'pureServiceAreaBusiness',
                            'subDestinations',
                            'utcOffsetMinutes',
                            'currentOpeningHours',
                            'currentSecondaryOpeningHours',
                            'internationalPhoneNumber',
                            'nationalPhoneNumber',
                            'priceLevel',
                            'priceRange',
                            'rating',
                            'regularOpeningHours',
                            'regularSecondaryOpeningHours',
                            'userRatingCount',
                            'websiteUri',
                            'allowsDogs',
                            'curbsidePickup',
                            'delivery',
                            'dineIn',
                            'editorialSummary',
                            'evChargeAmenitySummary',
                            'evChargeOptions',
                            'fuelOptions',
                            'generativeSummary',
                            'goodForChildren',
                            'goodForGroups',
                            'goodForWatchingSports',
                            'liveMusic',
                            'menuForChildren',
                            'neighborhoodSummary',
                            'parkingOptions',
                            'paymentOptions',
                            'outdoorSeating',
                            'reservable',
                            'restroom',
                            'reviews',
                            'reviewSummary',
                            'routingSummaries',
                            'servesBeer',
                            'servesBreakfast',
                            'servesBrunch',
                            'servesCocktails',
                            'servesCoffee',
                            'servesDessert',
                            'servesDinner',
                            'servesLunch',
                            'servesVegetarianFood',
                            'servesWine',
                            'takeout',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'map_id' => $this->config('map_id', config('google-maps.map_id')),
            'center' => $this->config('center', config('google-maps.center')),
            'zoom' => $this->config('zoom', config('google-maps.zoom')),
            'countries' => collect((new Countries)->optionItems())
                ->intersect($this->config('countries'))
                ->map(fn ($item) => $item['iso2'])
                ->values(),
        ];
    }

    public function preProcessIndex($data): string
    {
        return $this->placeDetails($data)->get('short_formatted_address');
    }

    public function augment($data): ?Collection
    {
        return $this->placeDetails($data);
    }

    protected function placeDetails(?string $id = null): ?Collection
    {
        if (empty($id)) {
            return null;
        }

        $params = $this->localizationParams();

        return Cache::rememberForever(
            $this->placeDetailsCacheKey($id, $params),
            fn () => GooglePlaces::make(config('google-maps.google_places_api_key'))
                ->placeDetails($id, $this->config('fields', ['*']), $params)
                ->collect()
                ->snakeKeys()
        );
    }

    protected function localizationParams(): array
    {
        return array_filter([
            'languageCode' => str_replace('_', '-', Site::current()->lang()),
            'regionCode' => $this->regionCode(),
        ], fn ($value) => filled($value));
    }

    protected function regionCode(): ?string
    {
        return \Locale::getRegion(Site::current()->locale()) ?: null;
    }

    protected function placeDetailsCacheKey(string $id, array $params): string
    {
        return collect(['google-maps-place-details', $id, ...array_values($params)])
            ->filter()
            ->implode('-');
    }
}
