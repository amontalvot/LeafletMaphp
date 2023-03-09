<?php
/*
LeafletMaphp class, ver. 1.3
Copyright 2023 Aaron Montalvo

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
    const POLYLINE = 3;

    const ES_PNOA = 10;
    const ES_RASTER_IGN = 11;
    const ES_IGN_BASE = 12;
    const ES_CATASTRO = 13;
    const OSM = 14;
    const OSM_DE = 15;
    const OSM_FR = 16;
    const OSM_HUMANITARIAN = 17;
    const STAMEN_TONER = 18;
    const STAMEN_TERRAIN = 19;
    const STAMEN_WATERCOLOR = 20;
    const OPNVKARTE_TRANSPORT = 21;
    const OPEN_TOPO_MAP = 22;
    const CUSTOM_TILES = 23;

    private $div_id;
    private $div_height;
    private $div_style;
    private $div_width;
    private $tiles = self::OSM_DE;
    private $tilesUrl = '';
    private $tilesAtt = '';
    private $tilesMinZoom = 1;
    private $tilesMaxZoom = 18;
    private $lat = NULL;
    private $lon = NULL;
    private $zoom = 15;
    private $bounds = NULL;
    private $markers = [];
    private $circles = [];
    private $polygons = [];
    private $polylines = [];
    private $geoJSONs = [];
    private $onClickFunText = '';

    function __construct(string $id='map', int $height = 300, int $width = 300, string $style='', int $tiles=self::OSM_DE) {
        $this->div_id = $id;
        $this->div_height = $height;
        $this->div_width = $width;
        $this->div_style = $style;
        if(!empty($tiles)) {
            if(($tiles < self::ES_PNOA) || ($tiles > self::CUSTOM_TILES)) {
                throw new LeafletMaphpException("Wrong tiles selection {$tiles}");
            } else {
                $this->tiles = $tiles;
            }
        }
    }
	
    function setCustomTiles(string $tilesIp='', string $tilesWeb='', string $tilesAtt = '', int $tilesMinZoom = 0, int $tilesMaxZoom = 0) {
		if(!empty($tilesIp) && !empty($tilesWeb) && !empty($tilesAtt) && !empty($tilesMinZoom) && !empty($tilesMaxZoom)) {
			$this->tilesUrl = "http://{$tilesIp}/{$tilesWeb}";
			$this->tilesAtt = $tilesAtt;
			$this->tilesMinZoom = $tilesMinZoom;
			$this->tilesMaxZoom = $tilesMaxZoom;
		}
	}
	
    function showHeadTags () : string {
        return "\t<link rel='stylesheet' href='https://unpkg.com/leaflet@1.9.3/dist/leaflet.css' integrity='sha256-kLaT2GOSpHechhsozzB+flnD+zUyjE2LlfWPgU04xyI=' crossorigin=''/>
    <script src='https://unpkg.com/leaflet@1.9.3/dist/leaflet.js' integrity='sha256-WBkoXOwTeyKclOHuWtc+i2uENFpDZ9YPdf5Hf+D7ewM=' crossorigin=''></script>
    <style>#{$this->div_id} { height: {$this->div_height}px; width: {$this->div_width}px }</style>\n";
    }

    function setCenter(float $lat, float $lon, array $bounds=NULL, int $zoom=NULL) {
        $this->lat = $lat;
        $this->lon = $lon;
        if($zoom != NULL)
            $this->zoom = $zoom;
        if($bounds != NULL) {
            if(count($bounds) != 4) 
                throw new LeafletMaphpException('Bounds array count != 4');
            $this->bounds = $bounds;
        }
    }
    
    function addMarker (float $lat, float $lon) : int {
        $marker['lat'] = $lat;
        $marker['lon'] = $lon;
        array_push($this->markers, $marker);
        return (count($this->markers)-1);
    }

    function addCircle(float $lat, float $lon, string $color=NULL, float $radius=NULL) : int {
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

    function addPolyline (array $polydata, string $color=NULL) : int {
        if(count($polydata) == 0)
            throw new LeafletMaphpException('Polydata is empty');
        $polyline['data'] = $polydata;
        if($color != NULL) $polyline['color'] = $color;
        array_push($this->polylines, $polyline);
        return (count($this->polylines)-1);
    }

    function addPolygon (array $polydata, string $color=NULL) : int {
        if(count($polydata) == 0)
            throw new LeafletMaphpException('Polydata is empty');
        $polygon['data'] = $polydata;
        if($color != NULL) $polygon['color'] = $color;
        array_push($this->polygons, $polygon);
        return (count($this->polygons)-1);
    }

    function addMultipolygon (array $multipolydata, string $color=NULL) : int {
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
            case self::POLYLINE:
                if(!isset($this->polylines[$element_id])) throw new LeafletMaphpException('Wrong polyline ID');
                $this->polylines[$element_id]['toolTip'] = $toolTip;
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
            case self::POLYLINE:
                if(!isset($this->polylines[$element_id])) throw new LeafletMaphpException('Wrong polyline ID');
                $this->polylines[$element_id]['popUp'] = $popUp;
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
            case self::POLYLINE:
                if(!isset($this->polylines[$element_id])) throw new LeafletMaphpException('Wrong polyline ID');
                $this->polylines[$element_id]['onClick'] = $onClick;
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
        if((count($this->markers) == 0) && (count($this->circles) == 0) && (count($this->polygons) == 0) && (count($this->polylines) == 0) && (count($this->geoJSONs) == 0) && ($this->lat == NULL) && ($this->lon == NULL)) {
            throw new LeafletMaphpException('No items added nor center set: Map is inviewable');
        } else {
            $drawnItems = "var drawnItems = new L.FeatureGroup([";
        }
        $scriptText = "var map = L.map('{$this->div_id}');\n";

        $tiles_layer = '';
        switch($this->tiles) {
            case self::ES_PNOA:
                $this->tilesUrl= 'http://www.ign.es/wms-inspire/pnoa-ma';
                $tiles_layer = 'OI.OrthoimageCoverage';
                $this->tilesAtt = '&copy; © <a href="https://www.ign.es/web/ign/portal/ide-area-nodo-ide-ign">Instituto Geográfico Nacional de España</a>';
                break;
            case self::ES_RASTER_IGN:
                $this->tilesUrl= 'http://www.ign.es/wms-inspire/mapa-raster';
                $tiles_layer = 'mtn_rasterizado';
                $this->tilesAtt = '&copy; © <a href="https://www.ign.es/web/ign/portal/ide-area-nodo-ide-ign">Instituto Geográfico Nacional de España</a>';
                break;
            case self::ES_IGN_BASE:
                $this->tilesUrl= 'http://www.ign.es/wms-inspire/ign-base';
                $tiles_layer = 'IGNBaseTodo';
                $this->tilesAtt = '&copy; © <a href="https://www.ign.es/web/ign/portal/ide-area-nodo-ide-ign">Instituto Geográfico Nacional de España</a>';
                break;
            case self::ES_CATASTRO:
                $this->tilesUrl= 'http://ovc.catastro.meh.es/Cartografia/WMS/ServidorWMS.aspx';
                $tiles_layer = 'Catastro';
                $this->tilesAtt = '&copy; © <a href="http://www.catastro.minhap.gob.es/esp/wms.asp">Dirección General del Catastro</a>';
                break;
            //free maps taken from list at https://wiki.openstreetmap.org/wiki/Tiles, see OSM wiki for updated information about availability and attribution
            case self::OSM:
                $this->tilesUrl = 'http://tile.openstreetmap.org/{z}/{x}/{y}.png';
                $this->tilesAtt = '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
                break;
            case self::OSM_DE:
                $this->tilesUrl = 'http://a.tile.openstreetmap.de/{z}/{x}/{y}.png';
                $this->tilesAtt = '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
                break;
            case self::OSM_FR:
                $this->tilesUrl = 'http://a.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png';
                $this->tilesAtt = '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
                break;
            case self::OSM_HUMANITARIAN:
                $this->tilesUrl = 'http://a.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
                $this->tilesAtt = '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors';
                break;
            case self::STAMEN_TONER:
                $this->tilesUrl = 'https://stamen-tiles.a.ssl.fastly.net/toner/{z}/{x}/{y}.png';
                $this->tilesAtt = 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.';
                $this->tilesMaxZoom = 16;
                break;
            case self::STAMEN_TERRAIN:
                $this->tilesUrl = 'https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.png';
                $this->tilesAtt = 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.';
                $this->tilesMaxZoom = 13;
                break;
            case self::STAMEN_WATERCOLOR:
                $this->tilesUrl = 'https://stamen-tiles.a.ssl.fastly.net/watercolor/{z}/{x}/{y}.png';
                $this->tilesAtt = 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://creativecommons.org/licenses/by-sa/3.0">CC BY SA</a>.';
                $this->tilesMaxZoom = 16;
                break;
            case self::OPNVKARTE_TRANSPORT:
                $this->tilesUrl = 'http://tile.memomaps.de/tilegen/{z}/{x}/{y}.png';
                $this->tilesAtt = 'Map <a href="https://memomaps.de/">memomaps.de</a> <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC BY SA</a>, map data <a href="http://openstreetmap.org/">Openstreetmap ODbL</a>';
                $this->tilesMaxZoom = 17;
                break;
            case self::OPEN_TOPO_MAP:
                $this->tilesUrl = 'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png';
                $this->tilesAtt = 'Kartendaten: © <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>-Mitwirkende, SRTM | Kartendarstellung: © <a href="http://opentopomap.org/">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)';
                $this->tilesMaxZoom = 17;
                break;
            case self::CUSTOM_TILES:
				if(empty($this->tilesUrl) || empty($this->tilesAtt)) {
					throw new LeafletMaphpException('Custom tiles not configured. Use method "setCustomTiles" ');
				}
                break;
            default:
                throw new LeafletMaphpException('Tileset not found');
                break;
        }

        switch($this->tiles) {
            case self::ES_PNOA: case self::ES_RASTER_IGN: case self::ES_IGN_BASE: case self::ES_CATASTRO: 
                $scriptText .= "L.tileLayer.wms('{$this->tilesUrl}', {layers: '$tiles_layer', format: 'image/png', transparent: false, continuousWorld : true,";
                break;
            case self::OSM: case self::OSM_DE: case self::OSM_FR: case self::OSM_HUMANITARIAN: case self::STAMEN_TONER: case self::STAMEN_TERRAIN: case self::STAMEN_WATERCOLOR: case self::OPNVKARTE_TRANSPORT: case self::OPEN_TOPO_MAP: case self::CUSTOM_TILES:
                $scriptText .= "L.tileLayer('{$this->tilesUrl}', {";
                break;
            default:
                throw new LeafletMaphpException('Tileset not found');
                break;
        }
        $scriptText .= " attribution: '{$this->tilesAtt}', minZoom: {$this->tilesMinZoom}, maxZoom: {$this->tilesMaxZoom} }).addTo(map);\n";
        
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

        for($i=0; $i<count($this->polylines); ++$i) {
            $polylineText = "var polyline$i = L.polyline([";
            if(isset($this->polylines[$i]['data'])) {
                foreach ($this->polylines[$i]['data'] as $coord) {
                    $polylineText .= "[{$coord[1]}, {$coord[0]}],";
                }
            }
            $polylineText = substr($polylineText, 0, -1); //remove last ','
            $polylineText .= "]";
            if(isset($this->polylines[$i]['color']) || (isset($this->polylines[$i]['onClick']))) {
                $optionsText = '';
                if(isset($this->polylines[$i]['color'])) {
                    $optionsText .= "color: '{$this->polylines[$i]['color']}'";
                }
                if(isset($this->polylines[$i]['onClick'])) {
                    if($optionsText != '') $optionsText .= ', ';
                    $optionsText .= "onClickText: '{$this->polylines[$i]['onClick']}'";
                }
                $polylineText .= ', {'.$optionsText.'}';
            }
            $polylineText .= ')';
            if(isset($this->polylines[$i]['onClick'])) $this->addOnClickFunction($this->polylines[$i], $polylineText);
            $this->addText($this->polylines[$i], $polylineText);
            $polylineText .= ".addTo(map);\n";
            $scriptText .= $polylineText;
            $drawnItems .= "polyline$i,";
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