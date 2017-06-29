/**
 * Author: Tobias Marstaller
 */

function getKeyByKeyCode(keyCode)
{
    var keys = new Array();
    keys[65] = "a";keys[66] = "b";keys[67] = "c";keys[68] = "d";keys[69] = "e";
    keys[70] = "f";keys[71] = "g";keys[72] = "h";keys[73] = "i";keys[74] = "j";
    keys[75] = "k";keys[76] = "l";keys[77] = "m";keys[78] = "n";keys[79] = "o";
    keys[80] = "p";keys[81] = "q";
    keys[82] = "r";keys[83] = "s";keys[84] = "t";keys[85] = "u";keys[86] = "v";
    keys[87] = "w";keys[88] = "x";keys[89] = "y";keys[90] = "z";keys[192] = "ö";
    keys[222] = "ä";keys[59] = "ü";
    keys[49] = "1";keys[50] = "2";keys[51] = "3";keys[52] = "4";keys[53] = "5";
    keys[54] = "6";keys[55] = "7";keys[56] = "8";keys[57] = "9";keys[48] = "0";
    keys[220] = "^";keys[219] = "?";keys[221] = "´";
    keys[27] = "esc";keys[112] = "F1";keys[113] = "F2";keys[114] = "F3";
    keys[115] = "F4";keys[116] = "F5";keys[117] = "F6";keys[118] = "F7";
    keys[119] = "F8";keys[120] = "F9";keys[121] = "F10";keys[122] = "F11";
    keys[123] = "F12"
    keys[8] = "back";keys[9] = "tab";keys[13] = "enter";keys[20] = "caps";
    keys[16] = "shift";keys[107] = "*";keys[191] = "#";keys[226] = "<";
    keys[188] = ",";keys[190] = ".";keys[109] = "-";keys[17] = "strg";
    keys[18] = "alt";
    keys[93] = "list";
    keys[92] = "wdr";keys[91] = "wdl";keys[145] = "roll";keys[19] = "break";
    keys[45] = "paste";keys[36] = "pos1";keys[33] = "bup";keys[34] = "bd";
    keys[35] = "end";keys[46] = "del";
    keys[37] = "left";keys[39] = "right";keys[40] = "down";keys[38] = "up";
    keys[144] = "num";keys[111] = "/";keys[106] = "*";keys[107] = "+";
    keys[110] = ",del";
    keys[103] = "num7";keys[104] = "num8";keys[105] = "num9";
    keys[100] = "num4";keys[101] = "num5";keys[102] = "num6";
    keys[97] = "num1";keys[98] = "num2";keys[99] = "num3";
    keys[33] = "num09";keys[12] = "num05";keys[173] = "snd";
    keys[174] = "vol+";keys[175] = "vol-";keys[32] = "space";
    
    return keys[keyCode];
}

function getAction(keyEvent)
{
    var key = getKeyByKeyCode(keyEvent.keyCode);
    return KEY_CONFIG[key];
}
function getActionUIRepresentation(action)
{
    return REV_KEY_CONFIG[action];
}

var ACTION = {
    ROTATE_LEFT: 0,
    ROTATE_RIGHT: 1,
    SOFT_DROP: 2,
    HARD_DROP: 3,
    MOVE_LEFT: 4,
    MOVE_RIGHT: 5,
    PAUSE_RESUME: 6
},
    KEY_CONFIG = new Array(),
    REV_KEY_CONFIG = new Array();
    
KEY_CONFIG.setType = function (type) {
    switch (type)
    {
        case "tobsetetris":
            KEY_CONFIG["space"] = ACTION.HARD_DROP;
            KEY_CONFIG["left"] = ACTION.MOVE_LEFT;
            KEY_CONFIG["right"] = ACTION.MOVE_RIGHT;
            KEY_CONFIG["up"] = ACTION.ROTATE_RIGHT;
            KEY_CONFIG["down"] = ACTION.SOFT_DROP;
            KEY_CONFIG["esc"] = ACTION.PAUSE_RESUME;

            REV_KEY_CONFIG = [];
            REV_KEY_CONFIG[ACTION.HARD_DROP] = "space";
            REV_KEY_CONFIG[ACTION.SOFT_DROP] = "down";
            REV_KEY_CONFIG[ACTION.MOVE_LEFT] = "left";
            REV_KEY_CONFIG[ACTION.MOVE_RIGHT] = "right";
            REV_KEY_CONFIG[ACTION.ROTATE_RIGHT] = "up";
            REV_KEY_CONFIG[ACTION.PAUSE_RESUME] = "esc";
            break;
        case "classic":
            KEY_CONFIG["space"] = ACTION.ROTATE_RIGHT;
            KEY_CONFIG["left"] = ACTION.MOVE_LEFT;
            KEY_CONFIG["right"] = ACTION.MOVE_RIGHT;
            KEY_CONFIG["up"] = undefined;
            KEY_CONFIG["down"] = ACTION.SOFT_DROP;
            KEY_CONFIG["esc"] = ACTION.PAUSE_RESUME;

            REV_KEY_CONFIG = [];
            REV_KEY_CONFIG[ACTION.HARD_DROP] = undefined;
            REV_KEY_CONFIG[ACTION.SOFT_DROP] = "down";
            REV_KEY_CONFIG[ACTION.MOVE_LEFT] = "left";
            REV_KEY_CONFIG[ACTION.MOVE_RIGHT] = "space";
            REV_KEY_CONFIG[ACTION.ROTATE_RIGHT] = "up";
            REV_KEY_CONFIG[ACTION.PAUSE_RESUME] = "esc";
            break;
        case "touch":
            // swipe actions integrate more tightly with the game
            // thus, they are always working and implemented in tetris.js
            
            REV_KEY_CONFIG = [];
            REV_KEY_CONFIG[ACTION.HARD_DROP] = "swipe-down";
            REV_KEY_CONFIG[ACTION.SOFT_DROP] = undefined;
            REV_KEY_CONFIG[ACTION.MOVE_LEFT] = "swipe-left";
            REV_KEY_CONFIG[ACTION.MOVE_RIGHT] = "swipe-right";
            REV_KEY_CONFIG[ACTION.ROTATE_LEFT] = undefined;
            REV_KEY_CONFIG[ACTION.ROTATE_RIGHT] = "swipe-up";
            REV_KEY_CONFIG[ACTION.PAUSE_RESUME] = "pinch-in";
            break;
        default:
            throw new Exception("Unknown config " + type);
    }

    $.settings("keyconfig", type);

    $(".control-scheme").removeClass("selected");
    $("#control-scheme-" + type).addClass("selected");
    $("#control-scheme-switcher span").removeClass("selected");
    $("span[rel=\"" + type + "\"]").addClass("selected");
};
