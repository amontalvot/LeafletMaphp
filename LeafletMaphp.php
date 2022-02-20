<?php
/*
LeafletMaphp class, ver. 0.9
Copyright 2022 Aaron Montalvo

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software Foundation,
Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301  USA


Requirements:
- PHP 7 or higher version;
- An active Internet connection to access Leaflet API

*/
class LeafletMaphpException extends Exception {};

class LeafletMaphp {
    private $div_id;
    private $div_height;
    private $div_width;
    private $lat = '';
    private $lon = '';
    private $zoom = '';
    private $bounds = '';
    private $markers = [];
    private $circles = [];
    private $polygons = [];
    private $geoJSONs = [];

    function __construct(string $id='map', int $height = 300, int $width = 300) {
        $this->div_id = $id;
        $this->div_height = $height;
        $this->div_width = $width;
    }
    function showHeadTags () : string {
        return "\t<link rel='stylesheet' href='https://unpkg.com/leaflet@1.7.1/dist/leaflet.css' integrity='sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==' crossorigin=''/>
    <script src='https://unpkg.com/leaflet@1.7.1/dist/leaflet.js' integrity='sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==' crossorigin=''></script>
    <style>#{$this->div_id} { height: {$this->div_height}px; width: {$this->div_width}px }</style>\n";
    }

    function setCenter(float $lat, float $lon, array $bounds, int $zoom=17) {
        if(count($bounds) != 4) 
            throw new LeafletMaphpException('Bounds array != 4');
        $this->lat = $lat;
        $this->lon = $lon;
        $this->bounds = $bounds;
        $this->zoom = $zoom;
    }
    
    function addMarker (float $lat, float $lon, string $toolTip='', string $onClick='') {
        $marker['lat'] = $lat;
        $marker['lon'] = $lon;
        if($toolTip != '') {
            $marker['toolTip'] = $toolTip;
        }
        if($onClick != '') {
            $marker['onClick'] = $onClick;
        }
        array_push($this->markers, $marker);
    }

    function addCircle(float $lat, float $lon, string $color='red') {
        $circle['lat'] = $lat;
        $circle['lon'] = $lon;
        $circle['color'] = $color;
        array_push($this->circles, $circle);
    }

    function addPolygon (array $polydata, string $color='blue') {
        $polygon['data'] = $polydata;
        $polygon['color'] = $color;
        array_push($this->polygons, $polygon);
    }

    function addGeoJSON(string $geoJSONdata, string $color='blue') {
        $geoJSON['data'] = $geoJSONdata;
        $geoJSON['color'] = $color;
        array_push($this->geoJSONs, $geoJSON);
    }

    function showOnClickDiv() : string {
        return "<div id='onClickDiv'></div>\n";
    }

    function show() : string {
        $scriptText = "var map = L.map('{$this->div_id}');\nL.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors', maxZoom: 18 }).addTo(map);\n";

        if((is_array($this->bounds)) && (count($this->bounds) == 4)) {
            $scriptText .= "map.fitBounds([[{$this->bounds[0]}, {$this->bounds[2]}], [{$this->bounds[1]}, {$this->bounds[3]}]]);\n";
        }

        if((count($this->markers) > 1) || (count($this->circles) > 1) || (count($this->polygons) != 0) || (count($this->geoJSONs) != 0)) {
            $drawnItems = "var drawnItems = new L.FeatureGroup([";
        }

        $onClickFunText = '';
        for($i=0; $i<count($this->markers); ++$i) {
            $markertext = "var marker$i = L.marker([{$this->markers[$i]['lat']}, {$this->markers[$i]['lon']}]";
            
            if(isset($this->markers[$i]['onClick'])) {
                $markertext .= ", {title: '{$this->markers[$i]['onClick']}'}).on('click', onClickShowDiv";
                if($onClickFunText == '') {
                    $onClickFunText = "function onClickShowDiv(e) { document.getElementById('onClickDiv').innerHTML= this.options.title; }\n";
                }
            }
            $markertext .= ")";
            
            if(isset($this->markers[$i]['toolTip'])) {
                $markertext .= ".bindTooltip('{$this->markers[$i]['toolTip']}')";
            }
            $markertext .= ".addTo(map);\n";
            $scriptText .= $markertext;
            if(count($this->markers) > 1) $drawnItems .= "marker$i,";
        }

        for($i=0; $i<count($this->circles); ++$i) {
            $scriptText .= "var circle$i = L.circle([{$this->circles[$i]['lat']}, {$this->circles[$i]['lon']}], {color: '{$this->circles[$i]['color']}'}).addTo(map);\n";
            if(count($this->circles) > 1) $drawnItems .= "circle$i,";
        }

        for($i=0; $i<count($this->polygons); ++$i) {
            $polytext = "var polygon$i = L.polygon([";
            foreach ($this->polygons[$i]['data'] as $coord) {
                $polytext .= "[{$coord[1]}, {$coord[0]}],";
            }
            $polytext = substr($polytext, 0, -1); //remove last ','
            $polytext .= "], {color: '{$this->polygons[$i]['color']}'}).addTo(map);\n";
            $scriptText .= $polytext;
            $drawnItems .= "polygon$i,";
        }

        for($i=0; $i<count($this->geoJSONs); ++$i) {
            $scriptText .= "L.geoJSON({$this->geoJSONs[$i]['data']}, {color: '{$this->geoJSONs[$i]['color']}'}).addTo(map);";
            $drawnItems .= "geoJSON$i,";
        }

        if(isset($drawnItems)) {
            $drawnItems = substr($drawnItems, 0, -1); //remove last ','
            $scriptText .= $drawnItems."]);\nmap.fitBounds(drawnItems.getBounds());\n";
        }
        return "<div id='{$this->div_id}'></div>\n<script>$onClickFunText$scriptText</script>\n";
    }

    function __toString() : string {
        return $this->show();
    }
}
?>