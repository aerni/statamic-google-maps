<?php

namespace Aerni\GoogleMaps;

use Statamic\Statamic;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Aerni\GoogleMaps\Fieldtypes\Places;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/cp.js',
            'resources/css/cp.css',
        ],
        'publicDirectory' => 'resources/dist',
        'hotFile' => __DIR__.'/../resources/dist/hot',
    ];

    protected $fieldtypes = [
        Places::class,
    ];

    public function bootAddon()
    {
        Statamic::inlineScript('
            (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
                key: "'.config('google-maps.google_places_api_key').'",
                v: "weekly"
            });
        ');

        Collection::macro('snakeKeys', function () {
            return $this->mapWithKeys(function ($value, $key) {
                $key = is_string($key) ? Str::snake($key) : $key;

                if ($value instanceof Collection) {
                    $value = $value->snakeKeys();
                } elseif (is_array($value)) {
                    $value = collect($value)->snakeKeys()->all();
                }

                return [$key => $value];
            });
        });
    }
}
