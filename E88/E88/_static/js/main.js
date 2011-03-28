// initialize globals related to game selector {{{
var availableGameIcons          = new Array();

// }}}

// initialize globals related to promotion panels {{{
var availablePromotions         = new Array();
// }}}


// ScrollingIcon {{{
/*
    Class for a single scrollable icon. Provides methods for getting
    HTML representation of the icon and scrolling mechanism.
*/
function ScrollingIcon (id, iconElement, iconWidth, animationLength, iconsPerPage, numberOfAllIcons) {
    this.id                     = id;
    this.iconElement            = iconElement;
    this.animationLength        = animationLength;
    this.iconsPerPage           = iconsPerPage;
    this.numberOfAllIcons       = numberOfAllIcons;
    this.iconWidth              = iconWidth;
    this.currentPosition        = id * this.iconWidth;
    this.distance               = this.iconWidth * this.iconsPerPage;
    this.animationId            = undefined;
    this.animationStart         = undefined;
}

ScrollingIcon.prototype.scroll = function(direction) {
    // abort in case there is already animation in progress {{{
    if (this.animationId != undefined) {
        return;
    }
    // }}}
    
    // remember the animation's start
    this.animationStart = new Date().getTime();
    
    // remember the current position
    this.currentPosition = this.iconElement.offsetLeft;

    // scroll the icon to the right {{{
    // create referene to the instance for setInterval()
    var instance = this;

    // create interval which drives the animation {{{
    this.animationId = setInterval(
        function() {
            instance["_scroll_" + direction]();
        },
        40
    );
    // }}}
    // }}}
}

ScrollingIcon.prototype._scroll_right = function() {
    // compute the time delta since start of the animation
    var delta = Math.min(this.animationLength, new Date().getTime() - this.animationStart);
 
    // compute the current position for the icon
    var position = Math.floor(this.currentPosition - (this.distance * (delta / this.animationLength)));
    
    // reposition the icon to the end to ensure seamless never ending scrolling {{{
    if (position < (-this.iconWidth)) {
        position = (position + this.iconWidth) + (this.iconWidth * (this.numberOfAllIcons - 1)); 
    }
    // }}}

    // abort the animation if time's up {{{
    if (delta == this.animationLength) {
        // remove the animation interval {{{
        clearInterval(this.animationId);
        this.animationId = undefined;
        // }}}

        // store the current position
        this.currentPosition = position;
    }
    // }}}

    // position the object
    this.iconElement.style.left = position + 'px';
}

ScrollingIcon.prototype._scroll_left = function() {
    // compute the time delta since start of the animation
    var delta = Math.min(this.animationLength, new Date().getTime() - this.animationStart);
 
    // compute the current position for the icon
    var position = Math.floor(this.currentPosition + (this.distance * (delta / this.animationLength)));

    // reposition the icon to the beginning to ensure seamless never ending scrolling {{{
    if (position > (this.iconWidth * this.iconsPerPage)) {
        position = -((this.iconWidth * (this.numberOfAllIcons - 1)) - (position - this.iconWidth)); 
    }
    // }}}

    // abort the animation if time's up {{{
    if (delta == this.animationLength) {
        // remove the animation interval {{{
        clearInterval(this.animationId);
        this.animationId = undefined;
        // }}}

        // store the current position
        this.currentPosition = position;
    }
    // }}}

    // position the object
    this.iconElement.style.left = position + 'px';
}

// }}}


// GLOBAL FUNCTIONS {{{

function showGameSelector(prefix, numberOfGames) {
    // create game icons {{{
    for (var a = 0; a < numberOfGames; a++) {
        // create the game icon
        var game = new ScrollingIcon(a, getHTMLObject(prefix + '_' + a), gameSelectorIconWidth, gameSelectorAnimationLength, gameSelectorGamesPerPage, numberOfGames);

        // add instance to the list of available game icon containers
        availableGameIcons.push(game);
    }
    // }}}
}

function showPromotions(prefix, numberOfPromotions) {
    // create promotion icons {{{
    for (var a = 0; a < numberOfPromotions; a++) {
        // get the promotion object
        e = getHTMLObject(prefix + '_' + a);
        // set the object position and visibility
        e.style.left = (a * promotionIconWidth) + 'px';
        e.style.display = 'block';
        // create the promotion icon
        var promotion = new ScrollingIcon(a, e, promotionIconWidth, promotionAnimationLength, promotionsPerPage, numberOfPromotions);

        // add instance to the list of available promotion icon containers
        availablePromotions.push(promotion);

    }
    // }}}
   
    // setup rotation interval
    promotionChangeInterval = setInterval(scrollPromotions, promotionsRotationInterval);
}

function scrollPromotions() {
    // instruct all game icons to sroll to the right {{{
    for (var a = 0; a < availablePromotions.length; a++) {
        availablePromotions[a].scroll("right");
    }
    // }}}
}

function gameSelectorScrollRight(divId) {
    // instruct all game icons to sroll to the right {{{
    for (var a = 0; a < availableGameIcons.length; a++) {
        availableGameIcons[a].scroll("right");
    }
    // }}}

    // return whether we should propagate click (in case of failure)
    return availableGameIcons.length ? false : true;
}

function gameSelectorScrollLeft(divId) {
    // instruct all game icons to scroll to the left {{{
    for (var a = 0; a < availableGameIcons.length; a++) {
        availableGameIcons[a].scroll("left");
    }
    // }}}

    // return whether we should propagate click (in case of failure)
    return availableGameIcons.length ? false : true;
}

function getHTMLObject(movieName) {
    // shortcut to document.getElementById()
    return document.getElementById(movieName);
}

function printJackpot(jackpot) {
    // initialize jackpot output HTML variable
    var jackpotHTML = "";

    // determine length of the jackpot string
    var jackpotLength = jackpot.length;
    
    // do not allow more than 9 characters to be displayed {{{
    if (jackpotLength > 9 ) {
        jackpotLength = 9;
    }
    // }}}
    
    // create the resulting jackpot HTML {{{
    for (var a = 0; a < jackpotLength; a++) {
        jackpotHTML += '<img src="' + jackpotLetterURIPart1 + letters[jackpot.charAt(a)] + jackpotLetterURIPart2+ '">';    
    }
    // }}}

    // get reference to the jackpot label
    var jackpotContainer = getHTMLObject("jackpotLabel");

    // replace the content of jackpot label
    jackpotContainer.innerHTML = jackpotHTML;
}

function increaseJackpot() {
    if (!currentJackpot)
        return;

    currentJackpot += 0.01;
    var jackpotText = sprintf("%.2f", currentJackpot);
    jackpotText = jackpotText.replace(/\d{1,3}(?=(\d{3})+(?!\d))/g, '$&,');
    printJackpot(jackpotText);

    setTimeout('increaseJackpot();', 1000 * jackpotIncreaseTimeout);
}

// }}}
