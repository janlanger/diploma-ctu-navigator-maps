var EDITOR_ADD = 0;
var EDITOR_MOVE = 1;

var editorState = EDITOR_ADD;

var STATE_ACTIVE = 0;
var STATE_INCACTIVE = 1;
var map = null;
var markers = [];
var markersImgae = [];
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
            addMarker(event.latLng, false);
            finishPolyline(event.latLng);
            createPolyLine(event.latLng);

        }
        if (additionState == STATE_INCACTIVE) {
            addMarker(event.latLng, false);
            createPolyLine(event.latLng);

            additionState = STATE_ACTIVE;
        }
    }
}

function mapRightClick(event) {
    if (editorState == EDITOR_ADD) {
        if (additionState == STATE_ACTIVE) {
            activePL = null;
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
            additionState = STATE_ACTIVE;
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
    activePL.getPath().push(startPosition)
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
}


function addMarker(position, draggable) {
    var x = new google.maps.Marker({
        map:map,
        draggable:draggable,
        position:position,
        icon:'/images/red_dot.png',
        title: "#"+(markers.length +1)
    });

    google.maps.event.addListener(x, 'click', markerClick);
    google.maps.event.addListener(x, 'rightclick', markerRightClick);
    google.maps.event.addListener(x, 'dragstart', markerDragStart);
    google.maps.event.addListener(x, 'dragend',markerDragEnd);
    markers.push(x);
    markersImgae.push('/images/red_dot.png');
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
            markersImgae[itemId] = image;
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
        var id = id;
        var itemId = id.substr(6);
        var type = id.substr(0,5);
        if(type == "lines") {
            lines[itemId].setOptions({strokeColor:lineChange});
        }
        if(type == "point") {
            markers[itemId].setIcon(markerChange == null?markersImgae[itemId]:markerChange);
        }
    };
    item.mouseover(function() {
        handle(this.id, '#00ff00','/images/green_dot.png');
    })
    item.mouseout(function() {
        handle(this.id, '#ff0000',null);
    })
}