Brick = function(stoneMatrix0deg, stoneMatrix90deg, stoneMatrix180deg,
    stoneMatrix270deg, color) {
    
    this.color = color == undefined? "blue" : color;
    this.dom = document.createElement("div");
    this.stoneMatrix = null;
    
    var matrices = [stoneMatrix0deg, stoneMatrix90deg, stoneMatrix180deg,
        stoneMatrix270deg],
        rotation = 0;
    
    this.updateUI = function(stoneMatrix)
    {
        this.stoneMatrix = stoneMatrix;
        this.dom.innerHTML = "";
        var $dom = $(this.dom);
        $dom.addClass("brick").addClass(color).css({
            width: stoneMatrix[0].length + ".01em",
            height: stoneMatrix.length + ".01em"
        });
        for (var r = 0;r < stoneMatrix.length;r++)
        {
            var stone;
            for (var c = 0;c < stoneMatrix[r].length;c++)
            {
                stone = document.createElement("span");
                $(stone).addClass("stone");
                if (stoneMatrix[r][c] == 0)
                {
                    $(stone).addClass("hidden");
                }

                this.dom.appendChild(stone);
            }
        }
    };
    
    this.updateUI(stoneMatrix0deg);
    
    this.x = 0;
    this.y = 0;
    
    this.setColor = function(color)
    {
        $(this.dom).removeClass(this.color).addClass(color);
        this.color = color;
    };
    this.getWidth = function() {
        return this.stoneMatrix[0].length;
    };
    this.getHeight = function() {
        return this.stoneMatrix.length;
    };
    this.getDOMElement = function() {
        return this.dom;
    };
    this.setPosition = function(x, y) {
        this.x = x;
        this.y = y;
        $(this.dom).css({marginTop: y + "rem", marginLeft: x + "rem"});
    };
    this.setPositionAnimated = function(x, y)
    {
        this.x = x;
        this.y = y;
        $(this.dom).animate({marginTop: y + "rem", marginLeft: x + "rem"}, START_INTERVAL - LEVEL * 50);
    };
    this.moveUp = function(n, animated)
    {
        // this.setPositionAnimated(this.x, this.y - n);
        this.setPosition(this.x, this.y - n);
    };
    this.moveDown = function(n)
    {
        // this.setPositionAnimated(this.x, this.y + n);
        this.setPosition(this.x, this.y + n);
    };
    this.moveLeft = function(n)
    {
        // this.setPositionAnimated(this.x - n, this.y);
        this.setPosition(this.x - n, this.y);
    };
    this.moveRight = function(n)
    {
        // this.setPositionAnimated(this.x + n, this.y);
        this.setPosition(this.x + n, this.y);
    };
    this.tryMoveDown = function(n, GMATRIX)
    {
        this.y += n;
        var s = this.collides(GMATRIX);
        this.y -= n;
        
        if (s)
        {
            return false;
        }
        else
        {
            this.moveDown(n);
            return true;
        }
    };
    this.tryMoveLeft = function(n, GMATRIX)
    {
        this.x -= n;
        var s = this.collides(GMATRIX);
        this.x += n;
        
        if (s)
        {
            return false;
        }
        else
        {
            this.moveLeft(n);
            return true;
        }
    };
    this.tryMoveRight = function(n, GMATRIX)
    {
        this.x += n;
        var s = this.collides(GMATRIX);
        this.x -= n;
        
        if (s)
        {
            return false;
        }
        else
        {
            this.moveRight(n);
            return true;
        }
    };
    this.getPosition = function()
    {
        return {x: this.x, y: this.y};
    };
    this.rotateRight = function()
    {
        this.setRotation(rotation - 1);
    };
    this.rotateLeft = function()
    {
        this.setRotation(rotation + 1);
    };
    this.setRotation = function(r)
    {
        if (r >= 0)
        {
            rotation = r % matrices.length;
        }
        else
        {
            if (r <= -matrices.length)
            {
                r = r % matrices.length;
            }
            rotation = matrices.length + r;
        }
        this.updateUI(matrices[rotation]);
    };
    this.getRotation = function()
    {
        return rotation;
    };
    this.setGhostBrick = function(is) {
        if (!is)
        {
            $(this.dom).removeClass("ghost");
        }
        else
        {
            $(this.dom).addClass("ghost");
        }
    };
    this.isGhostBrick = function() {
        return $(this.dom).hasClass("ghost");
    };
    this.collides = function(GMATRIX)
    {
        /*alert(this.x + " => " + (this.x + this.getWidth()) + ", "
            + this.y + " => " + (this.y + this.getHeight()));*/
        if (this.x < 0 || this.y < 0 ||
            GMATRIX.length <= this.y + this.getHeight() ||
            GMATRIX[0].length < this.x + this.getWidth())
        {
            // alert("exceeds bounds at " + this.x + "x" + this.y);
            return true;
        }
        for (var r = 0;r < this.stoneMatrix.length;r++)
        {
            for (var c = 0;c < this.stoneMatrix[0].length;c++)
            {
                if (this.stoneMatrix[r][c] == 1)
                { // do we need to check this stone?
                    if (GMATRIX[r + this.y][c + this.x] != 0)
                    {
                        // alert("Collides at: " + (r + this.y) + "x" + (c + this.x));
                        return true;
                    }
                }
            }
        }
        return false;
    }
    this.applyTo = function(GMATRIX)
    {
        if (this.collides(GMATRIX))
        {
            return;
        }
        for (var r = 0;r < this.stoneMatrix.length;r++)
        {
            for (var c = 0;c < this.stoneMatrix[r].length;c++)
            {
                if (this.stoneMatrix[r][c] == 1)
                {
                    GMATRIX[r + this.y][c + this.x] = this.color;
                }
            }
        }
        $(this.dom).remove();
    };
    this.clone = function()
    {
        var c = new Brick(stoneMatrix0deg, stoneMatrix90deg, stoneMatrix180deg,
            stoneMatrix270deg, this.color);
        c.setRotation(rotation);
        c.setPosition(this.x, this.y);
        c.setGhostBrick(this.isGhostBrick());
        c.getType = this.getType;
        return c;
    }
};
IBrick = function() {
    Brick.apply(this, [[
        [1],
        [1],
        [1],
        [1]
    ], [
        [1, 1, 1, 1]
    ], [
        [1],
        [1],
        [1],
        [1]
    ], [
        [1, 1, 1, 1]
    ], "turquoise"]);

    this.getType = function()
    {
        return "i";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};
LBrick = function() {
    Brick.apply(this, [[
        [1, 0],
        [1, 0],
        [1, 1]
    ], [
        [0, 0, 1],
        [1, 1, 1]
    ], [
        [1, 1],
        [0, 1],
        [0, 1]
    ], [
        [1, 1, 1],
        [1, 0]
    ], "orange"]);

    this.getType = function()
    {
        return "l";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};
RevLBrick = function() {
    Brick.apply(this, [[
        [0, 1],
        [0, 1],
        [1, 1]
    ], [
        [1, 1, 1],
        [0, 0, 1]
    ], [
        [1, 1],
        [1, 0],
        [1, 0]
    ], [
        [1, 0, 0],
        [1, 1, 1]
    ], "blue"]);

    this.getType = function()
    {
        return "rl";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};
TBrick = function() {
    Brick.apply(this, [[
        [0, 1, 0],
        [1, 1, 1]
    ], [
        [0, 1],
        [1, 1],
        [0, 1]
    ], [
        [1, 1, 1],
        [0, 1, 0]
    ], [
        [1, 0],
        [1, 1],
        [1, 0]
    ], "magenta"]);

    this.getType = function()
    {
        return "t";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};
ZBrick = function() {
    Brick.apply(this, [[
        [1, 1, 0],
        [0, 1, 1]
    ], [
        [0, 1],
        [1, 1],
        [1, 0]
    ], [
        [1, 1, 0],
        [0, 1, 1]
    ], [
        [0, 1],
        [1, 1],
        [1, 0]
    ], "red"]);

    this.getType = function()
    {
        return "z";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};
SBrick = function() {
    Brick.apply(this, [[
        [0, 1, 1],
        [1, 1, 0]
    ], [
        [1, 0],
        [1, 1],
        [0, 1]
    ], [
        [0, 1, 1],
        [1, 1, 0]
    ], [
        [1, 0],
        [1, 1],
        [0, 1]
    ],  "green"]);

    this.getType = function()
    {
        return "s";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};
SquareBrick = function() {
    Brick.apply(this, [[
        [1, 1],
        [1, 1]
    ], [
        [1, 1],
        [1, 1]
    ], [
        [1, 1],
        [1, 1]
    ], [
        [1, 1],
        [1, 1]
    ], "yellow"]);

    this.getType = function()
    {
        return "square";
    };
    
    $(this.dom).addClass("brick-" + this.getType());
};

function getRandomBrick()
{
    var r = Math.floor(Math.random() * 7);
    if (r == 0)
    {
        return new IBrick();
    }
    else if (r == 1)
    {
        return new LBrick();
    }
    else if (r == 2)
    {
        return new RevLBrick();
    }
    else if (r == 3)
    {
        return new ZBrick();
    }
    else if (r == 4)
    {
        return new SBrick();
    }
    else if (r == 5)
    {
        return new TBrick();
    }
    else if (r == 6)
    {
        return new SquareBrick();
    }
}

function getNewBrickByType(type)
{
    if (type == "i")
    {
        return new IBrick();
    }
    else if (type =="l")
    {
        return new LBrick();
    }
    else if (type == "rl")
    {
        return new RevLBrick();
    }
    else if (type == "z")
    {
        return new ZBrick();
    }
    else if (type == "s")
    {
        return new SBrick();
    }
    else if (type == "t")
    {
        return new TBrick();
    }
    else if (type == "square")
    {
        return new SquareBrick();
    }
}

var BRICK_TYPES = ["i", "l", "rl", "s", "square", "t", "z"];

var BrickSequenceGenerator = function(rng)
{		
    if (!rng)
    {
        rng = new DPRNG();
    }

    /**
     * The previous brick type
     * @var string
     */
    this.previous = null;

    /**
     * The pre-previous brick type
     * @var string
     */
    this.prePrevious = null;

    /**
     * Returns a random brick-type as per self::$types and $this->rng
     * @return string
     */
    var randomBrickType = function() {
        return BrickSequenceGenerator.TYPES[
            rng.nextInt(0, BrickSequenceGenerator.TYPES.length - 1)
        ];
    };

    /**
     * Returns the next brick-type in sequence
     * @return string
     */
    this.nextType = function() {
        var type = null;

        if (this.previous == null)
        {
            // first type: never spawn s, z or square first
            do
            {
                type = randomBrickType();
            }
            while (["s", "z", "square"].indexOf(type) != -1);
        }
        else
        {
            do
            {
                type = randomBrickType();
            }
            while (this.previous == type || this.prePrevious == type);
            
            this.prePrevious = this.previous;
        }
        
        this.previous = type;
        return type;
    };
};
BrickSequenceGenerator.TYPES = BRICK_TYPES;