/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 9.3.13
 * Time: 22:48
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
    export class GeoCoder {
        constructor(private mapping:BasicMap, private button, private source, private destination) {
            $(window).load(() => {
                $("#"+this.button).click((event) => {this.buttonClicked(event)});
                google.maps.event.addListener(this.mapping.map, 'click', (event) => {
                    this.setMarkerPosition(event.latLng)
                });
                if(this.getMarker()) {
                    this.setCallback(this.getMarker());
                }
            });
        }

        public buttonClicked(event) {
            var address = $("#"+this.source).val().trim();
            if(address == "") {
                alert("Zadejte prosím adresu budovy.");
                $("#" + this.source).focus();
                return;
            }
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode(
                {address: address, region: 'cz'},
                (result,status) => {this.onResponse(result, status)}
            )

        }

        private getMarker() {
            return this.mapping.markers.length > 0 ? this.mapping.markers[0]: undefined;
        }

        private setMarkerPosition(position) {
            if(!this.getMarker()) {
                this.mapping.markers.push(new google.maps.Marker({
                    map: this.mapping.map,
                    position: position,
                    draggable: true
                }));

                this.setCallback(this.getMarker());
            }
            else {
                this.getMarker().setPosition(position);
            }
            this.updateField(position);
        }

        private setCallback(marker:google.maps.Marker) {
            google.maps.event.addListener(marker, 'dragend', (event) => {this.updateField(event.latLng)});
        }

        private onResponse(result, status) {
            if(status == google.maps.GeocoderStatus.OK) {
                this.mapping.map.setCenter(result[0].geometry.location);
                this.mapping.map.setZoom(16);
                this.setMarkerPosition(result[0].geometry.location)
            }
            else if(status == "ZERO_RESULTS") {
                alert("Tuto adresu se bohužel nepodařilo najít. Umístěte značku na správnou pozici ručně.");
            }
            else {
                console.log('ERROR: '+status);
            }
        }



        private updateField(position:google.maps.LatLng) {
            $("#"+this.destination).val(position.lat()+","+position.lng());
        }
    }
}
