// keep the sizes up to date
var EM1 = 12,
    updateSizes = function() {
        EM1 = Math.floor($(window).height() / 24);
        $("body, #gamepanel, #gamepanel-overlay").css({fontSize: EM1, lineHeight: 1});
    },
            
    // whether the game is paused
    PAUSED = true,
    // the stone-matrix
    GMATRIX = null,
    // the current game-id, as assigned by the server
    CURRENT_GAME_ID = null,
    // Game turns to be submitted to the server
    TURN_QUEUE = new TransactionQueue(),
    // Current BrickSequenceGenerator
    BRICK_SEQUENCE_GENERATOR = null,
    // timeout-reference for the current brick interval
    FALLING_TIMEOUT = null,
    // the brick currently falling
    CURRENT_FALLING_BRICK = null,
    // whether a ghost is shown
    SHOW_GHOST = true,
    // width of the box
    WIDTH = 10,
    // height of the box
    HEIGHT = 19,
    // level, increases ever ? points
    LEVEL = 0,
    POINTS = 0,
    // defines what level starts at how much points
    LEVEL_ADVANCE = [0, 500, 2500, 5000, 10000, 25000, 75000, 150000],
    // Brick falling speed, decreases as level increases
    START_INTERVAL = 750,
	// maximum time between two equal actions to exaggerate it
	ACTION_EXAGGERATE_TIMEOUT = 150,
	// actions that support exaggeration
	EXAGGERATE_SUPPORTED_ACTIONS = [
		ACTION.MOVE_LEFT, ACTION.MOVE_RIGHT, ACTION.SOFT_DROP
	],
    // the next two bricks after the current one
    NEXT_BRICK_1 = getRandomBrick(),
    NEXT_BRICK_2 = getRandomBrick(),
    COMBO_STREAK = 0,
    // REFERENCES TO ALL AUDIO-TRACKS
    AUDIO = {
        MUTED: true,
        THEME: null,
        setMuted: function(is)
        {
            AUDIO.THEME.muted = is;
            AUDIO.MUTED = is? true : false;
            $.settings("audio.muted", is? "true" : "false");
        }
    };

function applyBrick(brick, GMATRIX)
{
    brick.applyTo(GMATRIX);
            
    updateUI(GMATRIX);
    var isGameOver = false;
    var removedLines = removeFullLines(function() {
        updateUI(GMATRIX);

        if (brick.getPosition().y < 0
         || (brick.getPosition().y == 0 && brick.collides(GMATRIX)))
        {
            GAME_OVER();
            isGameOver = true;
        }
        else
        {
            // trigger the next brick
            nextBrick();

            // queue this turn to be submitted to the server
            TURN_QUEUE.pushItem(brick);
        }
    });

    // if this brick ends the game, don't count it as an additional point
    if (isGameOver) return;

    var additionalPoints;

    if (removedLines == 0)
    {
        COMBO_STREAK = 0;
        additionalPoints = 1;
    }
    else
    {
        COMBO_STREAK++;
        if (COMBO_STREAK >= 2)
        {
            showEffectText(COMBO_STREAK + " Combo");
        }
 
        if (removedLines == 1)
        {
            additionalPoints = 15;
        }
        else if (removedLines == 2)
        {
            additionalPoints = 225;
        }
        else if (removedLines == 3)
        {
            additionalPoints = 990;
        }
        else if (removedLines == 4)
        {
            additionalPoints = 2300;
        }
        else
        {
            additionalPoints = Math.pow(5, removedLines);
        }
        
        additionalPoints *= COMBO_STREAK;
    }
    
    POINTS += additionalPoints;

    $.each(LEVEL_ADVANCE, function(level, reqPoints)
    {
        if (POINTS >= reqPoints)
        {
            LEVEL = level;
        }
    });

    updateTextUI();
}

