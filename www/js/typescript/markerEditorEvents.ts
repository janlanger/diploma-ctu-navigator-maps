/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 14.3.13
 * Time: 22:06
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
    export class Events {

        /*        public static STATE_ADD = 'add';
         public static STATE_DETAIL = 'detail';*/

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
            google.maps.event.addListener(marker, 'click', function (event) {
                _this.markerClicked(event, this)
            });
            google.maps.event.addListener(marker, 'rightclick', function (event) {
                _this.markerRightClicked(event, this)
            });
            google.maps.event.addListener(marker, 'dragstart', function (event) {
                _this.markerDragStart(event, this)
            });
            google.maps.event.addListener(marker, 'dragend', function (event) {
                _this.markerDragEnd(event, this)
            });
        }

        public registerPathEvents(path:google.maps.Polyline) {
            var _this = this;
            google.maps.event.addListener(path, 'click', function (event) {
                return _this.pathClicked(event, this);
            });
            google.maps.event.addListener(path, 'rightclick', function (event) {
                return _this.pathRightClicked(event, this);
            })
        }

        public registerUIEvents() {
            var _this = this;
            $("a[id^='marker-']").click(function (event) {
                event.preventDefault();
                _this.editor.ActiveMarkerType = this.id.substring(7);
            });

            $(document).keyup((e) => {
                if (e.keyCode == 27) {
                    this.mapRightClicked();
                }
            });
        }

        // --- map events

        private mapClicked(event:google.maps.MouseEvent) {
            //  if(this.editor.State == Mapping.Events.STATE_ADD) {
            if (this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                this.editor.addMarker(event.latLng);
                this.editor.addPath(this.tempLine.getPath().getAt(0), event.latLng);
                this.disableTemporaryLine();
                this.enableTemporaryLine(event.latLng);
            }
            if (this.editor.AdditionState == Mapping.Events.ADD_INCATIVE) {
                this.editor.addMarker(event.latLng);
                this.enableTemporaryLine(event.latLng);
                this.editor.AdditionState = Mapping.Events.ADD_ACTIVE;
            }

            //   }
        }

        private mapRightClicked() {
            //    if(this.editor.State == Mapping.Events.STATE_ADD &&
            //       this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
            this.disableTemporaryLine();
            this.editor.AdditionState = Mapping.Events.ADD_INCATIVE;
            //   }
        }

        private mapMouseMoved(event:google.maps.MouseEvent) {
            //  if(this.editor.State == Mapping.Events.STATE_ADD &&
            //      this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
            this.moveTemporaryLine(event.latLng);
            //  }
        }

        // --- marker events

        public markerClicked(event:google.maps.MouseEvent, marker:google.maps.Marker) {
            //   if(this.editor.State == Mapping.Events.STATE_ADD) {
            if (this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
                this.editor.addPath(this.tempLine.getPath().getAt(0), event.latLng);
                this.disableTemporaryLine();
            }
            else {
                this.editor.AdditionState = Mapping.Events.ADD_ACTIVE;
            }
            this.enableTemporaryLine(event.latLng);
            //   }
        }

        public markerRightClicked(event, marker) {
            //if(this.editor.State == Mapping.Events.STATE_DETAIL) {
            this.editor.AdditionState = Mapping.Events.ADD_INCATIVE;
            this.editor.openMarkerInfoWindow(marker);
            //}
        }

        private previousPosition:google.maps.LatLng = null;

        public markerDragStart(event:google.maps.MouseEvent, marker:google.maps.Marker) {
            this.editor.AdditionState = Mapping.Events.ADD_INCATIVE;
            this.previousPosition = marker.getPosition();
        }

        public markerDragEnd(event:google.maps.MouseEvent, marker:google.maps.Marker) {
            if (this.previousPosition != undefined) {
                for (var i = 0; i < this.editor.paths.length; i++) {
                    var line = this.editor.paths[i];
                    if (!line) continue;
                    var path = line.getPath();
                    if(!path || path.length < 2) continue;
                    if (path.getAt(0).equals(this.previousPosition)) {
                        path.setAt(0, marker.getPosition());
                    }
                    if (path.getAt(1).equals(this.previousPosition)) {
                        path.setAt(1, marker.getPosition());
                    }

                }
            }
        }

        // --- line clicked

        public pathClicked(event:google.maps.MouseEvent, caller:google.maps.Polyline) {
            //  if(this.editor.State == Mapping.Events.STATE_ADD) {
            this.editor.addMarker(event.latLng);
            if (this.editor.AdditionState == Mapping.Events.ADD_ACTIVE) {
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

        public pathRightClicked(event:google.maps.MouseEvent, caller:google.maps.Polyline) {
            this.editor.AdditionState = Mapping.Events.ADD_INCATIVE;
            this.editor.openPathInfoWindow(event.latLng, caller);
        }


        public onAdditionStateChange(newState) {
            if (newState == Mapping.Events.ADD_INCATIVE) {
                this.disableTemporaryLine();
            }
        }

        public onMarkerTypeChange(newType) {
            $("a[id^='marker-']").removeClass('btn-primary');
            $("#marker-" + newType).addClass("btn-primary");
        }

        public newSubmitHandler(event, textElement) {
            var addedNodes = [];
            var changedNodes = [];
            var deletedNodes = [];
            var checked = [];
            for (var key in this.editor.markers) {
                var original = this.editor.markersOriginals[key];
                var item = this.editor.markers[key];
                item.appOptions.gps = item.getPosition();
                if (!original && item.getMap() != null) {
                    //added
                    addedNodes[key] = item.appOptions;
                    addedNodes[key].position = item.getPosition().lat() + "," + item.getPosition().lng();
                    addedNodes[key].gps = undefined;
                }

                if (original && item.getMap() != null && !this.optionsEquals(original, item.appOptions)) {
                    changedNodes[item.appOptions.propertyId] = $.extend({}, item.appOptions);
                    changedNodes[item.appOptions.propertyId].position = item.getPosition().lat() + "," + item.getPosition().lng();
                    changedNodes[item.appOptions.propertyId].gps = undefined;
                }
                checked[key] = true;
            }

            for (var key in this.editor.markersOriginals) {
                // deleted directly
                if (checked[key]) continue;
                deletedNodes.push(this.editor.markersOriginals[key].propertyId);
            }

            var addedPaths = [];
            var deletedPaths = [];

            var checked = [];

            for (var key in this.editor.pathOriginals) {

                var original = this.editor.pathOriginals[key];
                var start = this.editor.findNodeWithId(original.start);
                var end = this.editor.findNodeWithId(original.end);

                checked[key] = true;

                if (!start || !end) {
                    //deleted
                    deletedPaths[key] = original;
                    continue;
                }
                var path = this.editor.getPathBetween(start.getPosition(), end.getPosition());
                if (!path) {
                    deletedPaths[key] = original;
                    if (this.editor.paths[key] && this.editor.paths[key].getPath().length > 0) {
                        // path was changed, not deleted - delete and add
                        checked[key] = false;
                    }
                }
            }

            for (var key in this.editor.paths) {
                if (checked[key]) continue;
                if (!this.editor.paths[key] || this.editor.paths[key].getPath().length < 2) continue;
                var start = this.editor.markers[this.editor.getMarkerIndexInPosition(this.editor.paths[key].getPath().getAt(0))];
                var end = this.editor.markers[this.editor.getMarkerIndexInPosition(this.editor.paths[key].getPath().getAt(1))];
                addedPaths[key] = {
                    start: $.extend(start.appOptions, {id: this.editor.markers.indexOf(start), position: start.getPosition().lat() + "," + start.getPosition().lng()}),
                    end: $.extend(end.appOptions, {id: this.editor.markers.indexOf(end), position: end.getPosition().lat() + "," + end.getPosition().lng()})
                };
            }

            $("#" + textElement).val(JSON.stringify({
                nodes: {
                    added: addedNodes,
                    changed: changedNodes,
                    deleted: deletedNodes
                },
                paths: {
                    added: addedPaths,
                    deleted: deletedPaths
                }
            }));
        }

        private optionsEquals(obj1, obj2) {
            for (var i in obj1) {
                if (obj1.hasOwnProperty(i)) {
                    if (!obj2.hasOwnProperty(i)) return false;
                    if (i != "gps" && obj1[i] != obj2[i]) return false;
                    if (i == "gps" && obj1[i] != null && obj1[i].equals != undefined && !obj1[i].equals(obj2[i])) return false;
                }
            }
            for (var i in obj2) {
                if (obj2.hasOwnProperty(i)) {
                    if (!obj1.hasOwnProperty(i)) return false;
                    if (i != "gps" && obj1[i] != obj2[i]) return false;
                    if (i == "gps" && obj2[i] != null && obj2[i].equals != undefined && !obj2[i].equals(obj1[i])) return false;
                }
            }
            return true;
        }

        // --- temporary line handle

        private enableTemporaryLine(position:google.maps.LatLng) {
            this.tempLine = new google.maps.Polyline(this.temporaryLineOptions);
            this.tempLine.setMap(this.editor.map);
            this.tempLine.getPath().push(position);
        }

        private disableTemporaryLine() {
            if (this.tempLine) {
                this.tempLine.setPath([]);
                this.tempLine = null;
            }
        }

        private moveTemporaryLine(position) {
            if (this.tempLine != undefined) {
                if (this.tempLine.getPath().length == 1) {
                    this.tempLine.getPath().push(position)
                }
                else {
                    this.tempLine.getPath().setAt(1, position);
                }
            }
        }

        //---------- marker Window events

        public onTypeChange(value, html) {
            //hide everything
            $("div[id^=form-]", html).hide();
            $("#form-type", html).show();
            switch (value) {
                case 'elevator':
                case 'passage':
                case 'stairs':
                    $("#form-other", html).show();
                    break;
                case 'lecture':
                case 'auditorium':
                case 'office':
                case 'study':
                    $("#form-room", html).show();
                case 'cafeteria':
                case 'entrance':
                case 'other':
                case 'restriction':
                case 'default':
                    $("#form-name", html).show();
                    break;
            }
        }

        public onMarkerWindowSave(event, marker:google.maps.Marker, roomPrefix:string, html, otherNodeTemp) {
            if (!marker.appOptions) {
                marker.appOptions = {};
            }
            marker.appOptions.name = $("input[name='name']", html).val();
            var room = $("input[name='room']", html).val().trim();
            if(room != "") {
                marker.appOptions.room = roomPrefix + $("input[name='room']", html).val();
            } else {
                marker.appOptions.room = "";
            }
          /*  marker.appOptions.fromFloor = $("input[name='fromFloor']", html).val();
            marker.appOptions.toFloor = $("input[name='toFloor']", html).val();
            marker.appOptions.toBuilding = $("select[name='toBuilding']", html).val();*/
            marker.appOptions.type = $("select[name=type]", html).val();


            marker.setIcon(this.editor.getMarkerIcon(marker.appOptions.type));
        }

        public onOtherNodeSelection(event, destination:string, destinationData:string, existing, marker:google.maps.Marker, type, callback) {
            event.preventDefault();

            var data = {coords: marker.getPosition().lat() + "," + marker.getPosition().lng()};
            var destProperty;
            if(existing) {
                data.floor = existing.destinationFloor.id;
                data.building = existing.destinationBuilding.id;
                destProperty = existing.destinationNode;
            }
            var modal = new Mapping.ModalMap(this.editor);
            modal.load(destination, destinationData, data, type, callback, destProperty);
        }

        public onOtherNodeSelected(original:google.maps.Marker, html, returned) {
            if(returned.deleted) {
                return returned;
            }
            if(!returned.marker) return;
            return {
                destinationNode: returned.marker.appOptions.propertyId,
                destinationFloor: returned.floorInfo,
                destinationBuilding: returned.buildingInfo
            }
        }





    }
}
