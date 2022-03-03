<?php
/*
LeafletMaphp class, ver. 1.0
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
    const MARKER = 0;
    const CIRCLE = 1;
    const POLYGON = 2;

    private $div_id;
    private $div_height;
    private $div_style;
    private $div_width;
    private $lat = NULL;
    private $lon = NULL;
    private $zoom = 15;
    private $bounds = NULL;
    private $markers = [];
    private $circles = [];
    private $polygons = [];
    private $geoJSONs = [];
    private $onClickFunText = '';

    function __construct(string $id='map', int $height = 300, int $width = 300, string $style='') {
        $this->div_id = $id;
        $this->div_height = $height;
        $this->div_width = $width;
        $this->div_style = $style;
    }
    function showHeadTags () : string {
        return "\t<link rel='stylesheet' href='https://unpkg.com/leaflet@1.7.1/dist/leaflet.css' integrity='sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==' crossorigin=''/>
    <script src='https://unpkg.com/leaflet@1.7.1/dist/leaflet.js' integrity='sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==' crossorigin=''></script>
    <style>#{$this->div_id} { height: {$this->div_height}px; width: {$this->div_width}px }</style>\n";
    }

    function setCenter(float $lat, float $lon, array $bounds=NULL, int $zoom=NULL) {
        $this->lat = $lat;
        $this->lon = $lon;
        if($zoom != NULL)
            $this->zoom = $zoom;
        if($bounds != NULL) {
            if(count($bounds) != 4) 
                throw new LeafletMaphpException('Bounds array != 4');
            $this->bounds = $bounds;
        }
    }
    
    function addMarker (float $lat, float $lon) : int {
        $marker['lat'] = $lat;
        $marker['lon'] = $lon;
        array_push($this->markers, $marker);
        return (count($this->markers)-1);
    }

    function addCircle(float $lat, float $lon, string $color=NULL, float $radius=NULL) : int  {
        $circle['lat'] = $lat;
        $circle['lon'] = $lon;
        if($color != NULL) $circle['color'] = $color;
        if($radius != NULL) $circle['radius'] = $radius;
        array_push($this->circles, $circle);

        //if circles are written and there was no map initialization, getBounds function won't work
        //if no center set, force first circle as center
        if((count($this->circles) == 1) && ($this->lat == NULL) && ($this->lon == NULL)) {
            $this->lat = $lat;
            $this->lon = $lon;
        }
        return (count($this->circles)-1);
    }

    function addPolygon (array $polydata, string $color=NULL) : int  {
        $polygon['data'] = $polydata;
        if($color != NULL) $polygon['color'] = $color;
        array_push($this->polygons, $polygon);
        return (count($this->polygons)-1);
    }

    function addMultipolygon (array $multipolydata, string $color=NULL) : int  {
        $polygon['multi'] = $multipolydata;
        if($color != NULL) $polygon['color'] = $color;
        array_push($this->polygons, $polygon);
        return (count($this->polygons)-1);
}
    
    function addTooltip (int $element_type, int $element_id, string $toolTip) {
        switch($element_type) {
            case self::MARKER:
                if(!isset($this->markers[$element_id])) throw new LeafletMaphpException('Wrong marker ID');
                $this->markers[$element_id]['toolTip'] = $toolTip;
                break;
            case self::CIRCLE:
                if(!isset($this->circles[$element_id])) throw new LeafletMaphpException('Wrong circle ID');
                $this->circles[$element_id]['toolTip'] = $toolTip;
                break;
            case self::POLYGON:
                if(!isset($this->polygons[$element_id])) throw new LeafletMaphpException('Wrong polygon ID');
                $this->polygons[$element_id]['toolTip'] = $toolTip;
                break;
            default:
                throw new LeafletMaphpException('Wrong element type');
                break;
        }
    }
    
    function addPopUp (int $element_type, int $element_id, string $popUp) {
        switch($element_type) {
            case self::MARKER:
                if(!isset($this->markers[$element_id])) throw new LeafletMaphpException('Wrong marker ID');
                $this->markers[$element_id]['popUp'] = $popUp;
                break;
            case self::CIRCLE:
                if(!isset($this->circles[$element_id])) throw new LeafletMaphpException('Wrong circle ID');
                $this->circles[$element_id]['popUp'] = $popUp;
                break;
            case self::POLYGON:
                if(!isset($this->polygons[$element_id])) throw new LeafletMaphpException('Wrong polygon ID');
                $this->polygons[$element_id]['popUp'] = $popUp;
                break;
            default:
                throw new LeafletMaphpException('Wrong element type');
                break;
        }
    }
    
    function addOnClickText (int $element_type, int $element_id, string $onClick) {
        switch($element_type) {
            case self::MARKER:
                if(!isset($this->markers[$element_id])) throw new LeafletMaphpException('Wrong marker ID');
                $this->markers[$element_id]['onClick'] = $onClick;
                break;
            case self::CIRCLE:
                if(!isset($this->circles[$element_id])) throw new LeafletMaphpException('Wrong circle ID');
                $this->circles[$element_id]['onClick'] = $onClick;
                break;
            case self::POLYGON:
                if(!isset($this->polygons[$element_id])) throw new LeafletMaphpException('Wrong polygon ID');
                $this->polygons[$element_id]['onClick'] = $onClick;
                break;
            default:
                throw new LeafletMaphpException('Wrong element type');
                break;
        }
    }

    function addGeoJSON(string $geoJSONdata, string $color=NULL) {
        $geoJSON['data'] = $geoJSONdata;
        if($color != NULL) $geoJSONdata['color'] = $color;
        array_push($this->geoJSONs, $geoJSON);
        
        //if geosjon data are written and there was no map initialization, getBounds function won't work
        //if no center set, force first geojson first coordinate as center
        if((count($this->geoJSONs) == 1) && ($this->lat == NULL) && ($this->lon == NULL)) {
            $decoded_data = json_decode($geoJSONdata, true);
            $this->lat = $decoded_data['geometries'][0]['coordinates'][0][0][0][1];
            $this->lon = $decoded_data['geometries'][0]['coordinates'][0][0][0][0];
        }
    }

    function showOnClickDiv() : string {
        return "<div id='onClickDiv'></div>\n";
    }

    function show() : string {
        if((count($this->markers) == 0) && (count($this->circles) == 0) && (count($this->polygons) == 0) && (count($this->geoJSONs) == 0)) {
            if(($this->lat == NULL) && ($this->lon == NULL)) {
                throw new LeafletMaphpException('No items added nor center set: Map is inviewable');
            }
        } else {
            $drawnItems = "var drawnItems = new L.FeatureGroup([";
        }

        $scriptText = "var map = L.map('{$this->div_id}');\nL.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors', maxZoom: 18 }).addTo(map);\n";
        
        if((is_array($this->bounds)) && (count($this->bounds) == 4)) {
            $scriptText .= "map.fitBounds([[{$this->bounds[0]}, {$this->bounds[2]}], [{$this->bounds[1]}, {$this->bounds[3]}]]);\n";
        }
        else if(($this->lat != NULL) && ($this->lon != NULL)){
            $scriptText .= "map.setView([{$this->lat}, {$this->lon}], {$this->zoom});\n";
        }
        if((count($this->markers) != 0) || (count($this->circles) != 0) || (count($this->polygons) != 0) || (count($this->geoJSONs) != 0)) {
            $drawnItems = "var drawnItems = new L.FeatureGroup([";
        }

        for($i=0; $i<count($this->markers); ++$i) {
            $markertext = "var marker$i = L.marker([{$this->markers[$i]['lat']}, {$this->markers[$i]['lon']}]";
            if(isset($this->markers[$i]['onClick'])) {
                $markertext .= ", {onClickText: '{$this->markers[$i]['onClick']}'}";
            }
            $markertext .= ')';
            if(isset($this->markers[$i]['onClick'])) $this->addOnClickFunction($this->markers[$i], $markertext);
            $this->addText($this->markers[$i], $markertext);
            $markertext .= ".addTo(map);\n";
            $scriptText .= $markertext;
            $drawnItems .= "marker$i,";
        }

        for($i=0; $i<count($this->circles); ++$i) {
            $circleText = "var circle$i = L.circle([{$this->circles[$i]['lat']}, {$this->circles[$i]['lon']}]";
            if(isset($this->circles[$i]['color']) || (isset($this->circles[$i]['radius'])) || (isset($this->circles[$i]['onClick']))) {
                $optionsText = '';
                if(isset($this->circles[$i]['color'])) {
                    $optionsText .= "color: '{$this->circles[$i]['color']}'";
                }
                if(isset($this->circles[$i]['radius'])) {
                    if($optionsText != '') $optionsText .= ', ';
                    $optionsText .= "radius: '{$this->circles[$i]['radius']}'";
                }
                if(isset($this->circles[$i]['onClick'])) {
                    if($optionsText != '') $optionsText .= ', ';
                    $optionsText .= "onClickText: '{$this->circles[$i]['onClick']}'";
                }
                $circleText .= ', {'.$optionsText.'}';
            }
            $circleText .= ')';
            if(isset($this->circles[$i]['onClick'])) $this->addOnClickFunction($this->circles[$i], $circleText);
            $this->addText($this->circles[$i], $circleText);
            $circleText .= ".addTo(map);\n";
            $scriptText .= $circleText;
            $drawnItems .= "circle$i,";
        }
        for($i=0; $i<count($this->polygons); ++$i) {
            $polygonText = "var polygon$i = L.polygon([";
            if(isset($this->polygons[$i]['data'])) {
                //simple polygon
                foreach ($this->polygons[$i]['data'] as $coord) {
                    $polygonText .= "[{$coord[1]}, {$coord[0]}],";
                }
            } else if(isset($this->polygons[$i]['multi'])) {
                //multipolygon
                foreach ($this->polygons[$i]['multi'] as $polygon) {
                    $polygonText .= "[";
                    foreach ($polygon as $coord) {
                        if(count($coord) == 2) {
                            $polygonText .= "[{$coord[1]}, {$coord[0]}],";
                        } else {
                            $polygonText .= "[";
                            foreach ($coord as $realCoord) {
                                $polygonText .= "[{$realCoord[1]}, {$realCoord[0]}],";
                            }
                            $polygonText .= "],";
                        }
                    }
                    $polygonText = substr($polygonText, 0, -1); //remove last ','
                    $polygonText .= "],";
                }
            }
            $polygonText = substr($polygonText, 0, -1); //remove last ','
            $polygonText .= "]";
            if(isset($this->polygons[$i]['color']) || (isset($this->polygons[$i]['onClick']))) {
                $optionsText = '';
                if(isset($this->polygons[$i]['color'])) {
                    $optionsText .= "color: '{$this->polygons[$i]['color']}'";
                }
                if(isset($this->polygons[$i]['onClick'])) {
                    if($optionsText != '') $optionsText .= ', ';
                    $optionsText .= "onClickText: '{$this->polygons[$i]['onClick']}'";
                }
                $polygonText .= ', {'.$optionsText.'}';
            }
            $polygonText .= ')';
            if(isset($this->polygons[$i]['onClick'])) $this->addOnClickFunction($this->polygons[$i], $polygonText);
            $this->addText($this->polygons[$i], $polygonText);
            $polygonText .= ".addTo(map);\n";
            $scriptText .= $polygonText;
            $drawnItems .= "polygon$i,";
        }

        for($i=0; $i<count($this->geoJSONs); ++$i) {
            $geoJSONText = "L.geoJSON({$this->geoJSONs[$i]['data']}";
            if(isset($this->geoJSONs[$i]['color'])) {
                $geoJSONText .= ", {color: '{$this->geoJSONs[$i]['color']}'}";
            }
            $geoJSONText .= ").addTo(map);\n";
            $scriptText .= $geoJSONText;
            $drawnItems .= "geoJSON$i,";
        }

        if(isset($drawnItems)) {
            $drawnItems = substr($drawnItems, 0, -1); //remove last ','
            $scriptText .= $drawnItems."]);\nmap.fitBounds(drawnItems.getBounds());\n";
        }

        $theStyle = '';
        if($this->div_style != '') {
            $theStyle = " style='$this->div_style'";
        }
        return "<div id='{$this->div_id}'$theStyle></div>\n<script>{$this->onClickFunText}$scriptText</script>\n";
    }

    function __toString() : string {
        return $this->show();
    }

    private function addText(array $item, string &$itemText) {
        
        if(isset($item['toolTip'])) {
            $itemText .= ".bindTooltip('{$item['toolTip']}')";
        }
        if(isset($item['popUp'])) {
            $itemText .= ".bindPopup('{$item['popUp']}')";
        }
    }

    private function addOnClickFunction(array $item, string &$itemText) {
        
        if(isset($item['onClick'])) {
            $itemText .= ".on('click', onClickShowDiv)";
            if($this->onClickFunText == '') $this->onClickFunText = "function onClickShowDiv(e) { document.getElementById('onClickDiv').innerHTML= this.options.onClickText; }\n";            
        }
    }
}
?>