function setFallingBrick(brick)
{
    window.clearTimeout(FALLING_TIMEOUT);

    CURRENT_FALLING_BRICK = brick;
    $("#play-overlay").html("").append(brick.getDOMElement());
    brick.setRotation(0);
    
    FALLING_TIMEOUT = window.setInterval(function() {
        if (!CURRENT_FALLING_BRICK.tryMoveDown(1, GMATRIX))
        {            
            applyBrick(CURRENT_FALLING_BRICK, GMATRIX);
        }
        updateUI(GMATRIX);
        updateGhost(CURRENT_FALLING_BRICK, GMATRIX);
    }, START_INTERVAL - LEVEL * 50);
    
    window.setTimeout(function() {
        updateGhost(brick, GMATRIX);
    }, 10);
}

function updateTextUI()
{
    $("#level").html(LEVEL + 1);

    $("#points").html(formatPoints(POINTS));
    
    $("#nextbrick1").html("");
    if (NEXT_BRICK_1 != null)
    {
        $("#nextbrick1").append(NEXT_BRICK_1.getDOMElement());
    }
    
    $("#nextbrick2").html("");   
    if (NEXT_BRICK_2 != null)
    {
        $("#nextbrick2").append(NEXT_BRICK_2.getDOMElement());
    }
}

function formatPoints(points)
{
    var pointStr = "" + points;

    if (points < 100000)
    {
        pointStr = "0" + pointStr;
    }
    if (points < 10000)
    {
        pointStr = "0" + pointStr;
    }
    if (points < 1000)
    {
        pointStr = "0" + pointStr;
    }
    if (points < 100)
    {
        pointStr = "0" + pointStr;
    }
    if (points < 10)
    {
        pointStr = "0" + pointStr;
    }

    return pointStr.substring(0, 3) + "," + pointStr.substring(3);
}

function updateUI(GMATRIX)
{
    var counter = 0, cells = $("#gamepanel .cell").get();
    for (var r = 0;r < GMATRIX.length;r++)
    {
        for (var c = 0;c < GMATRIX[r].length;c++)
        {
            $(cells[counter++]).attr("class",
                "cell" + (GMATRIX[r][c] != 0? " " + GMATRIX[r][c] : ""));
        }
    }
}

function updateGhost(forBrick, GMATRIX)
{
    $("#ghost-overlay").html("");
    if (!SHOW_GHOST || !forBrick)
    {
        return;
    }
    var gBrick = forBrick.clone();
    gBrick.setGhostBrick(true);
    
    // fall down.
    while (gBrick.tryMoveDown(1, GMATRIX));
    
    $("#ghost-overlay").append(gBrick.getDOMElement());
}

function nextBrick()
{
    window.clearTimeout(FALLING_TIMEOUT);
    
    $("#play-overlay, #ghost-overlay").html("");
    
    var nextFalling = NEXT_BRICK_1;
    nextFalling.setPosition(Math.round(5 - nextFalling.getWidth() / 2), 0);
    
    NEXT_BRICK_1 = NEXT_BRICK_2;
    NEXT_BRICK_2 = getNewBrickByType(BRICK_SEQUENCE_GENERATOR.nextType());
    updateTextUI();
    
    setFallingBrick(nextFalling);
}

function showMessage(heading, content, callback)
{
    var d = document.createElement("div");
    $(d).addClass("message").css("opacity", 0);
    
    $("#message-overlay").html("").show().append(d);
    
    var h = 0;
    if (heading)
    {
        var h1 = document.createElement("h1");
        $(h1).html(heading);
        d.appendChild(h1);
        h += $(h1).height();
    }

    if (content)
    {
        if (typeof content == "string")
        {
            var _c = document.createElement("p");
            $(_c).html(content);
            content = _c;
        }
        $(content).addClass("content");
        d.appendChild(content);
        h += $(content).height();
    }
    $(d).animate({opacity: 1}, START_INTERVAL, "easeInCubic");
    $(h1).animate({paddingTop: $(d).height() / 2 - h / 2}, START_INTERVAL, "easeOutCubic");
    $("#effect-overlay").hide();
    
    $("#message-overlay").find("input, button, a[href]").each(function(i, e) {
        $(this).attr("tabindex", i + 1);
    });
    
    if (callback != undefined)
    {
        window.setTimeout(callback, START_INTERVAL);
    }
}

