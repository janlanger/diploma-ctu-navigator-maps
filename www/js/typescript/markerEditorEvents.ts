/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 14.3.13
 * Time: 22:06
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
    export class Events {

        public static STATE_ADD = 'add';
        public static STATE_DETAIL = 'detail';

        public static ADD_ACTIVE = 'con';
        public static ADD_INCATIVE = 'new';

        private tempLine:google.maps.Polyline = null;

        constructor(private editor:Mapping.MarkerEditor, private temporaryLineOptions) {
        }

        public registerMapEvents() {
            google.maps.event.addListener(this.editor.map, 'click', this.mapClicked.bind(this));
            google.maps.event.addListener(this.editor.map, 'rightclick', this.mapRightClicked.bind(this));
            google.maps.event.addListener(this.editor.map, 'mousemove', this.mapMouseMoved.bind(this));
        }

        public registerMarkerEvents(marker:google.maps.Marker) {
            var _this = this;
            google.maps.event.addListener(marker, 'click', function(event) {_this.markerClicked(event, this)});
            google.maps.event.addListener(marker, 'dragstart', function(event) {_this.markerDragStart(event, this)});
            google.maps.event.addListener(marker, 'dragend', function(event) {_this.markerDragEnd(event, this)});
        }

        public registerPathEvents(path:google.maps.Polyline) {
            var _this = this;
            google.maps.event.addListener(path,'click', function (event) {
                return _this.pathClicked(event, this);
            });
        }

        public registerUIEvents() {
            $("#switcher-add").click(()=> {
                this.editor.State = Mapping.Events.STATE_ADD;
            });
            $("#switcher-detail").click(()=> {
                this.editor.State = Mapping.Events.STATE_DETAIL;
            });
            var _this = this;
            $("a[id^='marker-']").click(function() {
                _this.editor.ActiveMarkerType = this.id.substring(7);
            });
        }

        // --- map events

        private mapClicked(event:google.maps.MouseEvent) {
            if(this.editor.State == Mapping.Events.STATE_ADD) {
                if(this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                    this.editor.addMarker(event.latLng);
                    this.editor.addPath(this.tempLine.getPath().getAt(0), event.latLng);
                    this.disableTemporaryLine();
                    this.enableTemporaryLine(event.latLng);
                }
                if(this.editor.AdditionState == Mapping.Events.ADD_INCATIVE) {
                    this.editor.addMarker(event.latLng);
                    this.enableTemporaryLine(event.latLng);
                    this.editor.AdditionState = Mapping.Events.ADD_ACTIVE;
                }

            }
        }

        private mapRightClicked() {
            if(this.editor.State == Mapping.Events.STATE_ADD &&
                this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                this.disableTemporaryLine();
                this.editor.AdditionState = Mapping.Events.ADD_INCATIVE;
            }
        }

        private mapMouseMoved(event:google.maps.MouseEvent) {
            if(this.editor.State == Mapping.Events.STATE_ADD &&
                this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                this.moveTemporaryLine(event.latLng);
            }
        }

        // --- marker events

        public markerClicked(event:google.maps.MouseEvent, marker:google.maps.Marker) {
            if(this.editor.State == Mapping.Events.STATE_ADD) {
                if(this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                    this.editor.addPath(this.tempLine.getPath().getAt(0), event.latLng);
                    this.disableTemporaryLine();
                }
                else{
                    this.editor.AdditionState = Mapping.Events.ADD_ACTIVE;
                }
                this.enableTemporaryLine(event.latLng);
            }
            if(this.editor.State == Mapping.Events.STATE_DETAIL) {
                this.editor.openMarkerInfoWindow(marker);
            }
        }

        private previousPosition:google.maps.LatLng = null;
        public markerDragStart(event:google.maps.MouseEvent, marker:google.maps.Marker) {
            if(this.editor.State == Mapping.Events.STATE_DETAIL) {
                this.previousPosition = marker.getPosition();
            }
        }

        public markerDragEnd(event:google.maps.MouseEvent, marker:google.maps.Marker) {
            if(this.editor.State == Mapping.Events.STATE_DETAIL) {
                if(this.previousPosition != undefined) {
                    for(var i=0; i< this.editor.paths.length; i++) {
                        var line = this.editor.paths[i];
                        if(!line) continue;
                        var path = line.getPath();

                        if(path.getAt(0).equals(this.previousPosition)) {
                            path.setAt(0, marker.getPosition());
                        }
                        if(path.getAt(1).equals(this.previousPosition)) {
                            path.setAt(1, marker.getPosition());
                        }

                    }
                }
            }
        }

        // --- line clicked

        public pathClicked(event:google.maps.MouseEvent, caller:google.maps.Polyline) {
            if(this.editor.State == Mapping.Events.STATE_ADD) {
                this.editor.addMarker(event.latLng);
                if(this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                    this.editor.addPath(this.tempLine.getPath().getAt(0), event.latLng);
                    this.disableTemporaryLine();
                }
                else {
                    this.editor.AdditionState = Mapping.Events.ADD_ACTIVE;
                }
                this.editor.addPath(event.latLng, caller.getPath().getAt(1));
                caller.getPath().setAt(1, event.latLng);

                this.enableTemporaryLine(event.latLng);
            }

            if(this.editor.State == Mapping.Events.STATE_DETAIL) {
                this.editor.openPathInfoWindow(event.latLng, caller);
            }
        }

        // --- UI events

        public onEditorStateChange(newState) {
            this.editor.AdditionState = Mapping.Events.ADD_INCATIVE;

            $("#switcher-add").removeClass("btn-primary");
            $("#switcher-detail").removeClass("btn-primary");
            $("#switcher-"+newState).addClass("btn-primary");

            $("div[id^='toolbar-']").hide();
            $("#toolbar-"+newState).show();

            for(var i=0; i<this.editor.markers.length; i++) {
                if(!this.editor.markers[i]) continue;
                this.editor.markers[i].setDraggable(newState == Mapping.Events.STATE_DETAIL);
            }
            if(newState == Mapping.Events.STATE_ADD) {
                this.editor.map.setOptions({draggableCursor:'crosshair'});
            }
            else {
                this.editor.map.setOptions({draggableCursor:'hand'});
            }
        }

        public onAdditionStateChange(newState) {
            if(newState == Mapping.Events.ADD_INCATIVE) {
                this.disableTemporaryLine();
            }
        }

        public onMarkerTypeChange(newType) {
            $("a[id^='marker-']").removeClass('btn-primary');
            $("#marker-"+newType).addClass("btn-primary");
        }

        public submitHandler(event, textElement) {
            var field = $("#"+textElement);
            var markerFields = [];
            for(var i=0; i<this.editor.markers.length; i++) {
                if(this.editor.markers[i] == null) continue;
                var x = {
                    position: this.editor.markers[i].getPosition().lat() + "," + this.editor.markers[i].getPosition().lng()
                };

                if(this.editor.markers[i].appOptions != null) {
                    this.editor.markers[i].appOptions.position = undefined;
                    $.extend(x, this.editor.markers[i].appOptions,x);
                }
                markerFields[i] = x;
            }
            var lineFields = [];
            for(var i=0; i<this.editor.paths.length; i++) {
                if(!this.editor.paths[i]) continue;
                lineFields.push({
                    startNode: this.editor.markers.indexOf(this.editor.getMarkerInPosition(this.editor.paths[i].getPath().getAt(0))),
                    endNode: this.editor.markers.indexOf(this.editor.getMarkerInPosition(this.editor.paths[i].getPath().getAt(1))),
                    length: google.maps.geometry.spherical.computeLength(this.editor.paths[i].getPath())
                });
            }
            field.val(JSON.stringify({ nodes: markerFields, paths: lineFields}));
        }

        // --- temporary line handle

        private enableTemporaryLine(position:google.maps.LatLng) {
            this.tempLine = new google.maps.Polyline(this.temporaryLineOptions);
            this.tempLine.setMap(this.editor.map);
            this.tempLine.getPath().push(position);
        }

        private disableTemporaryLine() {
            if(this.tempLine) {
                this.tempLine.setPath([]);
                this.tempLine = null;
            }
        }

        private moveTemporaryLine(position) {
            if(this.tempLine != undefined) {
                if(this.tempLine.getPath().length == 1) {
                    this.tempLine.getPath().push(position)
                }
                else {
                    this.tempLine.getPath().setAt(1, position);
                }
            }
        }


    }
}
