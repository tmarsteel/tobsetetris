<?php
include_once(dirname(__FILE__) . '/../src/default.inc.php');
$webroot = $_CONFIG["webroot"];
?><!DOCTYPE html>
<html>
    <head>
        <title>Tetris</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <meta name="keywords" content="tetris html5 online free play" />
        <meta name="description" content="Play a HTML5-Tetris online and free!">

        <script type="text/javascript" src="<?=$webroot;?>js/libs/jquery.js"></script>
        <script type="text/javascript" src="<?=$webroot;?>js/libs/jquery.easing.js"></script>
        <script type="text/javascript" src="<?=$webroot;?>js/libs/jquery.settings.js"></script>
        <script type="text/javascript" src="<?=$webroot;?>js/libs/keyconfig.js"></script>
        <script type="text/javascript" src="<?=$webroot;?>js/utils.js?<?=time()?>"></script>
        <script type="text/javascript" src="<?=$webroot;?>js/bricks.js?<?=time()?>"></script>
        <script type="text/javascript" src="<?=$webroot;?>js/tetris.js?<?=time()?>"></script>
        
        <link href='http://fonts.googleapis.com/css?family=Ubuntu:400' rel='stylesheet' type='text/css'>
        <link href="<?=$webroot;?>css/styles.css" type="text/css" rel="stylesheet" media="screen" />
        <link href="<?=$webroot;?>favicon.png" rel="shortcut icon" type="image/png" />
    </head>
    <body>
        <audio id="audio-theme" muted autoplay loop>
            <source src="<?=$webroot;?>res/tetris.mp3" type="audio/mp3" />
            <source src="<?=$webroot;?>res/tetris.ogg" type="audio/ogg" />
        </audio>
        <div id="main">
            <div id="gamepanel">
                <div class="gamepanel-overlay" id="play-overlay"></div>
                <div class="gamepanel-overlay" id="ghost-overlay"></div>
                <div class="gamepanel-overlay" id="message-overlay"></div>
                <div class="gamepanel-overlay" id="effect-overlay"></div>
            </div>
            <div id="gameinfo">
                <div id="nextbrick1" class="brick-preview"></div>
                <div id="nextbrick2" class="brick-preview"></div>
                <span class="label">Level</span>
                <br>
                <a id="level" class="graved">1</a>
                <br> 
                <span class="label">Points</span>
                <br>
                <a id="points" class="graved">000,000</a>
                <div id="options">
                    <div class="option boolean" id="button-mute" title="(un)mute music"></div>
                    <div class="option boolean" id="button-ghost" title="show ghost block"></div>
                </div>
            </div>
        </div>
        
		<!-- OnPage Scripts -->
		<script type="text/javascript">
                    $(window).on('popstate', function(e) {
                        var pn = e? e.target.location.pathname : location.pathname;
                                        switch (pn) {
                            case "/credits":
                                showCredits();
                                break;
                            case "/keyconfig":
                                showOptions();
                                break;
                            case "/scores":
                                showHighscores();
                                break;
                            default:
                                if (pn.substr(0, 8) == '/scores/')
                                {
                                    var nPage = parseInt(pn.substr(8));
                                    showHighscorePage(nPage);
                                }
                                else
                                {
                                    showStart();
                                }
                                break;
                        }
                    });
                    $(document).ready(function() {
                        $(window).trigger('popstate');
                    });
                </script>
        
        <!-- templates -->
        <div class="template" id="template-start">
            <br>
            <div class="content">
                <input type="button" value="START" onclick="START_GAME();return false;">
                <br><br>
                <input type="button" value="HIGHSCORES" onclick="showHighscores();return false;">
                <br><br>
                <input type="button" value="CONTROLS" onclick="showOptions();return false;">
                <br><br>
                <input type="button" value="CREDITS" onclick="showCredits();return false;">
            </div>
        </div>
        
        <div class="template" id="template-options">
            <center>
                <div id="control-scheme-switcher">
                    <span class="selected" rel="tobsetetris">TTetris</span>
                    <span rel="classic">Classic</span>
                </div>
                <div class="control-scheme selected" id="control-scheme-tobsetetris">
                    <div class="control-info">
                        <div class="key esc"><span>ESC</span></div>
                        <span>pause</span>
                    </div>
                    <div class="control-info">
                        <div class="key down"></div>
                        <span>soft drop</span>
                    </div>
                    <br>
                    <br>
                    <div class="control-info">
                        <div class="key left"></div>
                        <span>move left</span>
                    </div>
                    <div class="control-info">
                        <div class="key right"></div>
                        <span>move right</span>
                    </div>
                    <br>
                    <br>
                    <div class="control-info">
                        <div class="key up"></div>
                        <span>rotate right</span>
                    </div>
                    <div class="control-info">
                        <div class="key space"></div>
                        <span>hard drop</span>
                    </div>
                </div>
                <div class="control-scheme" id="control-scheme-classic">
                    <div class="control-info">
                        <div class="key esc"><span>ESC</span></div>
                        <span>pause</span>
                    </div>
                    <div class="control-info">
                        <div class="key down"></div>
                        <span>soft drop</span>
                    </div>
                    <br>
                    <br>
                    <div class="control-info">
                        <div class="key left"></div>
                        <span>move left</span>
                    </div>
                    <div class="control-info">
                        <div class="key right"></div>
                        <span>move right</span>
                    </div>								
                    <br>
                    <br>
                    <div class="control-info">
                        <div class="key space"></div>
                        <span>rotate right</span>
                    </div>
                </div>
            </center>
            <br>
            <input value="BACK" type="button" onclick="showStart();return false;">
        </div>
        
        <div class="template" id="template-credits">
            <p class="credits content">
                <br>
                Code & Design by Tobias Marstaller
                <br><br>
                Music by Nintendo &REG;, Copyright &COPY; 2015
                <br><br>
                improved with shiploads of feedback from ptR and Marcel 
                <br><br><br>
                Some icons by <a href="">Flaticons</a> authors:
                <br><br>
                <a href="http://www.flaticon.com/authors/icomoon">Icomoon</a>
                <br>
                <a href="http://www.flaticon.com/authors/plainicon">Plainicon</a>
                <br>
                <a href="http://www.flaticon.com/authors/freepik">Freepik</a>
                <br><br>
                these icons are licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0">CC BY 3.0</a>
                <br><br><br>
                <input value="BACK" type="button" onclick="showStart();return false;">
            </p>
        </div>
        
        <div class="template" id="template-submit">
            <p class="content">
                <span>Good game! Better luck next time.</span>
                <br><br>
                <input maxlength="11" id="nameInput" type="text">
                <br><br>
                <input value="submit score" type="button" class="submit-button">
                <input value="play again" type="button" onclick="START_GAME();return false;">
                <input value="start screen" type="button" onclick="showStart();return false;">
            </p>
        </div>
    </body>
</html>
