/**
 * Created with JetBrains PhpStorm.
 * User: Jan
 * Date: 13.4.13
 * Time: 10:34
 * To change this template use File | Settings | File Templates.
 */

module Mapping {
    export class ModalMap {

        private highlight:google.maps.Marker;
        private saved:google.maps.Marker;
        private map:Mapping.BasicMap;
        private requestedType:string;

        constructor(private editor:Mapping.MarkerEditor) {

        }

        public load(destination, dataDestination, data, type, callback, propertyId) {
            var modal = $("#modal").modal({
                keyboard: true,
                backdrop: true,
                show: true
            });
            $('#modal-actual').hide();
            $("#modal-loader").show();
            $.get(destination, data, (payload, textStatus, XMLHttpRequest) => {
                    if (payload.redirect) {
                        window.location.href = payload.redirect;
                        return;
                    }
                    if(payload.snippets['snippet--modal']) {
                        $('#modal-content').html(payload.snippets['snippet--modal']);

                        $.get(dataDestination, data, (payload, textStatus, XMLHttpRequest) => {
                           this.initEvents(callback);
                           this.show(payload.data, type, propertyId);
                        });
                        return;
                    }
                    alert("Server neposlal žádná data...");
                    modal.modal('hide');
                }
            );
        }

        private initEvents(callback) {
            var modal = $("#modal");
            modal.find("#floors-submit").on('click',(event) => {
                event.preventDefault();
                $("#floors-submit", modal).ajaxSubmit((payload) => {
                    this.reloadMap(payload.data);
                });
            });
            modal.css({
                width: '100%',
                'max-width': '850px',
                'margin-left': function () {
                    return -($(this).width() / 2);
                }
            });

            $(window).resize(()=>this.fitModalBody(modal));
            $(".modal-footer .btn-primary", modal).click((event) => {
                modal.modal('hide');
                var floor = $("form select[name=floor] option:selected", modal);
                var building = $("form select[name=building] option:selected", modal);
                callback({
                    marker: this.saved,
                    floorInfo: {
                        id: floor.val(),
                        name: floor.text()
                    },
                    buildingInfo: {
                        id: building.val(),
                        name: building.text()
                    }

                });
                event.preventDefault();
                return false;
            });
            $(".modal-footer .btn-danger", modal).click(() => {
                if (window.confirm("Opravdu odstranit?")) {
                    modal.modal('hide');
                    callback({
                        deleted: true
                    });
                }

                event.preventDefault();
                return false;
            });

            modal.on('hide', () => {
                modal.find("#floors-submit").unbind('click');
                modal.find("#floors-submit").unbind('click');
                $(".modal-footer .btn-primary", modal).unbind('click');
                $(".modal-footer .btn-danger", modal).unbind('click');
                modal.unbind('hide');
            });
        }

        private reloadMap(payload, propertyId=null) {
            var modal = $("#modal");
            var data = payload;
            if (!data || ($.isEmptyObject(data.points))) {
                this.noMapData(modal);
                return;
            }

            modal.find(".alert").addClass('hide');
            modal.find("#map_canvas-modal").removeClass('hide');

            if(this.map == null) {
                this.map = new Mapping.BasicMap(document.getElementById("map_canvas-modal"), {zoom:data.zoom, center:data.center, pathOptions: data.pathOptions});
                this.map.initialize();
                this.setNewData(data, this.map);
            } else {
                this.setNewData(payload, this.map);
            }
            this.fitModalBody(modal);

            if(propertyId != null) {
                this.highlightPropertyId(propertyId);
            }
        }

        public show(payload, type, propertyId) {

            this.requestedType = type;
            $("#modal-info").html(this.createInfoText(type));
            $("#modal-info-2").hide();


            var modal = $("#modal");

            $("#modal-loader").slideUp();
            $('#modal-actual').slideDown();
            this.reloadMap(payload, propertyId);



        }

        private createInfoText(type) {
            return "Musíte vybrat bod typu " + this.editor.readableNodeType(type)
        }

