LeafletMaphp
============
A simple PHP wrapper to Leaflet JS using OSM data and tiles

Basic usage
-----------

Create a new instance of LeafletMaphp. You can optionally give a name to the required div for the map, height and width in px (by default 'map' and 300), and style in a text string containing the desired css. Attribution to OpenStreetMap contributors will be added by default.
```php
$map = new LeafletMaphp();
```

Before closing the &lt;head&gt; tag add the required Leaflet tags (curretly for Leaflet version 1.7.1)

```php
echo $map->showHeadTags();
```

You can add several Leaflet elements using the proper method. Several methods have optional parameters.

Markers, Circles and Polygons (including Multipolygons) will return the numeric id of the current element of its type. Circle #0 is different from Marker #0 and from Polygon #0. Please note that Polygons and Multipolygons are in the same list, so the id of a Multipolygon added after Polygon #3 will be Multipolygon #4.

When adding several elements of any type, the Bounding Box is automatically recalculated for keeping all of them on sight.
```php
$map->setCenter($lat, $lon, $bounds, $zoom);
$map->addMarker($lat, $lon);
$map->addCircle($lat, $lon, $color?);
$map->addPolygon($data, $color?);
$map->addMultipolygon($data, $color?);
$map->addGeoJSON($data, $color?);
```

You can add several Leaflet text elements: Popups, ToolTips and onClickText. All of them can be added in Markers, Circles and Polygons (including Multipolygons). You have to select the type using the constants defined (MARKER, CIRCLE, POLYGON and the id of the desired element)
```php
$map->addTooltip($element_type, $element_id, $toolTipText);
$map->addPopUp($element_type, $element_id, $PopupText);
$map->addOnClickText($element_type, $element_id, $onClickText);
```

Finally you have to show the &lt;div&gt; element by calling its own method where you desired
```php
echo $map->show();
```

If you have added any onClickText on any marker, you have to show also a new &lt;div&gt; element that will have the id 'onClickDiv'
```php
echo $map->showOnClickDiv();
```