var EDITOR_ADD = 0;
var EDITOR_MOVE = 1;

var editorState = EDITOR_ADD;

var STATE_ACTIVE = 0;
var STATE_INCACTIVE = 1;
var map = null;
var markers = [];
var lines = [];
var activePL;
var additionState = STATE_INCACTIVE;

//*********************
// State switching ****

$(document).ready(function () {
    $("#switcher-add").click(function () {
        changeState(EDITOR_ADD)
    });
    $("#switcher-move").click(function () {
        changeState(EDITOR_MOVE)
    })
});

function changeState(newState) {
    editorState = newState;
    additionState = STATE_INCACTIVE;
    var draggable = true;
    if (newState == EDITOR_ADD) {
        $("#switcher-move").removeClass("btn-info");
        $("#switcher-add").addClass("btn-info");
        //disable marker draggablity
        draggable = false;
        $("#tip-line").html("<b>Přidávání bodů</b>: levým kliknutím přidáváte body, jsou automaticky spojeny s předchozím. Pravým kliknutím přerušíte řadu bodů.");
    }
    if (newState == EDITOR_MOVE) {
        $("#switcher-add").removeClass("btn-info");
        $("#switcher-move").addClass("btn-info");
        $("#tip-line").html("<b>Úprava bodů</b>: Tažením můžete upravit pozici bodů na mapě. Připojené cesty se upraví automaticky.");
    }

    for(var i=0; i<markers.length; i++) {
        markers[i].setDraggable(draggable);
    }
}

/* *********************
*** MAP EVENTS ********/

function mapClick(event) {
    if (editorState == EDITOR_ADD) {
        if (additionState == STATE_ACTIVE) {
            finishPolyline(event.latLng);
            createPolyLine(event.latLng);
            addMarker(event.latLng, false);
        }
        if (additionState == STATE_INCACTIVE) {
            createPolyLine(event.latLng);
            addMarker(event.latLng, false);
            additionState = STATE_ACTIVE;
        }
    }
}

function mapRightClick(event) {
    if (editorState == EDITOR_ADD) {
        if (additionState == STATE_ACTIVE) {
            activePL = null;
            lines.pop();
            additionState = STATE_INCACTIVE;
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
            additionState = STATE_ACTIVE;
        }
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
    }
    movedMarker = [];
}


/* *************************
 *** POLYLINE  EVENTS ******/

function lineClicked(event) {
    if(editorState == EDITOR_ADD) {
        position = event.latLng
        if(additionState == STATE_ACTIVE) {
            finishPolyline(position);
        }
        if(additionState == STATE_INCACTIVE) {
            additionState = STATE_ACTIVE;
        }
        var index = 0
        for(; index<lines.length; index++) {
            if(lines[index] == this) {
                break;
            }
        }
        createPolyLine(position)
        finishPolyline(lines[index].getPath().getAt(1))
        lines[index].getPath().setAt(1,position)
        createPolyLine(position);
        addMarker(position, false);
    }
}


/* ************************
 *** OBJECT CREATION ******/
function createPolyLine(startPosition) {
    activePL = new google.maps.Polyline(polyOptions);
    activePL.setMap(map);
    activePL.getPath().push(startPosition)
}

function finishPolyline(endPosition) {
    if(activePL != undefined) {
        if(activePL.getPath().getAt(0) != endPosition) {
            activePL.getPath().push(endPosition);
            lines.push(activePL);
            updateList(activePL);

            google.maps.event.addListener(activePL,'click',lineClicked);
        }
    }
    activePL = null;
}


function addMarker(position, draggable) {
    var x = new google.maps.Marker({
        map:map,
        draggable:draggable,
        position:position,
        icon:'/images/red_dot.png'
    })
    google.maps.event.addListener(x, 'click', markerClick);
    google.maps.event.addListener(x, 'rightclick', markerRightClick);
    google.maps.event.addListener(x, 'dragstart', markerDragStart);
    google.maps.event.addListener(x, 'dragend',markerDragEnd);
    markers.push(x);
    return x
}