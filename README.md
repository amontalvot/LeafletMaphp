LeafletMaphp
============
A simple PHP wrapper to Leaflet JS using OSM data and tiles.
Current version is 1.1.

Basic usage
-----------
Create a new instance of LeafletMaphp. You can optionally give a name to the required div for the map, height and width in px (by default 'map' and 300), a string containing the desired css and the desired tiles to be used.
```php
$map = new LeafletMaphp($map?, $height?, $width?, $style?, $tiles?);
```

The available tiles are from Spain Instituto Geográfico Nacional (IGN, https://www.ign.es/web/ign/portal/ide-area-nodo-ide-ign) and Catastro (http://www.catastro.minhap.gob.es/esp/wms.asp), both for Spain only, and the free ones listed in https://wiki.openstreetmap.org/wiki/Tiles. All of them are attributed by default, but see OSM wiki for updated information about availability and attribution.
* ES_PNOA: Spain's IGN Ortofotos máxima actualidad del PNOA
* ES_RASTER_IGN: Spain's IGN Cartografía raster
* ES_IGN_BASE: Spain's IGN Mapa base
* ES_CATASTRO: Spain's Catastro
* OSM: OpenStreetMap's Standard tile layer
* OSM_DE: OpenStreetMap's fork of the Standard tile layer. Used by default
* OSM_FR: OpenStreetMap's France
* OSM_HUMANITARIAN: Humanitarian focused OSM base layer
* STAMEN_TONER: Stamen Toner
* STAMEN_TERRAIN: Stamen Terrain
* STAMEN_WATERCOLOR: Stamen Watercolor
* OPNVKARTE_TRANSPORT: Öpnvkarte Transport Map
* OPEN_TOPO_MAP: OpenTopoMap

You must add the required Leaflet tags before closing the &lt;head&gt;. Current Leaflet version used is version 1.7.1.

```php
echo '<head>'.$map->showHeadTags().'</head>';
```

Then you only have to center for the map location, using latitude and longitude coordinates. You can also optionally add the bounding box for the map, which must be an array of 4 elements, and a zoom value to be used by default.
```php
$map->setCenter($latitude, $longitude, $bounds?, $zoom?) {
```

Finally you have to show the &lt;div&gt; element by calling its own method where you desired
```php
echo $map->show();
```

Adding Leaflet items
--------------------
You can add several Leaflet items using the proper method. Several methods have optional parameters.

Markers, Circles and Polygons (including Multipolygons) will return the numeric id of the current element of its type. Circle #0 is different from Marker #0 and from Polygon #0. Please note that Polygons and Multipolygons are in the same list, so the id of a Multipolygon added after Polygon #3 will be Multipolygon #4.

When adding several elements of any type, the bounding box is automatically recalculated for keeping all of them on sight.
```php
$map->addMarker($lat, $lon);
$map->addCircle($lat, $lon, $color?, $radius?);
$map->addPolygon($data, $color?);
$map->addMultipolygon($data, $color?);
$map->addGeoJSON($data, $color?);
```

For a map to be correctly rendered, you must either add any item or set the map center coordinates.

Adding text on items
--------------------
You can add several Leaflet text elements: Popups, ToolTips and onClickText. All of them can be added in Markers, Circles and Polygons (including Multipolygons). You have to select the type using the constants defined (MARKER, CIRCLE, POLYGON and the id of the desired element)
```php
$map->addTooltip($element_type, $element_id, $toolTipText);
$map->addPopUp($element_type, $element_id, $PopupText);
$map->addOnClickText($element_type, $element_id, $onClickText);
```

If you have added any onClickText on any marker, you have to show also a new &lt;div&gt; element that will have the id 'onClickDiv'
```php
echo $map->showOnClickDiv();
```