/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 14.3.13
 * Time: 10:28
 * To change this template use File | Settings | File Templates.
 */


module Mapping {
    export class MarkerEditor extends Mapping.BasicMap {

        private eventHandler:Mapping.Events;

        //      private editorState:string;
        private additionState:string;
        private activeMarkerType:string = 'intersection';

        /*   get State():string {
         return this.editorState;
         }*/
        get AdditionState():string {
            return this.additionState;
        }

        get ActiveMarkerType():string {
            return this.activeMarkerType;
        }

        /*   set State(newState:string) {
         this.eventHandler.onEditorStateChange(newState);
         this.editorState = newState;
         }*/
        set AdditionState(newState:string) {
            this.eventHandler.onAdditionStateChange(newState);
            this.additionState = newState;
        }

        set ActiveMarkerType(newType:string) {
            this.eventHandler.onMarkerTypeChange(newType);
            this.activeMarkerType = newType;
        }

        constructor(private mapElement:Element, private options) {
            super(mapElement, options);
        }

        public initialize() {

            super.initialize();
            //parse definition field and ad its information to options
            this.map.setOptions({draggableCursor: 'crosshair'});

            var defintion = $("#" + this.options.definitionElement).val();
            if (defintion) {
                this.parseDefinition(defintion);
            }
            this.eventHandler = new Mapping.Events(this, this.options.temporaryPathOptions);

            this.eventHandler.registerMapEvents();
            this.eventHandler.registerUIEvents();

            $.each(this.markers, (index, item) => {
                this.eventHandler.registerMarkerEvents(item);
            });

            $.each(this.paths, (index, item) => {
                this.eventHandler.registerPathEvents(item);
            });

            $("#" + this.options.definitionElement).hide();

            this.AdditionState = Mapping.Events.ADD_INCATIVE;
            //  this.State = Mapping.Events.STATE_ADD;
            this.ActiveMarkerType = 'intersection';
            $("#" + this.options.submitElement).click((event) => {
                this.eventHandler.submitHandler(event, this.options.definitionElement)
            });
        }

        private parseDefinition(definition) {
            var d = JSON.parse(definition);

            var nodes = d.nodes;
            var paths = d.paths;
            $.each(paths, (index, path) => {
                if (path.startNode == null || path.endNode == null) {
                    return;
                }
                var n = [nodes[path.startNode], nodes[path.endNode]];
                var path = [];
                for (var i = 0; i < n.length; i++) {
                    if (!(n[i].position instanceof google.maps.LatLng)) {
                        var position = n[i].position.split(",");
                        var p = new google.maps.LatLng(position[0], position[1]);
                    } else {
                        var p = n[i].position;
                    }
                    if (this.getMarkerInPosition(p) == null) {
                        var options = n[i];
                        this.markers.push(this.createMarker({
                            position: p,
                            draggable: true,
                            icon: this.getMarkerIcon(options.type)
                        }, options));
                    }
                    path.push(p);
                }
                this.paths.push(this.createPath(path[0], path[1]));
            })
        }

        public addPath(start, end) {
            var path = super.createPath(start, end);
            this.paths.push(path);
            this.eventHandler.registerPathEvents(path);
        }

        public addMarker(position:google.maps.LatLng) {
            var marker = this.createMarker({
                position: position,
                draggable: true,
                icon: this.getMarkerIcon(this.ActiveMarkerType)
            }, {type: this.ActiveMarkerType});
            this.markers.push(marker);
            this.eventHandler.registerMarkerEvents(marker);
        }

        private createMarker(markerOptions, additional = {}) {
            var marker = super.createMarker(markerOptions);
            marker.appOptions = additional;
            return marker;
        }

        private getMarkerIcon(type:string):google.maps.MarkerImage {
            var url = this.options.iconsBasePath + "/" + type + ".png";
            return {url: url, anchor: new google.maps.Point(this.options.markerTypes[type].anchor[0], this.options.markerTypes[type].anchor[1])};
        }