function hideMessage(removeContent, callback)
{
    $("#message-overlay").fadeOut(START_INTERVAL, "easeOutCubic").find("h1")
        .animate({paddingTop: 0}, START_INTERVAL, "easeInCubic");
    $("#effect-overlay").show();

    if (removeContent === undefined || removeContent == true)
    {
        window.setTimeout(function() {
            $("#message-overlay").html("");
            
            if (callback)
            {
                callback();
            }
        }, START_INTERVAL + 10);
    }
    else
    {
        window.setTimeout(function() {
            if (callback)
            {
                callback();
            }
        }, START_INTERVAL + 10);
    }
}

var effectTextQueue = new HandlingQueue(function(effectText, resolve) {
    var effectLabel = document.createElement("h1");
    effectLabel.innerText = effectText;
    effectLabel.style.lineHeight = (19 * EM1) + "px";

    $("#effect-overlay").html("").append(effectLabel);
    $(effectLabel).css({fontSize: 0.5 * EM1, opacity: 0.8})
            .animate({fontSize: 2.4 * EM1, opacity: 0}, 1000);


    window.setTimeout(function() {
        $("#effect-overlay").html("");
        resolve();
    }, 1000);
});
function showEffectText(effectText)
{
    effectTextQueue.pushItem(effectText);
}

function showHighscorePage(nPage)
{
    $.ajax({
        url: "/highscore.php",
        dataType: "JSON",
        data: {
            page: nPage
        },
        success: function(data)
        {
            showHighscores(data);
        },
        error: function(req, error)
        {
            SERVER_ERROR(error);
        }
    });
}

function showHighscores(data)
{
    if (data == undefined)
    {   
        showHighscorePage(1);
        return;
    }
    
    var perPage = 13;
    history.pushState(null, null, '/scores/' + data.pageN);
    
    var container1 = document.createElement("div"),
        container2 = document.createElement("div"),
        hstable = document.createElement("table");
    $(container1).addClass("content graved");
    $(container2).addClass("highscores");
    hstable.cellSpacing = 0;
    hstable.cellPadding = 0;

    var tr = document.createElement("tr");
    var td = document.createElement("th");
    td.innerHTML = "#";
    tr.appendChild(td);

    td = document.createElement("th");
    td.innerHTML = "Name";
    tr.appendChild(td);

    td = document.createElement("th");
    td.innerHTML = "Score";
    tr.appendChild(td);

    hstable.appendChild(tr);

    $.each(data.scores, function(index, entry) {
        tr = document.createElement("tr");

        if (data.highlight.indexOf(index) != -1)
        {
            tr.className = "highlighted";
        }

        td = document.createElement("td");
        td.innerHTML = "" + ((data.pageN - 1) * perPage + index + 1);
        tr.appendChild(td);

        td = document.createElement("td");
        td.innerHTML = entry.name;
        tr.appendChild(td);

        td = document.createElement("td");
        $(td).html(formatPoints(entry.score)).addClass("last");
        tr.appendChild(td);

        hstable.appendChild(tr);
    });
    
    var hasNextPage = data.scores.length >= perPage;
    var hasPrevPage = data.pageN > 1;
    
    // fill empty places in the highscore
    for (var i = 0;i < perPage - data.scores.length;i++)
    {
        tr = document.createElement("tr");

        td = document.createElement("td");
        td.innerHTML = "" + ((data.pageN - 1) * perPage + data.scores.length + i + 1);
        tr.appendChild(td);

        td = document.createElement("td");
        tr.appendChild(td);

        td = document.createElement("td");
        tr.appendChild(td);

        hstable.appendChild(tr);
    }
    
    container2.appendChild(hstable);
    container1.appendChild(container2);
    
    container1.appendChild(getButton("show-start"));
    container1.appendChild(getButton("replay"));
    
    if (hasPrevPage) {
        var bt = document.createElement("div");
        $(bt).addClass("imgbutton prevpage").css("height", 1.2 * EM1).click(function() {
            showHighscorePage(data.pageN - 1);
        });
        bt.title = "previous page";
        container1.appendChild(bt);
    }
    
    if (hasNextPage) {
        var bt = document.createElement("div");
        $(bt).addClass("imgbutton nextpage").css("height", 1.2 * EM1).click(function() {
            showHighscorePage(data.pageN + 1);
        });
        bt.title = "next page";
        container1.appendChild(bt);
    }
    
    var br = document.createElement("br");
    br.style.clear = "both";
    container1.appendChild(br);
    
    br = document.createElement("br");
    br.style.clear = "both";
    container1.appendChild(br);

    showMessage("SCORE", container1);
}

