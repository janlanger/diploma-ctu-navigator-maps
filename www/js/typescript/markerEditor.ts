/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 14.3.13
 * Time: 10:28
 * To change this template use File | Settings | File Templates.
 */


module Mapping {
    export class MarkerEditor extends Mapping.BasicMap {

        public eventHandler:Mapping.Events;

        //      private editorState:string;
        private additionState:string;
        private activeMarkerType:string = 'intersection';

        public markersOriginals = [];
        public pathOriginals = [];

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
            this.initOriginals();
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
                //event.preventDefault();
                this.eventHandler.newSubmitHandler(event, this.options.definitionElement)
                //return false;
            });

            if (this.options.customControls) {


                /*$.each(this.options.customControls, (index, item) => {
                    index = this.map.controls[item.position].push(item.element);
                });*/
                if(this.options.customControls.length == 1) {
                    var item = this.options.customControls[0];
                    var prevPosition = item.position;
                    google.maps.event.addListenerOnce(this.map, 'idle', function() {
                        $(item.element).removeClass('hide');
                    })
                    var index = this.map.controls[item.position].push(item.element) -1;
                    var _this = this;
                    $(this.options.positionSelect).change(function(event) {
                        _this.map.controls[prevPosition].removeAt(index);
                        var newPosition;
                        switch($(this).val()) {
                            case 'top':
                                newPosition = google.maps.ControlPosition.TOP_CENTER;
                                $(item.element).removeClass('btn-group-vertical');
                                break;
                            case 'bottom':
                                newPosition = google.maps.ControlPosition.BOTTOM_CENTER;
                                $(item.element).removeClass('btn-group-vertical');
                                break;
                            default:
                                newPosition = google.maps.ControlPosition.RIGHT_CENTER;
                                $(item.element).addClass('btn-group-vertical');
                                break;
                        }

                        index = _this.map.controls[newPosition].push(item.element) - 1;
                        prevPosition = newPosition;
                    });
                    $(this.options.positionSelect).trigger('change');
                }
            }

            if (this.options.resizableBox) {
                var center;
                var options = {
                    start: () => {
                        center = this.map.getCenter();
                    },
                    stop: () => {

                        google.maps.event.trigger(this.map, "resize");
                        if (center) {
                            this.map.setCenter(center);
                        }
                    },
                    handles: "e,w,s,se,sw",
                    resize: function (event, ui) {
                        $(this).css({
                            'left': parseInt(ui.position.left, 10) + ((ui.originalSize.width - ui.size.width)) / 2
                        });
                    }
                };

                $(this.options.resizableBox.element).resizable($.extend(options, this.options.resizableBox.options));
            }
        }

        private initOriginals() {
            this.markersOriginals = [];
            for (var key in this.markers) {
                var item = this.markers[key];

                this.markersOriginals[key] = $.extend({}, item.appOptions);
                this.markersOriginals[key].gps = item.getPosition();
            }
            this.pathOriginals = [];
            for (var key in this.paths) {
                var item = this.paths[key];
                var path = item.getPath();
                var s = this.getMarkerInPosition(path.getAt(0));
                var e = this.getMarkerInPosition(path.getAt(1));
                this.pathOriginals[key] = {
                    start: (s ? s.appOptions.propertyId : null),
                    end: (e ? e.appOptions.propertyId : null)
                };
            }
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

        public createMarker(markerOptions, additional = {}) {
            var marker = super.createMarker(markerOptions);
            marker.appOptions = additional;
            return marker;
        }

        public getMarkerIcon(type:string):google.maps.MarkerImage {
            var url = this.options.iconsBasePath + "/" + type + ".png";
            return {url: url, anchor: new google.maps.Point(this.options.markerTypes[type].anchor[0], this.options.markerTypes[type].anchor[1])};
        }

        public readableNodeType(index) {
            if (this.options.markerTypes[index]) {
                return this.options.markerTypes[index].legend;
            }
            return index;
        }


        public openMarkerInfoWindow(marker:google.maps.Marker) {
            var window = new google.maps.InfoWindow();
            var content = this.createMarkerForm($("#innerForm form"), marker, window);

            window.setContent(content);
            google.maps.event.addListener(window, 'domready', function () {
                $("select[name=type]", content).trigger('change');
            });
            window.open(this.map, marker);
        }

        private createMarkerForm(element, marker:google.maps.Marker, window:google.maps.InfoWindow) {
            var html = element.clone(true, true);
            var _this = this;

            //infobox size - we have to show only 3 element to make gmaps infobox correctly sized
            $("div[id^=form-]", html).hide();
            $("div[id^=form-]:lt(4)", html).show();


            //events registration
            var typeSelect = $("select[name=type]", html);
            typeSelect.change(function(event) {
                _this.eventHandler.onTypeChange(this.value, html);
            });

            var otherNode = $("#form-other a", html).click((event)  => {
                this.eventHandler.onOtherNodeSelection(event, this.options.markerSelectorAction, marker, typeSelect.val(),
                    (selected) => {
                        var other = this.eventHandler.onOtherNodeSelected(marker, html, selected);
                        if(!$.isEmptyObject(other)) {
                            var info = other;
                            marker.appOptions.x = other;
                            $("#form-other div", html).text("Cíl: bod #"+ info.propertyId + ", patro "+info.floor.name + (typeSelect.val() == "passage"?" budova "+info.building.name:""));
                        }
                    }
            )});

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
                this.eventHandler.onMarkerWindowSave(event, marker, this.options.roomPrefix, html);
                window.close();
            });

            $("input[name=delete]", html).click((event) => {
                this.removeNode(marker);
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
                marker.appOptions.x = marker.appOptions.other;
                if(marker.appOptions.x) {
                    var info = marker.appOptions.x;
                    $("#form-other div", html).text("Cíl: bod #" + info.propertyId + ", patro " + info.floor.name + (typeSelect.val() == "passage" ? " budova " + info.building.name : ""));
                }
            }
            return html[0];



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

        public  removeNode(marker:google.maps.Marker, linesToo = true) {
            var index = this.markers.indexOf(marker);
            marker.setMap(null);
            var position = marker.getPosition();

            if(linesToo) {
                $.each(this.paths, (index, line:google.maps.Polyline) => {
                    if (!line) return;
                    var path = line.getPath();
                    if (path.getAt(0).equals(position) || path.getAt(1).equals(position)) {
                        line.setPath([]);
                        delete this.paths[index];
                    }
                });
            }

            delete this.markers[index];
        }

        public findNodeWithId(id, additionalSource = null) {
            var r = null;
            for (var i = 0; i < this.markers.length; i++) {
                if (this.markers[i] && (this.markers[i].appOptions.propertyId == id ||
                    (this.markers[i].appOptions.propertyId == undefined && this.markers[i].appOptions.id == id))) {
                    return this.markers[i];
                }
            }
        }

        public getPathBetween(sP, eP) {
            if (!(sP instanceof google.maps.LatLng)) {
                sP = this.findNodeWithId(sP);
                if (!sP) {
                    return null;
                }
                sP = sP.getPosition();
            }
            if (!(eP instanceof google.maps.LatLng)) {
                eP = this.findNodeWithId(eP);
                if (!eP) {
                    return;
                }
                eP = eP.getPosition();
            }
            for (var i = 0; i < this.paths.length; i++) {
                var line = this.paths[i];
                if (!line) continue;
                var path = line.getPath();
                if (path.length < 2) continue;
                if (path.getAt(0).equals(sP) && path.getAt(1).equals(eP)) {
                    return line;
                }
            }
        }

    }
}