var EDITOR_ADD = 'add';
var EDITOR_MOVE = 'move';
var EDITOR_DETAIL = 'detail';

var editorState = EDITOR_ADD;

var markerType = 'intersection';
var markerImages = {
    intersection: "/images/red_dot.png",
    elevator: '/images/elevator.png',
    entrance: '/images/exit.png',
    passage: '/images/passage.png',
    stairs: '/images/stairs.png'
};

var STATE_ACTIVE = 'con';
var STATE_INCACTIVE = 'new';
var map = null;
var markers = [];

var lines = [];
var activePL;
var tempPL;
var additionState = STATE_INCACTIVE;


//*********************
// State switching ****

$(document).ready(function () {
    $("#switcher-add").click(function () {
        changeState(EDITOR_ADD);
    });
    $("#switcher-move").click(function () {
        changeState(EDITOR_MOVE);
    })
    $("#switcher-detail").click(function() {
        changeState(EDITOR_DETAIL);
    })
    $("a[id^='marker-']").click(function() {
        changeMarkerType(this.id.substring(7));
    })
    changeMarkerType('intersection');
});

function changeState(newState) {
    changeAdditionState(STATE_INCACTIVE);
    var draggable = true;
    var help = [];
    help['add']="<b>Přidávání bodů</b>: levým kliknutím přidáváte body, jsou automaticky spojeny s předchozím. Pravým kliknutím přerušíte řadu bodů.";
    help['move'] ="<b>Úprava bodů</b>: Tažením můžete upravit pozici bodů na mapě. Připojené cesty se upraví automaticky.";
    help['detail'] = "<b>Úprava detailů</b>: Kliknutím na jednotlivé body upravíte jejich typ a detaily.";

    editorState = newState;
    $("#switcher-move").removeClass("btn-primary");
    $("#switcher-add").removeClass("btn-primary");
    $("#switcher-detail").removeClass("btn-primary");
    $("#switcher-"+newState).addClass("btn-primary");
    $("#tip-line").html(help[newState]);

    $("div[id^='toolbar-']").hide();
    $("#toolbar-"+newState).show();

    for(var i=0; i<markers.length; i++) {
        markers[i].setDraggable(newState == EDITOR_MOVE);
    }
    if(editorState == EDITOR_ADD) {
        map.setOptions({draggableCursor:'crosshair'});
        changeMarkerType('intersection');
    }
    else {
        map.setOptions({draggableCursor:'hand'});
    }
}

function changeAdditionState(newState) {
    additionState = newState;
    $("#addition-con").removeClass("btn-info");
    $("#addition-new").removeClass("btn-info");

    $("#addition-"+newState).addClass("btn-info");
}

function changeMarkerType(type) {
    $("a[id^='marker-']").removeClass('btn-primary');
    $("#marker-"+type).addClass("btn-primary");

    markerType = type;
}
/* *********************
*** MAP EVENTS ********/

function mapClick(event) {
    if (editorState == EDITOR_ADD) {
        if (additionState == STATE_ACTIVE) {
            addMarker(event.latLng, false);
            finishPolyline(event.latLng);
            createPolyLine(event.latLng);

        }
        if (additionState == STATE_INCACTIVE) {
            addMarker(event.latLng, false);
            createPolyLine(event.latLng);

            changeAdditionState(STATE_ACTIVE);
        }
    }
}

function mapRightClick(event) {
    if (editorState == EDITOR_ADD) {
        if (additionState == STATE_ACTIVE) {
            activePL = null;
            tempPL.setPath([]);
            tempPL = null;
            changeAdditionState(STATE_INCACTIVE);
        }
    }
}

function mapMouseMove(event) {
    if(editorState == EDITOR_ADD && additionState == STATE_ACTIVE) {
        if(tempPL != undefined) {
            if(tempPL.getPath().length == 1) {
                tempPL.getPath().push(event.latLng);
            }
            else {
                tempPL.getPath().setAt(1, event.latLng);
            }
        }
    }
}
/* *******************************
*** MARKER PLACEMENT EVENTS ******/

function markerClick(event) {
    if (editorState == EDITOR_ADD) {
        if (additionState == STATE_ACTIVE) {
            finishPolyline(event.latLng);
            createPolyLine(event.latLng);
        }
        if (additionState == STATE_INCACTIVE) {
            createPolyLine(event.latLng);
            changeAdditionState(STATE_ACTIVE);
        }
    }

    if (editorState == EDITOR_DETAIL) {
        var infoWindow = new google.maps.InfoWindow();
        infoWindow.setContent(getInfoWindowContent(this, infoWindow));
        infoWindow.open(map, this)
    }
}

function markerRightClick(event) {

}


/* ****************************
 *** MARKER MOVE EVENTS ******/

var movedMarker = [];
var moveStart = null;
function markerDragStart(event) {

    if(editorState == EDITOR_MOVE) {
        position = this.position;
        moveStart = position;
        for (var i = 0; i < lines.length; i++) {
            path = lines[i].getPath();
            if (path.getAt(0) == position) {
                movedMarker[i] = lines[i];
            }
            if (path.getAt(1) == position) {
                movedMarker[i] = lines[i];
            }
        }
    }
}