function START_GAME()
{
    POINTS = 0;
    LEVEL = 0;
    PAUSED = false;
    // reset NEXT_BRICK_1 and NEXT_BRICK_2 as they will be overwritten
    // by the server-forced brick sequence
    NEXT_BRICK_1 = null;
    NEXT_BRICK_2 = null;
    TURN_QUEUE = new TransactionQueue();
    N_PLACED_BRICKS = 0;
    updateTextUI();
    
    GMATRIX = [];
    
    for (var r = 0;r <= HEIGHT;r++)
    {
        GMATRIX[r] = [];
        for (var c = 0;c < WIDTH;c++)
        {
            GMATRIX[r][c] = 0;
        }
    }
    
    updateUI(GMATRIX);
    
    hideMessage(true, function() {
        showMessage(' ', 'Connecting to game server<span id="startGameProgress"></span>');
    
        var gameSaltLoaded = false,
            gameSaltLoadingCounter = 0;
        var gameSaltLoadingAnimationInterval = window.setInterval(function() {
            if (gameSaltLoaded == true && gameSaltLoadingCounter > 2)
            {
                window.clearInterval(gameSaltLoadingAnimationInterval);
                
                NEXT_BRICK_1 = getNewBrickByType(BRICK_SEQUENCE_GENERATOR.nextType());
                NEXT_BRICK_2 = getNewBrickByType(BRICK_SEQUENCE_GENERATOR.nextType());
                updateTextUI();
                
                hideMessage(true, function() {
                    showEffectText("START!");
                        window.setTimeout(function() {
                        nextBrick();
                    }, 500);
                });                
            }
            else
            {
                $("#startGameProgress").html('.'.repeat(++gameSaltLoadingCounter % 4));
            }
        }, 500);
        
        // notify the server and obtain the game ID
        $.ajax({
            url: '/game/start',
            dataType: 'JSON',
            success: function(data) {
                CURRENT_GAME_ID = data.id;
                BRICK_SEQUENCE_GENERATOR = new BrickSequenceGenerator(new DPRNG(data.brickSequenceGenerationSalt));
                
                gameSaltLoaded = true; // this will end the animation and initiate the game
            },
            error: function() {
                SERVER_ERROR("0x03");
                window.clearInterval(gameSaltLoadingAnimationInterval);
            }
        });
    });
}

function GAME_OVER()
{
    window.clearTimeout(FALLING_TIMEOUT);
    CURRENT_FALLING_BRICK = null;
    updateUI(GMATRIX);

    // sync all the turns that are left and then
    // pause the game server-side so that the time counter does not count the time it takes
    // the user to enter his name and submit the form
    var turnsSyncedAndGamePaused = syncTurnQueue().then(function() {
        return new Promise(function(resolve, reject) {
            $.ajax({
                url: '/game/' + CURRENT_GAME_ID + '/pause',
                complete: resolve
            });
        });
    });

    // construct the submit-screen
    showMessage('GAME OVER', $("#template-submit").html());

    // game-over!
    $("#effect-overlay").css("display", "none");

    // focus the name input field
    $("#message-overlay input:first-of-type").focus();

    // register submit handler
    $("#message-overlay .submit-button").click(function() {
        var $input = $(this).parent().find('input[type="text"]');
        $input.prop("disabled", true).addClass("disabled");

        // do the commit only after the turns have been synced.
        turnsSyncedAndGamePaused.then(function() {
            $.ajax({
                url: "/game/" + CURRENT_GAME_ID + "/commit",
                type: "POST",
                dataType: "JSON",
                data: {
                    name: $input.val()
                },
                success: function(data)
                {
                    showHighscores(data);
                },
                error: function(req)
                {
                    SERVER_ERROR("0x02");
                }
            });
        });
    });
}