        private fitModalBody(modal) {
            var header = $(".modal-header", modal);
            var body = $(".modal-body", modal);
            var footer = $(".modal-footer", modal);

            var modalheight = parseInt(modal.css("height"));
            var headerheight = header.outerHeight();
            var footerHeight = footer.outerHeight();
            var bodypaddings = parseInt(body.css("padding-top")) + parseInt(body.css("padding-bottom"));

            var height = $(window).height() - headerheight - bodypaddings - footerHeight - 10;
            if($(window).height() < 750) {
                modal.css("top", '1%');
            }
            else {
                modal.css("top", '10%');
            }
            if(height < 320) {
                height = 300;
            }
            body.css("max-height", height+"px");
        }

        private setNewData(payload, map:Mapping.BasicMap) {

            var newPoints = [];
            var newPaths = [];
            var points = payload.points;
            var paths = payload.paths;

            map.map.setCenter(new google.maps.LatLng(payload.center['lat'], payload.center['lng']));
            map.map.setZoom(payload.zoom);
            while (map.map.overlayMapTypes.length > 0) {
                map.map.overlayMapTypes.pop();
            }
            map.loadCustomLayers(payload.customLayers);

            if(this.highlight != null) {
                this.highlight.setMap(null);
                this.highlight = null;
            }


            $.each(map.markers, (index, item) => {
                if (!item) return;
                item.setMap(null);
            });
            map.markers = [];
            $.each(map.paths, (index, item) => {
                if (!item) return;
                item.setPath([]);
                item.setMap(null);
            });
            map.paths = [];

            for (var i = 0; i < points.length; i++) {
                var item = points[i];
                if (!item) continue;
                var pointInfo = {};
                pointInfo.appOptions = JSON.parse(item.appOptions);

                pointInfo.position = {
                    lat: item.position.lat,
                    lng: item.position.long
                };
                pointInfo.icon = this.editor.getMarkerIcon(item.type);
                pointInfo.draggable = false;
                pointInfo.title = item.title;

                newPoints.push(pointInfo);
            }

            map.loadMarkers(newPoints);
            this.registerMarkerClickHandler(map.markers);

            for (var i = 0; i < paths.length; i++) {
                var item = paths[i];
                if (!item) continue;
                var pathInfo = {
                    start: { lat: item.start.lat, lng: item.start.long},
                    end: { lat: item.end.lat, lng: item.end.long}
                };

                newPaths.push(pathInfo);
            }

            map.loadPaths(newPaths);
        }

        private registerMarkerClickHandler(markers:google.maps.Marker[]) {
            var _this = this;
            $.each(markers, (index, item:google.maps.Marker) => {
                google.maps.event.addListener(item, 'click', function(event) {
                    _this.onMarkerClick(event, this);
                })
            })
        }

        private onMarkerClick(event, marker:google.maps.Marker) {
            if(marker.appOptions.type != this.requestedType) {
                alert("Musíte vybrat bod typu "+this.editor.readableNodeType(this.requestedType) + ", vybrali jste typ "+this.editor.readableNodeType(marker.appOptions.type));
                return;
            }
            this.highlightPosition(marker.getPosition());
            this.saved = marker;
            $("#modal-info-2").show().text("Zvolen bod #"+marker.appOptions.propertyId+(marker.getTitle()?", název '"+marker.getTitle()+"'":""));
        }

        private highlightPosition(position:google.maps.LatLng) {
            if(this.highlight == null) {
                this.highlight = new google.maps.Marker({
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 11,
                        strokeWeight: 2,
                        strokeColor: "#00ff00"
                    },
                    map: this.map.map,
                    clickable: false
                });
            }
            this.highlight.setPosition(position);
            this.highlight.setPosition(position);
        }

        private highlightPropertyId(id) {
            if(id == null) return;
            var r = null;
            for (var i = 0; i < this.map.markers.length; i++) {
                if (this.map.markers[i] && (this.map.markers[i].appOptions.propertyId == id)) {
                    google.maps.event.trigger(this.map.markers[i], 'click');
                }
            }
        }
        private noMapData(modal) {
            $(".alert", modal).removeClass('hide');
            $("#map_canvas-modal", modal).addClass('hide');
        }
    }


}