function markerDragEnd(event) {
    if(editorState == EDITOR_MOVE) {
        newPosition = this.position;
        for(var i=0; i<movedMarker.length; i++) {
            if (movedMarker[i] == undefined) continue;

            line = lines[i];
            if(line.getPath().getAt(0) == moveStart) {
                line.getPath().setAt(0, newPosition);
            }
            if(line.getPath().getAt(1) == moveStart) {
                line.getPath().setAt(1, newPosition);
            }
        }
        index = markers.indexOf(this)
        updatePointsList(index, this)
    }
    movedMarker = [];
}


/* *************************
 *** POLYLINE  EVENTS ******/

function lineClicked(event) {
    if(editorState == EDITOR_ADD) {
        position = event.latLng
        addMarker(position, false);
        if(additionState == STATE_ACTIVE) {
            finishPolyline(position);
        }
        if(additionState == STATE_INCACTIVE) {
            changeAdditionState(STATE_ACTIVE);
        }
        var index = lines.indexOf(this);

        createPolyLine(position)
        finishPolyline(lines[index].getPath().getAt(1))
        lines[index].getPath().setAt(1,position)
        createPolyLine(position);

    }
}


/* ************************
 *** OBJECT CREATION ******/
function createPolyLine(startPosition) {
    activePL = new google.maps.Polyline(polyOptions);
    activePL.setMap(map);
    activePL.getPath().push(startPosition);
    tempPL = new google.maps.Polyline(tempPolyOptions);
    tempPL.setMap(map);
    tempPL.getPath().push(startPosition);
}

function finishPolyline(endPosition) {
    if(activePL != undefined) {
        if(activePL.getPath().getAt(0) != endPosition) {
            activePL.getPath().push(endPosition);
            lines.push(activePL);

            google.maps.event.addListener(activePL,'click',lineClicked);
            updateLinesList()
        }
    }
    activePL = null;
    tempPL.setPath([]);
    tempPL = null;
}


function addMarker(position, draggable) {
    var x = new google.maps.Marker({
        map:map,
        draggable:draggable,
        position:position,
        icon: markerImages[markerType],
        title: "#"+(markers.length +1),
        appType: markerType
    });

    google.maps.event.addListener(x, 'click', markerClick);
    google.maps.event.addListener(x, 'rightclick', markerRightClick);
    google.maps.event.addListener(x, 'dragstart', markerDragStart);
    google.maps.event.addListener(x, 'dragend',markerDragEnd);

    markers.push(x);

    updatePointsList(markers.length-1,x)
    return x
}

function getMarkerIndexInPosition(position) {
    for(var i=0; i<markers.length; i++) {
        if(markers[i].position == position) {
            return i;
        }
    }
}

/* **** UI HANDLE **** */

function updatePointsList(index, item) {
    var parent = $("#points-list");
    if($("#points-list #point-"+index).length > 0) {
    //    var inner = $("#points-list #point-"+index);
    //    inner.text(item.position)
    }
    else {
        var newEl = $("#point-sample").clone()
        newEl.attr('style','')
        newEl.attr('id','point-'+index)
        newEl.find("strong").text("#"+(index+1))
        var select = newEl.find("select");
        select.attr('id','point-selector-'+index)

        parent.append(newEl);
        var select = $("#point-selector-"+index);

        select.change(function() {
            itemId = this.id.substring(15);
            value = this.value;
            var image = $(this).children("[value="+value+"]").attr("data-image");
            //markersImage[itemId] = image;
            markers[itemId].setIcon(image);
        });
        select.msDropdown();
        registerEvents(newEl)
    }
}

function updateLinesList() {
    var el = $("#lines-list");
    el.html("");
    for(var i=0; i<lines.length; i++) {
        var newOne = $("<div></div>");
        newOne.addClass('list-row');

        line = lines[i]
        path = line.getPath()
        var indexA = getMarkerIndexInPosition(path.getAt(0));
        var indexB = getMarkerIndexInPosition(path.getAt(1));
        newOne.attr('id','lines-'+i);
        newOne.html("<strong>#"+(indexA+1)+" -> #"+(indexB+1)+"</strong>");
        el.append(newOne);
        registerEvents(newOne)
    }
}

function registerEvents(item) {
    handle = function(id, lineChange, markerChange) {
        var itemId = id.substr(6);
        var type = id.substr(0,5);
        if(type == "lines") {
            lines[itemId].setOptions({strokeColor:lineChange});
        }
        if(type == "point") {
            markers[itemId].setIcon(markerChange == null?markerImages[markers[itemId].appType]:markerChange);
        }
    };
    item.mouseover(function() {
        handle(this.id, '#00ff00','/images/green_dot.png');
    });
    item.mouseout(function() {
        handle(this.id, '#ff0000',null);
    })
}
/* ****************************
***** INFO WINDOW HANDLE **** */
function getInfoWindowContent(marker, window) {
    var markerType = marker.appType;
    var html = $("#innerForm").clone();
    $("select", html).val(markerType);
    $("input[type=submit]", html).click(function() {
        if(marker.appType != $("select", html).val()) {
            marker.appType = $("select", html).val();
            marker.setIcon(markerImages[marker.appType]);
        }
        window.close()
    });

    html.attr('style',"");
    return html[0];
}