function PAUSE()
{
    if (!PAUSED)
    {
        window.clearInterval(FALLING_TIMEOUT);
        FALLING_TIMEOUT = null;
        PAUSED = true;

        AUDIO.THEME.pause();
        showMessage("PAUSE", "The game is paused. Resume with <div class='key "+
            getActionUIRepresentation(ACTION.PAUSE_RESUME) + "'><span>" +
            getActionUIRepresentation(ACTION.PAUSE_RESUME).toUpperCase() + '</span></key>'
        );
        document.title = "Tetris - PAUSED";

        // tell the server!
        $.ajax({
            url: '/game/' + CURRENT_GAME_ID + '/pause'
        });
    }
}

function RESUME()
{
    hideMessage();
    
    document.title = "Tetris";
    
    AUDIO.THEME.play();
    
    PAUSED = false;

    $.ajax({
        url: '/game/' + CURRENT_GAME_ID + '/pause'
    });
	
    if (CURRENT_FALLING_BRICK == null)
    {
        nextBrick();
    }
    else
    {
        setFallingBrick(CURRENT_FALLING_BRICK);
    }
}

function SERVER_ERROR(code)
{
    showMessage("Error", "Sorry, the server messed something up :(<br><br>Code " + code);
    window.clearTimeout(FALLING_TIMEOUT);
    PAUSED = true;
}

function removeFullLines(callback, showEffect)
{
    var removedLines = 0;
    for (var r = 0;r < GMATRIX.length;r++)
    {
        var curRowAll = true,
            curRowOne = false;
        for (var c = 0;c < GMATRIX[r].length;c++)
        {
            curRowAll = curRowAll && (GMATRIX[r][c] != 0);
            curRowOne = curRowOne || (GMATRIX[r][c] != 0);
        }
        if (curRowAll && curRowOne)
        {
            removeRow(GMATRIX, r);
            removedLines++;
        }
    }
    
    if (removedLines == 0)
    {
        callback();
    }
    else
    {
        window.setTimeout(callback, 100);
    
        if ((showEffect == undefined || showEffect) && removedLines > 1)
        {
            showEffectText("x" + removedLines);
        }
    }
    
    return removedLines;
}

function addCells(n)
{
    var cell,
        gp = $("#gamepanel");
    for (var i = 0;i < n;i++)
    {
        cell = document.createElement("div");
        $(cell).addClass("cell");
        gp.prepend(cell);
    }
}

function removeRow(GMATRIX, row, callback)
{
    // get the cells dom
    var cells = new Array(),
        allCells = $("#gamepanel .cell").get();
    
    for (var i = 0;i < WIDTH;i++)
    {
        cells.push(allCells[row * WIDTH + i]);
    }
    
    var a = $(cells);
    a.addClass("pendingRemoval").fadeOut(100);
    
    window.setTimeout(function() {
        addCells(10);
        a.remove();
        
        var buffer = new Array();
        for (var i = 0;i < row;i++)
        {
            buffer[i] = new Array();
            for (var c = 0;c < GMATRIX[i].length;c++)
            {
                buffer[i][c] = GMATRIX[i][c];
            }
        }
        
        for (var i = 1;i <= buffer.length;i++)
        {
            for (var c = 0;c < GMATRIX[i].length;c++)
            {
                GMATRIX[i][c] = buffer[i - 1][c];
            }
        }
        
        for (var c = 0;c < GMATRIX[0].length;c++)
        {
            GMATRIX[0][c] = 0;
        }
        
        if (callback != undefined)
        {
            callback();
        }
    }, 100); 
}

