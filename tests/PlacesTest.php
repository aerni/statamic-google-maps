<?php

namespace Aerni\GoogleMaps\Tests;

use Aerni\GoogleMaps\Fieldtypes\Places;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Statamic\Facades\Site;

class PlacesTest extends TestCase
{
    #[Test]
    public function it_localizes_place_details_using_the_current_site(): void
    {
        Site::setSites([
            'de' => ['name' => 'Deutsch', 'locale' => 'de_CH', 'url' => '/'],
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => '/en/'],
        ]);

        Site::setCurrent('de');

        $this->assertSame(
            ['languageCode' => 'de', 'regionCode' => 'CH'],
            $this->localizationParams()
        );

        Site::setCurrent('en');

        $this->assertSame(
            ['languageCode' => 'en', 'regionCode' => 'US'],
            $this->localizationParams()
        );
    }

    #[Test]
    public function it_omits_the_region_code_when_the_site_locale_has_no_region(): void
    {
        Site::setSites([
            'en' => ['name' => 'English', 'locale' => 'en', 'url' => '/'],
        ]);

        Site::setCurrent('en');

        $this->assertSame(
            ['languageCode' => 'en'],
            $this->localizationParams()
        );
    }

    #[Test]
    public function it_caches_place_details_per_site_locale(): void
    {
        Site::setSites([
            'de' => ['name' => 'Deutsch', 'locale' => 'de_CH', 'url' => '/'],
        ]);

        Site::setCurrent('de');

        Cache::partialMock()
            ->shouldReceive('rememberForever')
            ->once()
            ->withArgs(fn ($key) => $key === 'google-maps-place-details-ChIJ123-de-CH')
            ->andReturn(collect(['formatted_address' => 'Bahnhofstrasse 1, 8001 Zürich']));

        $this->assertSame(
            'Bahnhofstrasse 1, 8001 Zürich',
            (new Places)->augment('ChIJ123')->get('formatted_address')
        );
    }

    protected function localizationParams(): array
    {
        $method = new ReflectionMethod(Places::class, 'localizationParams');

        return $method->invoke(new Places);
    }
}
