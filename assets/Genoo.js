/**
 * Admin Helper
 *
 * @type {*|Object}
 */

var Admin = Admin || {};


/**
 * Build query
 *
 * @param formdata
 * @param numeric_prefix
 * @param arg_separator
 * @return {String}
 */

Admin.buildQuery = function (formdata, numeric_prefix, arg_separator){
    var value, key, tmp = [],
        that = this;
    var _http_build_query_helper = function (key, val, arg_separator) {
        var k, tmp = [];
        if (val === true) {
            val = "1";
        } else if (val === false) {
            val = "0";
        }
        if (val != null) {
            if(typeof val === "object") {
                for (k in val) {
                    if (val[k] != null) {
                        tmp.push(_http_build_query_helper(key + "[" + k + "]", val[k], arg_separator));
                    }
                }
                return tmp.join(arg_separator);
            } else if (typeof val !== "function") {
                return encodeURIComponent(key) + "=" + encodeURIComponent(val);
            } else {
                throw new Error('There was an error processing for Admin.buildQuery().');
            }
        } else {
            return '';
        }
    };

    if (!arg_separator) {
        arg_separator = "&";
    }
    for (key in formdata) {
        value = formdata[key];
        if (numeric_prefix && !isNaN(key)) {
            key = String(numeric_prefix) + key;
        }
        var query=_http_build_query_helper(key, value, arg_separator);
        if(query !== '') {
            tmp.push(query);
        }
    }
    return tmp.join(arg_separator);
}


/**
 * Genoo
 *
 * @version 0.4
 */

/**
 * Provide
 * @type {*|Object}
 */

var Genoo = Genoo || {};

/**
 * Theme switcher id
 * @type {String}
 */

var GenooThemeSwitcher = 'genooThemeSettings-genooFormTheme';

/**
 * Theme preview id
 * @type {String}
 */

var GenooThemePreview = 'genooThemeSettings-genooFormPrev';


/**
 * Impprting message
 * @type {*}
 */

var GenooImportingMessage = GenooVars.GenooMessages.importing;


/**
 * Check if element exists
 *
 * @param elem
 * @return {Boolean}
 */

Genoo.elementExists = function(elem){ if(elem.length > 0){ return true; } else { return false; } };


/**
 * Switches image
 *
 * @param to
 */

Genoo.switchImage = function(to)
{
    var preUrl = (window['GenooVars'] != undefined) ? GenooVars : {};
    var preElem = jQuery('#' + GenooThemePreview);
    if(preUrl.GenooPluginUrl){
        // if url is there,
        var preImage = preUrl.GenooPluginUrl + to + '.jpg';
        var preImageTag = '<img src="' + preImage + '?genoo=2" class="genooAdminImage" />';
        preElem.html(preImageTag);
    } else {
        Genoo.flush();
    }
}


/**
 * Flush preview image
 */

Genoo.flush = function(){ jQuery('#' + GenooThemePreview).html(''); }


/**
 * Switch to init image
 */

Genoo.switchToInitImage = function(){ Genoo.switchImage(Genoo.getCurrentValue(document.getElementById(GenooThemeSwitcher))); }


/**
 * Switch to image, used with "onChange" on form
 *
 * @param elem
 */

Genoo.switchToImage = function(elem){ Genoo.switchImage(Genoo.getCurrentValue(elem)); };


/**
 * Get current value of a dropdown
 *
 * @param elem
 * @return {String|Number|String}
 */

Genoo.getCurrentValue = function(elem){ return elem.options[elem.selectedIndex].value; }


/**
 * In array, copy of PHP in_array
 *
 * @param needle
 * @param haystack
 * @param argStrict
 * @return {Boolean}
 */

