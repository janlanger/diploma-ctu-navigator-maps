var EDITOR_ADD = 'add';
var EDITOR_DETAIL = 'detail';

var editorState = EDITOR_ADD;

var markerType = 'intersection';

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
    $("#switcher-detail").click(function() {
        changeState(EDITOR_DETAIL);
    })
    $("a[id^='marker-']").click(function() {
        changeMarkerType(this.id.substring(7));
    })
    changeState(EDITOR_ADD);
    changeMarkerType('intersection');
});

function changeState(newState) {
    changeAdditionState(STATE_INCACTIVE);
    var draggable = true;
    var help = [];
    help['add']="<b>Přidávání bodů</b>: levým kliknutím přidáváte body, jsou automaticky spojeny s předchozím. Pravým kliknutím přerušíte řadu bodů.";
    help['detail'] = "<b>Úprava detailů</b>: Kliknutím na jednotlivé body upravíte jejich typ a detaily, tažením upravíte jejich pozici.";

    editorState = newState;
    $("#switcher-move").removeClass("btn-primary");
    $("#switcher-add").removeClass("btn-primary");
    $("#switcher-detail").removeClass("btn-primary");
    $("#switcher-"+newState).addClass("btn-primary");
    $("#tip-line").html(help[newState]);

    $("div[id^='toolbar-']").hide();
    $("#toolbar-"+newState).show();

    for(var i=0; i<markers.length; i++) {
        markers[i].setDraggable(newState == EDITOR_DETAIL);
    }
    if(editorState == EDITOR_ADD) {
        if(map != null)
            map.setOptions({draggableCursor:'crosshair'});
        changeMarkerType('intersection');
    }
    else {
        map.setOptions({draggableCursor:'hand'});
    }
}

function changeAdditionState(newState) {
    additionState = newState;
    if(newState == STATE_INCACTIVE && tempPL != null) {
        tempPL.setPath([]);
        tempPL = null;
    }
}

function changeMarkerType(type) {
    $("a[id^='marker-']").removeClass('btn-primary');
    $("#marker-"+type).addClass("btn-primary");

    markerType = type;
}
/* *********************
*** MAP EVENTS ********/

function getMarkerIcon(type) {
    var markerImages = {
        intersection: {url:"/images/red_dot.png",anchor: new google.maps.Point(4,4)},
        elevator: {url: '/images/elevator.png', anchor: new google.maps.Point(7,8)},
        entrance: {url: '/images/exit.png', anchor: new google.maps.Point(7,8)},
        passage: {url: '/images/passage.png', anchor: new google.maps.Point(7,8)},
        stairs: {url: '/images/stairs.png', anchor: new google.maps.Point(7,8)}
    };
    return markerImages[type];
}

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
        initAndOpenInfoWindow(this, infoWindow);
    }
}

function markerRightClick(event) {

}


/* ****************************
 *** MARKER MOVE EVENTS ******/

var movedMarker = [];
var moveStart = null;
function markerDragStart(event) {

    if(editorState == EDITOR_DETAIL) {
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
    if(editorState == EDITOR_DETAIL) {
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
        index = markers.indexOf(this);
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
        icon: getMarkerIcon(markerType),
        title: "#"+(markers.length +1),
        appType: markerType
    });

    google.maps.event.addListener(x, 'click', markerClick);
    google.maps.event.addListener(x, 'rightclick', markerRightClick);
    google.maps.event.addListener(x, 'dragstart', markerDragStart);
    google.maps.event.addListener(x, 'dragend',markerDragEnd);

    markers.push(x);
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

/* ****************************
***** INFO WINDOW HANDLE **** */
function initAndOpenInfoWindow(marker, window) {
    var markerType = marker.appType;
    var html = $("#innerForm form").clone();
    $("div[id^=form]:lt(3)", html).each(function() {

        $(this).css("display","");
    })
    $("select", html).val(markerType);
    $("input[type=submit]", html).click(function() {
        if(marker.appType != $("select", html).val()) {
            marker.appType = $("select", html).val();
            marker.setIcon(getMarkerIcon(marker.appType));
        }
        window.close()
    });
    html.removeAttr('id');
    html.attr('style',"");
    window.setContent(html[0]);
    google.maps.event.addListener(window, 'domready', function() {
        Nette.initForm(html[0]);
    });
    window.open(map, marker);

}