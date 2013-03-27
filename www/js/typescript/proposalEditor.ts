/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 24.3.13
 * Time: 22:49
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
    export class ProposalEditor extends Mapping.MarkerEditor {

        public reverseChanges = [];
        private markersOriginals = [];

        constructor(private mapElement:Element, private options) {
            super(mapElement, options);
        }

        public initialize() {
            super.initialize();

            for (var key in this.markers) {
                var item = this.markers[key];

                this.markersOriginals[key] = $.extend({}, item.appOptions);
                this.markersOriginals[key].gps = item.getPosition();
            }

            this.registerEvents();
            this.detectCollisions();
        }

        /*  public createMarker(markerOptions, additional = {}) {
         var marker = super.createMarker(markerOptions, additional);

         if(marker.appOptions) {
         alert('aaaa');
         marker.appOptionsOriginal = $.extend({}, marker.appOptions);
         }
         return marker;
         }*/

        private registerEvents() {
            var _this = this;
            if (this.options.proposals) {
                $.each(this.options.proposals, (index, item) => {
                    $("#" + index + " a").click(() => {
                        var element = $("#modalTemplate");
                        $("#modal-header", element).text("Návrh od " + item.author + " z " + item.date + ":");
                        var content = "Komentář uživatele: <em>" + item.comment + "</em><br><br>";
                        if (item.specification.nodes && item.specification.nodes.length > 0) {
                            content += "<strong>Body:</strong><ul>";
                            for (var i = 0; i < item.specification.nodes.length; i++) {
                                content += "<li>" + this.getNodeChangeTextual(item.specification.nodes[i]) + "</li>";
                            }
                            content += "</ul>";
                        }
                        if (item.specification.paths && item.specification.paths.length > 0) {
                            content += "<strong>Cesty:</strong> ";
                            var added = 0;
                            var deleted = 0;
                            for (var i = 0; i < item.specification.paths.length; i++) {
                                if (item.specification.paths[i].deleted) {
                                    deleted++;
                                } else {
                                    added++;
                                }
                            }
                            if (added > 0)
                                content += "<b>" + added + "x</b> nová ";
                            if (deleted > 0)
                                content += "<b>" + deleted + "x</b> odstraněná";
                        }
                        $("#modal-content", element).html(content);
                        element.modal({show: true});

                        return false;
                    });

                    $("#" + index + " input[type=radio]").change(function (event) {
                        _this.handleApproveClick(event, this, index)
                    })
                });

                $("#proposal-send").click((event) => {
                    event.preventDefault();
                    var addedNodes = [];
                    var changedNodes = [];
                    var deletedNodes = [];
                    var checked = [];
                    for (var key in this.markers) {
                        var original = this.markersOriginals[key];
                        var item = this.markers[key];
                        item.appOptions.gps = item.getPosition();
                        if (!original) {
                            //added
                            addedNodes[key] = item.appOptions;
                        }

                        if (item.getMap() == null) {
                            //deleted
                            deletedNodes[key] = this.markersOriginals[key];
                        }
                        if (original && item.getMap() != null && !this.optionsEquals(original, item.appOptions)) {
                            changedNodes[key] = item.appOptions;
                            console.log(item.appOptions, original);
                        }
                        checked[key] = true;
                    }

                    for (var key in this.markersOriginals) {
                        if (checked[key]) continue;
                        deletedNodes[key] = this.markersOriginals[key];
                    }
                    console.log(addedNodes, changedNodes, deletedNodes);
                    return false;

                });

            }
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

        private getNodeChangeTextual(nodeChange) {
            var description = "";
            if (nodeChange.original && !nodeChange.deleted) {
                description = "Změna bodu:";
                description += "<ul><li>" + this.nodeChangeDesc(nodeChange.original, nodeChange.properties).join("</li><li>") + "</li></ul>";
            }
            if (nodeChange.deleted) {
                description = "Odstranění bodu";
                description += "<ul><li>" + this.nodeChangeDesc(nodeChange.original).join("</li><li>") + "</li></ul>";
            }
            else if (!nodeChange.original) {
                description = "Nový bod:";
                description += "<ul><li>" + this.nodeChangeDesc(nodeChange.properties).join("</li><li>") + "</li></ul>";
            }
            return description;
        }

        private nodeChangeDesc(original, newNode = "") {
            var x = [];
            $.each(original, (index, item) => {
                var y = "";
                if (index == "id") return;
                if (newNode && item == newNode[index]) return;
                if (newNode) {
                    if (index == "gpsCoordinates") {
                        y += "<b>Změna pozice</b>";
                    } else {
                        var newOne = newNode[index];
                        if (index == "type") {
                            item = this.readableNodeType(item);
                            newOne = this.readableNodeType(newOne);
                        }
                        y += "<b>" + this.readableIndex(index) + ":</b> " + (item == null ? "prázdný" : item) + "->" + newOne;
                    }
                } else {
                    if (index == "gpsCoordinates" || item == null) {
                        return;
                    }
                    if (index == "type") {
                        item = this.readableNodeType(item);
                    }
                    y += "<b>" + this.readableIndex(index) + ":</b> " + (item == null ? "prázdný" : item);
                }
                x.push(y);
            });
            return x;
        }

        private readableIndex(index) {
            var x = {
                room: "Místnost",
                type: "Typ bodu",
                name: "Popisek",
                fromFloor: "Z patra",
                toFloor: "Do patra",
                toBuilding: "Do budovy"
            };
            return x[index];
        }

        private readableNodeType(index) {
            if (this.options.markerTypes[index]) {
                return this.options.markerTypes[index].legend;
            }
            return index;
        }

        public handleApproveClick(event, radio, index) {
            radio = $(radio);
            if (!radio.is(':checked')) {
                return;
            }

            if (radio.val() == "approve") {
                radio.parent().parent().removeClass("error");
                radio.parent().parent().addClass("success");
                this.applyChanges(index, this.options.proposals[index].specification);
            }
            else {
                radio.parent().parent().addClass("error");
                radio.parent().parent().removeClass("success");
                this.removeChanges(this.reverseChanges[index]);
                this.reverseChanges[index] = {};
            }
        }

        private applyChanges(name, spec) {
            this.reverseChanges[name] = {};

            if (spec.nodes) {
                this.reverseChanges[name].nodes = {
                    deleted: [],
                    added: [],
                    changed: []
                };
                for (var i = 0; i < spec.nodes.length > 0; i++) {
                    var item = spec.nodes[i];
                    if (!item.original) {
                        //add
                        var position = this.parsePositionString(item.properties.gpsCoordinates);
                        var marker = this.createMarker({
                            draggable: true,
                            position: position,
                            icon: this.getMarkerIcon(item.properties.type)
                        }, item.properties);
                        this.eventHandler.registerMarkerEvents(marker);
                        this.markers.push(marker)
                        this.addStrokeMarker(marker);
                        this.reverseChanges[name].nodes.added.push(this.markers.length - 1);
                    }
                }
            }
            if (spec.paths) {
                this.reverseChanges[name].paths = {
                    deleted: [],
                    added: []
                };

                for (var i = 0; i < spec.paths.length > 0; i++) {
                    var item = spec.paths[i];
                    if (item.deleted) {
                        //remove

                        var path = this.getPathBetween(item.original.startNode, item.original.endNode);

                        path.setPath([]);
                        path.setMap(null);
                        this.reverseChanges[name].paths.deleted.push(item.original);
                        delete this.paths[this.paths.indexOf(path)];

                    }
                    else {
                        var options = $.extend({}, this.options.pathOptions);
                        options.strokeColor = "#00ff00";
                        var path = this.createPath(this.findNodeWithId(item.properties.startNode).getPosition(), this.findNodeWithId(item.properties.endNode).getPosition(), options);
                        this.paths.push(path);
                        this.reverseChanges[name].paths.added.push(this.paths.length - 1);
                    }
                }
            }

            if (spec.nodes) {
                for (var i = 0; i < spec.nodes.length > 0; i++) {
                    var item = spec.nodes[i];
                    if (item.deleted) {
                        var node = this.findNodeWithId(item.original.id);

                        this.reverseChanges[name].nodes.deleted.push(this.markers.indexOf(node));
                        if (node.appStroke) {
                            node.appStroke.setMap(null);
                        }
                        node.setMap(null); //hides the node from map
                    }
                    if (!item.deleted && item.original) {
                        //change
                        var node = this.findNodeWithId(item.original.id);
                        if (node) {
                            if (item.original.gpsCoordinates != item.properties.gpsCoordinates) {
                                var newPosition = this.parsePositionString(item.properties.gpsCoordinates);
                                this.movePathEnd(node.getPosition(), newPosition);
                                node.setPosition(newPosition);
                            }
                            if (item.original.type != item.properties.type) {
                                node.setIcon(this.getMarkerIcon(this.properties.type));
                            }
                            node.appOptions.toBuilding = item.properties.toBuilding;
                            node.appOptions.toFloor = item.properties.toFloor;
                            node.appOptions.fromFloor = item.properties.fromFloor;
                            node.appOptions.name = item.properties.name;
                            node.appOptions.room = item.properties.room;

                            this.addStrokeMarker(node);
                            this.reverseChanges[name].nodes.changed[this.markers.indexOf(node)] = item.original;
                        }
                    }
                }
            }


        }

        private removeChanges(reverseSpec) {
            if (reverseSpec == undefined) return;
            if (reverseSpec.nodes) {
                for (var i = 0; i < reverseSpec.nodes.deleted.length; i++) {
                    var index = reverseSpec.nodes.deleted[i];
                    var item = this.markers[index];
                    item.setMap(this.map);
                    if (item.appStroke) {
                        item.appStroke.setMap(this.map);
                    }
                }
                for (var i = 0; i < reverseSpec.nodes.added.length; i++) {
                    var item = this.markers[reverseSpec.nodes.added[i]];
                    if (item.appStroke) {
                        item.appStroke.setMap(null);
                    }
                    this.removeNode(item, false);
                }
                for (var i = 0; i < reverseSpec.nodes.changed.length; i++) {
                    var item = reverseSpec.nodes.changed[i];
                    if (!item) continue;

                    var node = this.findNodeWithId(item.id);
                    if (node) {
                        node.appStroke.setMap(null);
                        node.appStroke = undefined;

                        var newPosition = this.parsePositionString(item.gpsCoordinates);
                        this.movePathEnd(node.getPosition(), newPosition);
                        node.setPosition(newPosition);

                        node.setIcon(this.getMarkerIcon(item.type));
                        node.appOptions.toBuilding = item.toBuilding;
                        node.appOptions.toFloor = item.toFloor;
                        node.appOptions.fromFloor = item.fromFloor;
                        node.appOptions.name = item.name;
                        node.appOptions.room = item.room;
                    }

                }
            }
            if (reverseSpec.paths) {
                for (var i = 0; i < reverseSpec.paths.deleted.length; i++) {
                    var item = reverseSpec.paths.deleted[i];
                    this.paths.push(this.createPath(this.findNodeWithId(item.startNode).getPosition(), this.findNodeWithId(item.endNode).getPosition()));
                }
                for (var i = 0; i < reverseSpec.paths.added.length; i++) {
                    var path = this.paths[reverseSpec.paths.added[i]];
                    path.setMap(null);
                    path.setPath([]);
                    delete this.paths[this.paths.indexOf(path)];
                }
            }
        }

        private parsePositionString(position) {
            var p = position.split(",");
            return new google.maps.LatLng(p[0], p[1]);
        }

        private findNodeWithId(id, additionalSource = null) {
            var r = null;
            for (var i = 0; i < this.markers.length; i++) {
                if (this.markers[i] && (this.markers[i].appOptions.propertyId == id ||
                    (this.markers[i].appOptions.propertyId == undefined && this.markers[i].appOptions.id == id))) {
                    return this.markers[i];
                }
            }
        }

        private addStrokeMarker(marker) {
            var stroke = new google.maps.Marker({
                position: marker.getPosition(),
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 11,
                    strokeWeight: 2,
                    strokeColor: "#00ff00"
                },
                map: marker.getMap(),
                clickable: false
            });


            google.maps.event.addListener(marker, 'dragend', function (event) {
                stroke.setPosition(this.getPosition());
            });
            marker.appStroke = stroke;
        }

        private movePathEnd(old, newOne) {
            if (old.equals(newOne)) return;
            for (var i = 0; i < this.paths.length; i++) {
                var line = this.paths[i];
                if (!line) continue;
                var path = line.getPath();
                if (path.length < 2) continue;
                if (path.getAt(0).equals(old)) {
                    path.setAt(0, newOne);
                }
                if (path.getAt(1).equals(old)) {
                    path.setAt(1, newOne);
                }

            }
        }

        private getPathBetween(start, end) {
            var sP = this.findNodeWithId(start).getPosition();
            var eP = this.findNodeWithId(end).getPosition();
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

        private detectCollisions() {
            $.each(this.options.collisions, (index, item)=> {
                var element = $("<img>");
                element.attr("src", "/images/icons/warning.png");
                var title = "Tento návrh upravuje stejné prvky jako návrh ";
                for (var key in item) {
                    if (key === 'length' || !item.hasOwnProperty(key)) continue;
                    title += "#" + key;
                }
                element.attr("title", title);
                element.css("margin", '0px 5px');
                $("#proposal" + index).children("td:first").append(element);
            });
        }
    }
}