/**
 * Executes the given action (does NOT include the exaggeration feature!)
 */
function executeUserAction(action) {
	if (CURRENT_FALLING_BRICK == null)
	{
        return;
	}

	if (action == ACTION.HARD_DROP)
	{
        window.clearInterval(FALLING_TIMEOUT);

        // how long will it fall?
        do
        {
                CURRENT_FALLING_BRICK.moveDown(1);
        }
        while (!CURRENT_FALLING_BRICK.collides(GMATRIX));
        CURRENT_FALLING_BRICK.moveUp(1);

        applyBrick(CURRENT_FALLING_BRICK, GMATRIX);
	}
	else if (action == ACTION.MOVE_LEFT)
	{
        CURRENT_FALLING_BRICK.tryMoveLeft(1, GMATRIX);
	}
	else if (action == ACTION.MOVE_RIGHT)
	{
        CURRENT_FALLING_BRICK.tryMoveRight(1, GMATRIX);
	}
	else if (action == ACTION.SOFT_DROP)
	{
        CURRENT_FALLING_BRICK.tryMoveDown(1, GMATRIX);
	}
	else if (action == ACTION.ROTATE_RIGHT || action == ACTION.ROTATE_LEFT)
	{
        if (action == ACTION.ROTATE_RIGHT)
        {
            CURRENT_FALLING_BRICK.rotateRight();
        }
        else
        {
            CURRENT_FALLING_BRICK.rotateLeft();
        }

        // check whether the block hits the right edge
        if (CURRENT_FALLING_BRICK.getPosition().x + CURRENT_FALLING_BRICK.getWidth() > WIDTH)
        {
            var blocksMovedLeft = 0;

            do
            {
                CURRENT_FALLING_BRICK.moveLeft(1);
                blocksMovedLeft++;
            }
            while (CURRENT_FALLING_BRICK.getPosition().x + CURRENT_FALLING_BRICK.getWidth() > WIDTH);

            if (CURRENT_FALLING_BRICK.collides(GMATRIX))
            { // rotate not possible, rewind

                if (blocksMovedLeft > 0)
                {
                    CURRENT_FALLING_BRICK.moveRight(blocksMovedLeft);
                }

                if (action == ACTION.ROTATE_RIGHT)
                {
                    CURRENT_FALLING_BRICK.rotateLeft();
                }
                else
                {
                    CURRENT_FALLING_BRICK.rotateRight();
                }
            }
        }
        else if (CURRENT_FALLING_BRICK.collides(GMATRIX))
        { // rotate not possible, rewind
            if (action == ACTION.ROTATE_RIGHT)
            {
                CURRENT_FALLING_BRICK.rotateLeft();
            }
            else
            {
                CURRENT_FALLING_BRICK.rotateRight();
            }
        }
	}
	else if (action == ACTION.PAUSE_RESUME)
	{
        if (FALLING_TIMEOUT == null)
        {
            RESUME();
        }
        else
        {
            PAUSE();
        }
	}
	
	updateGhost(CURRENT_FALLING_BRICK, GMATRIX);
}

function getButton(type)
{
    switch (type)
    {
        case "replay":
            var bt = document.createElement("div");
            $(bt).addClass("imgbutton replay").css("height", 1.2 * EM1).click(function() {
                START_GAME();
            });
            bt.title = "play again";
            return bt;
        case "show-start":
            var bt = document.createElement("div");
            $(bt).addClass("imgbutton showstart").css("height", 1.2 * EM1).click(function() {
                showStart();
            });
            bt.title = "show start screen";
            return bt;
    }
}