Genoo.inArray = function(needle, haystack, argStrict){
    var key = '',
        strict = !! argStrict;
    if (strict){
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
    return false;
}


/**
 * Is array
 *
 * @param o
 * @return {Boolean}
 */

Genoo.isArray = function(o)
{
    if(o != null && typeof o == 'object') {
        return (typeof o.push == 'undefined') ? false : true;
    }else {
        return false;
    }
}


/**
 * Start import
 *
 * @param e
 */

Genoo.startImport = function(e)
{
    // prevent default click
    e.preventDefault();

    /**
     * Step 1: Start import
     */

    Genoo.startEventLog();
    Genoo.setLog();

    // call for comments info
    var data = { action: 'genooImportStart'};

        jQuery.post(ajaxurl, data, function(response){

            Genoo.setLog(false);

            /**
             * Step 2: If we can import, import, display next step message
             */

            Genoo.addLogMessage(response.commentsMessage, 0);

            // do we import?
            if(response.commentsStatus == true){

                // Prep vars
                var msgs = response.commentsCount;
                var msgOffset = 0;
                var msgPer = 100;
                var msgSteps = 1;
                if(msgs > msgPer){ msgSteps = Math.ceil(msgs / msgPer); }
                var msgStep = 0;

                /**
                 * Step 3: Loop thru steps, catch response
                 */

                Genoo.startEventLogIn();
                Genoo.addLogMessage(GenooImportingMessage);
                Genoo.setProgressBar();
                Genoo.progressBar(0);

                /**
                 * Step 4: Set up interval, steps that wait for last to finish
                 */

                (function importComments(){

                    msgOffset = msgStep * msgPer;

                    var temp = {
                        action: 'genooImportComments',
                        offset: msgOffset,
                        per: msgPer
                    };

                    /**
                     * Step 5: Add log message for each comment with success / error.
                     */

                    jQuery.post(ajaxurl, temp, function(importResponse){

                        if(Genoo.isArray(importResponse.messages)){
                            for (var i = 0; i < importResponse.messages.length; i++){
                                Genoo.addLogMessage(importResponse.messages[i]);
                            }
                        } else {
                            Genoo.addLogMessage(importResponse.messages);
                        }

                        msgStep++;
                        Genoo.progressBar(Genoo.logPercentage(msgStep, msgSteps));

                        if(msgStep < msgSteps){
                            setTimeout(function(){
                                importComments();
                            }, 1000);
                        }

                    });
                }());

            }
    });
}


/**
 * Start subscriber import
 *
 * @param e
 */

Genoo.startSubscriberImport = function(e)
{
    // prevent default click
    e.preventDefault();



    /**
     * Step 1: Start import
     */

    Genoo.startEventLog();
    Genoo.setLog();

    // call for comments info
    var data = { action: 'genooImportSubscribersStart'};

    jQuery.post(ajaxurl, data, function(response){

        Genoo.setLog(false);

        /**
         * Step 2: If we can import, import, display next step message
         */

        Genoo.addLogMessage(response.message, 0);

        // do we import?
        if(response.status == true){

            // Prep vars
            var msgs = response.count;
            var msgOffset = 0;
            var msgPer = 100;
            var msgSteps = 1;
            if(msgs > msgPer){ msgSteps = Math.ceil(msgs / msgPer); }
            var leadType = Genoo.getCurrentValue(document.getElementById('toolsLeadTypes'));
            var msgStep = 0;

            /**
             * Step 3: Loop thru steps, catch response
             */

            Genoo.startEventLogIn();
            Genoo.addLogMessage(GenooImportingMessage);
            Genoo.setProgressBar();
            Genoo.progressBar(0);

            /**
             * Step 4: Set up interval, steps that wait for last to finish
             */

            (function importSubscribers(){

                msgOffset = msgStep * msgPer;

                var temp = {
                    action: 'genooImportSubscribers',
                    offset: msgOffset,
                    leadType: leadType,
                    per: msgPer
                };

                /**
                 * Step 5: Add log message for each comment with success / error.
                 */

                jQuery.post(ajaxurl, temp, function(importResponse){

                    if(Genoo.isArray(importResponse.messages)){
                        for (var i = 0; i < importResponse.messages.length; i++){
                            Genoo.addLogMessage(importResponse.messages[i]);
                        }
                    } else {
                        Genoo.addLogMessage(importResponse.messages);
                    }

                    msgStep++;
                    Genoo.progressBar(Genoo.logPercentage(msgStep, msgSteps));

                    if(msgStep < msgSteps){
                        setTimeout(function(){
                            importSubscribers();
                        }, 1000);
                    }

                });
            }());
        }
    });
}



/**
 * Log percentage calc
 *
 * @param step
 * @param steps
 * @return {Number}
 */

Genoo.logPercentage = function(step, steps){ return (step / steps) * 100; }


/**
 * Start event log
 */

Genoo.startEventLog = function()
{
    jQuery("#genooLog").remove();
    jQuery(".metabox-holder").prepend('<div id="genooLog" class="strong update-nag">' +
        '<div id="genooHeader"></div>' +
        '</div>');
}


/**
 * Event log in
 */

Genoo.startEventLogIn = function(){ return jQuery('#genooLog').append('<div id="genooLogIn"></div>'); }


/**
 * Start progress bar
 *
 * @param yes
 * @return {*}
 */

Genoo.setProgressBar = function(yes)
{
    if(yes == false){
        return jQuery("#genooProgressBar").remove();
    }
    return jQuery("#genooLog").append('<div id="genooProgressBar"><span id="genooProgressBarBG" class="button button-primary"></span><span id="genooProgressBarText"></span></div>');
}


/**
 * Progress bar
 * @param perc
 */

Genoo.progressBar = function(perc)
{
    var cailed = Math.ceil(perc);
    document.getElementById('genooProgressBarText').innerHTML = cailed + '%';
    document.getElementById('genooProgressBarBG').style.width = cailed + '%';
}


/**
 * Add a log message
 *
 * @param message
 * @param type
 */

Genoo.addLogMessage = function(message, type)
{
    if(type == 0){
        return  jQuery("#genooHeader").append('<h3>' + message + '</h3><div class="clear"></div>');
    }
    return jQuery("#genooLogIn").append('<small>' + message + '</small><div class="clear"></div>');
}


/**
 * Set Genoo log
 *
 * @param log
 * @return {*}
 */

Genoo.setLog = function(log)
{
    if(log == false){
        return jQuery("#genooLoading").remove();
    }
    return jQuery("#genooLog").append('<div id="genooLoading" class="genooLoading"></div>');
}


/**
 * Genoo init
 */

Genoo.init = function()
{
    if(Genoo.elementExists(document.getElementById(GenooThemeSwitcher))){
        Genoo.switchToImage(document.getElementById(GenooThemeSwitcher));
    }
};


/**
 * Jquery document ready (init)
 */

jQuery(document).ready(function(){ Genoo.init(); });