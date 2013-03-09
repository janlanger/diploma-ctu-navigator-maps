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
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }

            this.map = new google.maps.Map(this.mapElement, mapOptions);
            this.loaded = true;
            if(this.options.points.length > 0) {
                this.loadMarkers(this.options.points);
            }
            if(this.options.customLayers.length > 0) {
                this.loadCustomLayers(this.options.customLayers);
            }

        }

        public loadCustomLayers(definition:array) {
            $.each(definition, (index, value) => {
                this.map.overlayMapTypes.push(new google.maps.ImageMapType({
                    tileSize: new google.maps.Size(256,256),
                    getTileUrl: (coord:google.maps.Point, zoom:number) => {
                        return "/" + value + "/" + zoom + "/" +coord.x + "/" + (Math.pow(2, zoom) - coord.y -1) + ".png";
                    }
                }));
            })
        }


        private loadMarkers(definition:array) {
            $.each(definition, (index, item) => {
                this.markers.push(new google.maps.Marker({
                    map:this.map,
                    draggable: (!item.draggable?false:item.draggable),
                    position: new google.maps.LatLng(item.position['lat'],item.position['lng'])
                }));
            });

        }



    }

}