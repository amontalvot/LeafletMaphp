LeafletMaphp
============
A simple PHP wrapper to Leaflet JS

Basic usage
-----------

Create a new instance of LeafletMaphp.
```php
$map = new LeafletMaphp();
```

Before closing the <head> tag add the required Leaflet tags (curretly for Leaflet version 1.7.1)

```php
echo $map->showHeadTags();
```

You can add several Leaflet elements using the proper method
```php
echo $map->showHeadTags();
```


    //Incluimos en el head las etiquetas necesarias
    echo $map->showHeadTags()."\t<title>{$place['place_id']}</title>\n</head>\n<body>\n";

    // Establecemos centro, zoom y tamaño del mapa
    $map->setCenter($place['lat'], $place['lon'], $place["boundingbox"]);

    // Añadimos un marcador en la posición obtenida
    $map->addMarker($place['lat'], $place['lon'], $place["display_name"], "Has clicado las coordenadas {$place['lat']}, {$place['lon']}");

    // Añadimos un círculo en la posición obtenida
    $map->addCircle($place['lat'], $place['lon']);

    // Añadimos un polígono que rodee el objeto si existe
    if((isset($place['geojson'])) && ($place['geojson']['type'] == 'Polygon')) {
        $map->addPolygon($place['geojson']['coordinates'][0]);
    }
    else if ($place['osm_type'] == 'relation') {
        $geoJSON_url = "http://polygons.openstreetmap.fr/get_geojson.py?id={$place['osm_id']}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_URL, $geoJSON_url);
        $geoJSON = curl_exec($ch);
        curl_close($ch);
        $map->addGeoJSON($geoJSON);
    }
    echo "{$place['display_name']}<br>\n";
    // Mostramos el mapa
    echo $map->show();
    // Mostramos el div que se usará al pulsar el mapa
    echo $map->showOnClickDiv();