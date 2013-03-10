/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 9.3.13
 * Time: 19:23
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
    export class BasicMap {

        public map:google.maps.Map;
        public markers:google.maps.Marker[] = [];
        public paths:google.maps.Polyline[] = [];

        private loaded = false;

        constructor(private mapElement:Element, private options) {
            $(window).load(()=> {
                this.initialize()
            });

        }

        public initialize() {
            var mapOptions = {
                zoom: this.options.zoom,
                center: new google.maps.LatLng(this.options.center.lat, this.options.center.lng),
                mapTypeId: 'mystyle',
                streetViewControl: false,
                mapTypeControlOptions: {
                    mapTypeIds: ['mystyle', google.maps.MapTypeId.SATELLITE]
                }
            }

            this.map = new google.maps.Map(this.mapElement, mapOptions);
            this.map.setTilt(0);
            this.map.mapTypes.set('mystyle', new google.maps.StyledMapType([
                {
                    "featureType": "poi.school",
                    "elementType": "labels",
                    "stylers": [
                        { "visibility": "off" }
                    ]
                },{
                    "featureType": "landscape.man_made",
                    "elementType": "labels",
                    "stylers": [
                        { "visibility": "off" }
                    ]
                }
            ], {name:"Základní",maxZoom:22}));

            this.loaded = true;
            if(this.options.points.length > 0) {
                this.loadMarkers(this.options.points);
            }
            if(this.options.customLayers.length > 0) {
                this.loadCustomLayers(this.options.customLayers);
            }
            if(this.options.paths.length > 0) {
                this.loadPaths(this.options.paths);
            }
            if(this.options.legend) {
                this.map.controls[google.maps.ControlPosition.RIGHT_TOP].push(
                    this.options.legend
                );
            }

        }

        public loadCustomLayers(definition:array) {
            $.each(definition, (index, value) => {
                if(!value) return;
                this.map.overlayMapTypes.push(new google.maps.ImageMapType({
                    tileSize: new google.maps.Size(256,256),
                    getTileUrl: (coord:google.maps.Point, zoom:number) => {
                        if(zoom > 17)
                            return "/" + value + "/" + zoom + "/" +coord.x + "/" + (Math.pow(2, zoom) - coord.y -1) + ".png";
                    },
                    maxZoom: 22,
                    minZoom: 19
                }));
            })
        }


        private loadMarkers(definition:array) {
            $.each(definition, (index, item) => {
                this.markers.push(new google.maps.Marker({
                    map:this.map,
                    draggable: (!item.draggable?false:item.draggable),
                    position: new google.maps.LatLng(item.position['lat'],item.position['lng']),
                    icon:item.icon,
                    title:item.title
                }));
            });

        }

        private loadPaths(definition) {
            $.each(definition, (index, item) => {
                var path = new google.maps.Polyline(this.options.pathOptions);
                path.setMap(this.map);
                path.getPath().push(new google.maps.LatLng(item.start.lat,item.start.lng));
                path.getPath().push(new google.maps.LatLng(item.end.lat,item.end.lng));
                this.paths.push(path);
            })
        }



    }

}