        public openMarkerInfoWindow(marker:google.maps.Marker) {
            var window = new google.maps.InfoWindow();
            var html = $("#innerForm form").clone();
            //infobox size
            $("div[id^=form-]", html).hide();
            $("div[id^=form-]:lt(3)", html).show();
            //events registration
            var typeSelect = $("select[name=type]", html);
            typeSelect.change(function (event) {
                //hide everything
                $("div[id^=form-]", html).hide();
                $("#form-type", html).show();
                switch (this.value) {
                    case 'elevator':
                        $("#form-fromFloor", html).show();
                    case 'stairs':
                        $("#form-toFloor", html).show();
                        break;
                    case 'passage':
                        $("#form-toFloor", html).show();
                        $("#form-toBuilding", html).show();
                        break;
                    case 'lecture':
                    case 'auditorium':
                    case 'office':
                    case 'study':
                        $("#form-room", html).show();
                    case 'cafeteria':
                    case 'entrance':
                    case 'other':
                    case 'default':
                        $("#form-name", html).show();
                        break;
                }
            });
            var room = $("input[name=room]", html);
            room.attr('autocomplete', 'off');
            room.typeahead({
                source: (query, process) => {
                    return $.get("http://navigator.jamrtal.cz/api/1/kos/rooms",
                        {query: "code==*" + this.options.roomPrefix + query + "*"},
                        (data)=> {
                            return process(data.rooms);
                        }
                    )
                },
                updater: (item) => {
                    return item.replace(this.options.roomPrefix, "");
                }
            });
            $("input[name=save]", html).click(() => {
                if (!marker.appOptions) {
                    marker.appOptions = {};
                }
                marker.appOptions.name = $("input[name='name']", html).val();
                marker.appOptions.room = this.options.roomPrefix + $("input[name='room']", html).val();
                marker.appOptions.fromFloor = $("input[name='fromFloor']", html).val();
                marker.appOptions.toFloor = $("input[name='toFloor']", html).val();
                marker.appOptions.toBuilding = $("select[name='toBuilding']", html).val();
                marker.appOptions.type = $("select[name=type]", html).val();

                marker.setIcon(this.getMarkerIcon(marker.appOptions.type));
                window.close()
            });
            $("input[name=delete]", html).click((event) => {
                var index = this.markers.indexOf(marker);
                marker.setMap(null);
                var position = marker.getPosition();

                $.each(this.paths, (index, line:google.maps.Polyline) => {
                    if (!line) return;
                    var path = line.getPath();
                    if (path.getAt(0).equals(position) || path.getAt(1).equals(position)) {
                        line.setPath([]);
                        delete this.paths[index];
                    }
                });

                delete this.markers[index];
                window.close();
            });
            html.removeAttr('id');
            html.attr('style', "");

            if (marker.appOptions != null) {
                $("input[name='name']", html).val(marker.appOptions.name);
                $("input[name='room']", html).val((marker.appOptions.room ? marker.appOptions.room.replace(this.options.roomPrefix, "") : ""));
                $("input[name='fromFloor']", html).val(marker.appOptions.fromFloor);
                $("input[name='toFloor']", html).val(marker.appOptions.toFloor);
                $("select[name='toBuilding']", html).val(marker.appOptions.toBuilding);
                $("select[name=type]", html).val(marker.appOptions.type);
            }
            window.setContent(html[0]);
            google.maps.event.addListener(window, 'domready', function () {
                typeSelect.trigger('change');
            });
            window.open(this.map, marker);
        }

        public openPathInfoWindow(position:google.maps.LatLng, line:google.maps.Polyline) {
            var infoWindow = new google.maps.InfoWindow();

            var content = $("<input>");
            content.val("Odstranit cestu");
            content.attr("type", "button");
            content.attr("class", "btn btn-small btn-danger");
            content.click((event) => {
                var index = this.paths.indexOf(line);
                line.setPath([]);
                delete this.paths[index];
                infoWindow.close();
            });
            var x = $("<div></div>");
            x.append(content);
            infoWindow.setContent(x[0]);
            infoWindow.setPosition(position);
            infoWindow.open(this.map);
        }

    }
}