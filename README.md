LeafletMaphp
============
A simple PHP wrapper to Leaflet JS using OSM data and tiles
Current version is 1.3

Basic usage
-----------
Create a new instance of LeafletMaphp.
```php
$map = new LeafletMaphp($map?, $height?, $width?, $style?, $tiles?);
```
The parameters, all of them non-compulsory, are:
* a name to the required div for the map (by default 'map').
* height and width (by default 300px300px).
* a string containing the desired css.
* the desired tiles to be used (see below).

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
* CUSTOM_TILES: Tiles with a custom format. You have to configure them using the setCustomTiles method (see below).

You must add the required Leaflet tags before closing the &lt;head&gt;. Current Leaflet version used is version 1.9.3.

```php
echo '<head>'.$map->showHeadTags().'</head>';
```

Then you only have to center for the map location, 
```php
$map->setCenter($latitude, $longitude, $bounds?, $zoom?) {
```
The parameters are:
* latitude and longitude coordinates.
* optionally, the bounding box for the map, which must be an array of 4 elements.
* optionally, a zoom value to be used by default.

Finally you have to show the &lt;div&gt; element (the proper map) by calling its own method in the location you want it to be displayed.
```php
echo $map->show();
```

Adding Leaflet items
--------------------
You can add several Leaflet items using the proper method. Several methods have optional parameters.

Markers, Circles, Polygons, Polylines and Multipolygons will return the numeric identifier of the current element of its type. Circle #0 is different from Marker #0 and from Polygon #0. Please note that Polygons and Multipolygons are in the same list, so the id of a Multipolygon added after Polygon #3 will be Multipolygon #4.

When adding several elements of any type, the bounding box is automatically recalculated for keeping all of them on sight.
```php
$map->addMarker($lat, $lon);
$map->addCircle($lat, $lon, $color?, $radius?);
$map->addPolygon($data, $color?);
$map->addPolyline($data, $color?);
$map->addMultipolygon($data, $color?);
$map->addGeoJSON($data, $color?);
```

**For a map to be correctly rendered, you must either add any item or set the map center coordinates.**

Adding text on items
--------------------
You can add several Leaflet text elements: **PopUp**, **ToolTip** and **onClickText**. 
```php
$map->addTooltip($element_type, $element_id, $toolTipText);
$map->addPopUp($element_type, $element_id, $PopupText);
$map->addOnClickText($element_type, $element_id, $onClickText);
```
All of them can be added in elements of type *Marker*, *Circle*, *Polygon*, *Polyline* and *Multipolygon*.
You have to select the type using the constants defined (*MARKER*, *CIRCLE*, *POLYGON*) and the identifier of the desired element. 

If you have added any onClickText on any marker, you have to show also a new &lt;div&gt; element that will have the id 'onClickDiv'. You can do it manually or using the *showOnClickDiv* method.
```php
echo $map->showOnClickDiv();
```

Configuring custom tiles
------------------------
When *CUSTOM_TILES* option has been set, you must configure it using the *setCustomTiles* method.
```php
echo $map->setCustomTiles($tilesIp, $tilesWebAddr, $tilesAttribution, $tilesMinZoom, $tilesMaxZoom);
```