$(document).ready(function() {
    // initially set 1em
    updateSizes();
    
    // fill the gamepanel
    addCells(WIDTH * HEIGHT);
    
    // key listener
    var brickSwipeStartXPos = 0;
        
    $(document).keydown(function(evt)
    {
        var action = getAction(evt);
		
        dispatchAction(action);
    }).swipe({
        swipeStatus:function(event, phase, direction, distance , duration , fingerCount) {
            if (PAUSED || !(CURRENT_FALLING_BRICK))
                return;
            
            if (direction == $.fn.swipe.directions.LEFT || direction == $.fn.swipe.directions.RIGHT)
            {
                if (phase === $.fn.swipe.phases.PHASE_START)
                {
                    brickSwipeStartXPos = CURRENT_FALLING_BRICK.getPosition().x;
                    console.log("startpos %o", brickSwipeStartXPos);
                }
                
                

                if(phase === $.fn.swipe.phases.PHASE_MOVE)
                {
                    distance = direction == $.fn.swipe.directions.LEFT? -distance : distance;

                    var newXPos = brickSwipeStartXPos + Math.round(distance / EM1);

                    console.log(newXPos);

                    var oldPosition = CURRENT_FALLING_BRICK.getPosition();
                    
                    CURRENT_FALLING_BRICK.setPosition(
                        newXPos,
                        oldPosition.y
                    );
            
                    if (CURRENT_FALLING_BRICK.collides(GMATRIX))
                    {
                        CURRENT_FALLING_BRICK.setPosition(oldPosition.x, oldPosition.y);
                    }
                    else
                    {
                        updateGhost(CURRENT_FALLING_BRICK, GMATRIX);
                    }
                }
            }
            else if (phase == $.fn.swipe.phases.PHASE_END)
            {
                if (direction == $.fn.directions.UP)
                {
                    dispatchAction(ACTION.ROTATE_RIGHT);
                }
                else if (direction == $.fn.directions.DOWN)
                {
                    dispatchAction(ACTION.HARD_DROP);
                }
            }
        },
        pinchStatus: function(event, phase, direction, distance , duration , fingerCount, pinchZoom) {
            if(phase === $.fn.swipe.phases.PHASE_END)
            {
                var action = KEY_CONFIG["pinch-" + direction];

                if (action)
                {
                    dispatchAction(action);
                }
            }
        },
        threshold: 2 * EM1
    });
    
    $("#effect-overlay").hide();
    
    $("#start-button").click(function() {
		var $bt = $(this);
        $bt.prop("disabled", true);
        
        hideMessage();
        window.setTimeout(function() {
            $("#effect-overlay").show();
            START_GAME();
			$bt.prop("disabled", false);
        }, 700);
    });
    
    AUDIO.THEME = document.getElementById("audio-theme");
    
    // setup settings
    $.settings.getURL = "/settings.php";
    $.settings.setURL = "/settings.php";
    
    $("#options #button-mute").on('click', function() {
        if (AUDIO.MUTED)
        {
            AUDIO.setMuted(false);
        }
        else
        {
            AUDIO.setMuted(true);
        }
    });
    $.settings.onSettingSet("audio.muted", function(s_name, is) {
        $("#options #button-mute").addClass("disabled");
        
        if (is != "true")
        {
            $("#options #button-mute").removeClass("disabled");
        }
    });
    var b = new TBrick();
    b.rotateRight();
    $("#options #button-ghost").click(function() {
        if (SHOW_GHOST)
        {
            $.settings("ghost", "false");
        }
        else
        {
            $.settings("ghost", "true");
        }
    }).append(b.getDOMElement());
    $.settings.onSettingSet("ghost", function(s_name, is) {
        SHOW_GHOST = is == "true";
        $("#options #button-ghost").addClass("disabled");
        if (SHOW_GHOST)
        {
            $("#options #button-ghost").removeClass("disabled");
        }
        
        updateGhost(CURRENT_FALLING_BRICK, GMATRIX);
    });
    
    // load settings
    AUDIO.setMuted($.settings("audio.muted") != "false");
    GHOST = $.settings("ghost") == "true";
    if (!GHOST)
    {
        $("#settings #button-ghost").addClass("disabled");
    }
	
	// set the default key config
	var keyConfig = $.settings("keyconfig");
	KEY_CONFIG.setType(keyConfig? keyConfig : "tobsetetris");
});

function showStart()
{
    showMessage('TETRIS', $("#template-start").html());
    history.pushState(null, null, '/');
}

function showOptions()
{
    showMessage('KEYS', $("#template-options").html(), function() {
        $("#control-scheme-switcher span").click(function() {
            KEY_CONFIG.setType($(this).attr("rel"));
        });
    });
    
    history.pushState(null, null, '/keyconfig');
}

function showCredits()
{
    history.pushState(null, null, '/credits');
    showMessage('CREDITS', $("#template-credits").html());
}

function syncTurnQueue()
{
    if (CURRENT_GAME_ID != null && TURN_QUEUE.hasItems())
    {
        return TURN_QUEUE.consume(20, function(bricksToSubmit, resolve, reject) {
            var postData = {};

            for (var i = 0;i < bricksToSubmit.length;i++)
            {
                var brick = bricksToSubmit[i];
                postData["brickType" + i] = brick.getType();
                postData["brickX" + i] = brick.getPosition().x;
                postData["brickY" + i] = brick.getPosition().y;
                postData["brickRotation" + i] = brick.getRotation() * 90;
            }

            $.ajax({
                url: '/game/' + CURRENT_GAME_ID + '/turn',
                type: 'POST',
                dataType: 'JSON',
                data: postData,
                success: function(data) {
                    resolve(data.score);
                },
                error: function(req) {
                    reject(req);
                }
            });
        });
    }
    else
    {
        return new Promise(function(resolve) {
            resolve(POINTS);
        });
    }
}

~function() {
    // always holds the previous action
    var previousAction = null;
    // holds the unix timestamp of the previous action (in milliseconds)
    var previousActionAt = null;
    dispatchAction = function(action) {
        // when paused, ignore all input other than resume
        if (PAUSED && action != ACTION.PAUSE_RESUME)
        {
            return;
        }

        // Action exaggeration
        if (action == previousAction && (new Date()).getTime() - previousActionAt <= ACTION_EXAGGERATE_TIMEOUT)
        {
            if (EXAGGERATE_SUPPORTED_ACTIONS.indexOf(action) != -1)
            {
                executeUserAction(action);
            }
        }

        executeUserAction(action);

        // set the previous action
        previousAction = action;
        previousActionAt = (new Date()).getTime();
    };
    
    // update the right panel, including the next bricks
    updateTextUI();
    
    // start the server-sync job
    var syncPeriod = 10000; // sync every x milliseconds
    var syncFn = function() {
        if (PAUSED) {
            window.setTimeout(syncFn, syncPeriod);
            return;
        }

        var startedAt = (new Date()).getTime();
        syncTurnQueue().then(function(points) {
            if (!TURN_QUEUE.hasItems())
            {
                POINTS = points;
                updateTextUI();
            }
            
            var doneAt = (new Date()).getTime();
            window.setTimeout(syncFn, syncPeriod - ((doneAt - startedAt) % syncPeriod));
        }, function(reason) {
            console.log(reason);
            SERVER_ERROR("0x04");
        });
    };
    window.setTimeout(syncFn, 5000);
    
    // window focus/blur handler
    var gamePausedBecauseWindowBlur = false;
    window.onblur = function() {
        if (!PAUSED && CURRENT_FALLING_BRICK != null) // if CURRENT_FALLING_BRICK is null and not paused => game over
        {
            gamePausedBecauseWindowBlur = true;
            PAUSE();
        }
    };
    window.onfocus = function() {
        if (gamePausedBecauseWindowBlur)
        {
            gamePausedBecauseWindowBlur = false;
            RESUME();
        }
    };
}();
