/**
 * Genoo Admin
 *
 * @version 1.0.5
 * @author Genoo LLC
 */

/*********************************************************************/

 /**
 * Tools
 * @type {*|Object}
 */

var Tool = Tool || {};


/**
 * Has class
 *
 * @param el
 * @param className
 * @return {Boolean}
 */

Tool.hasClass = function(el, className)
{
    if (el.classList)
        return el.classList.contains(className);
    else
        return new RegExp('(^| )' + className + '( |$)', 'gi').test(el.className);
};


/**
 * Add class
 *
 * @param el
 * @param className
 */

Tool.addClass = function(el, className)
{
    if (el.classList)
        el.classList.add(className);
    else
        el.className += ' ' + className;
};


/**
 * Remove class
 *
 * @param el
 * @param className
 */

Tool.removeClass = function(el, className)
{
    if (el.classList)
        el.classList.remove(className);
    else
        el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
};

/**
 * Switch class
 *
 * @param element
 * @param className
 */

Tool.switchClass = function(element, className)
{
    if(Tool.hasClass(element, className)){
        Tool.removeClass(element, className);
    } else {
        Tool.addClass(element, className);
    }
};


/**
 * Switch tab
 *
 * @param el
 * @param id
 */

Tool.switchTab = function(el, id)
{
    var selected = el.options[el.selectedIndex].value;
    var tabHtml = document.getElementById(id + 'html');
    var tabImg = document.getElementById(id + 'img');
    var tabCurrent = document.getElementById(id + selected);
    Tool.switchClass(tabHtml, 'hidden');
    Tool.switchClass(tabImg, 'hidden');
};


/**
 * Version compare (js copy of PHP code)
 *
 * @param v1
 * @param v2
 * @param operator
 * @returns {*}
 */

Tool.versionCompare = function(v1, v2, operator){
    //       discuss at: http://phpjs.org/functions/version_compare/
    //      original by: Philippe Jausions (http://pear.php.net/user/jausions)
    //      original by: Aidan Lister (http://aidanlister.com/)
    // reimplemented by: Kankrelune (http://www.webfaktory.info/)
    //      improved by: Brett Zamir (http://brett-zamir.me)
    //      improved by: Scott Baker
    //      improved by: Theriault
    //        example 1: version_compare('8.2.5rc', '8.2.5a');
    //        returns 1: 1
    //        example 2: version_compare('8.2.50', '8.2.52', '<');
    //        returns 2: true
    //        example 3: version_compare('5.3.0-dev', '5.3.0');
    //        returns 3: -1
    //        example 4: version_compare('4.1.0.52','4.01.0.51');
    //        returns 4: 1

    this.compare = this.compare || {};
    this.compare.ENV = this.compare.ENV || {};
    // END REDUNDANT
    // Important: compare must be initialized at 0.
    var i = 0,
        x = 0,
        compare = 0,
    // vm maps textual PHP versions to negatives so they're less than 0.
    // PHP currently defines these as CASE-SENSITIVE. It is important to
    // leave these as negatives so that they can come before numerical versions
    // and as if no letters were there to begin with.
    // (1alpha is < 1 and < 1.1 but > 1dev1)
    // If a non-numerical value can't be mapped to this table, it receives
    // -7 as its value.
        vm = {
            'dev': -6,
            'alpha': -5,
            'a': -5,
            'beta': -4,
            'b': -4,
            'RC': -3,
            'rc': -3,
            '#': -2,
            'p': 1,
            'pl': 1
        },
    // This function will be called to prepare each version argument.
    // It replaces every _, -, and + with a dot.
    // It surrounds any nonsequence of numbers/dots with dots.
    // It replaces sequences of dots with a single dot.
    //    version_compare('4..0', '4.0') == 0
    // Important: A string of 0 length needs to be converted into a value
    // even less than an unexisting value in vm (-7), hence [-8].
    // It's also important to not strip spaces because of this.
    //   version_compare('', ' ') == 1
        prepVersion = function(v) {
            v = ('' + v)
                .replace(/[_\-+]/g, '.');
            v = v.replace(/([^.\d]+)/g, '.$1.')
                .replace(/\.{2,}/g, '.');
            return (!v.length ? [-8] : v.split('.'));
        };
    // This converts a version component to a number.
    // Empty component becomes 0.
    // Non-numerical component becomes a negative number.
    // Numerical component becomes itself as an integer.
    numVersion = function(v) {
        return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10));
    };
    v1 = prepVersion(v1);
    v2 = prepVersion(v2);
    x = Math.max(v1.length, v2.length);
    for (i = 0; i < x; i++) {
        if (v1[i] == v2[i]) {
            continue;
        }
        v1[i] = numVersion(v1[i]);
        v2[i] = numVersion(v2[i]);
        if (v1[i] < v2[i]) {
            compare = -1;
            break;
        } else if (v1[i] > v2[i]) {
            compare = 1;
            break;
        }
    }
    if (!operator) {
        return compare;
    }

    // Important: operator is CASE-SENSITIVE.
    // "No operator" seems to be treated as "<."
    // Any other values seem to make the function return null.
    switch (operator) {
        case '>':
        case 'gt':
            return (compare > 0);
        case '>=':
        case 'ge':
            return (compare >= 0);
        case '<=':
        case 'le':
            return (compare <= 0);
        case '==':
        case '=':
        case 'eq':
            return (compare === 0);
        case '<>':
        case '!=':
        case 'ne':
            return (compare !== 0);
        case '':
        case '<':
        case 'lt':
            return (compare < 0);
        default:
            return null;
    }
};

/*********************************************************************/

/**
 * Modal
 * @type {*|Object}
 */

var Modal = Modal || {};


/**
 * Open modal
 *
 * @param e
 * @param el
 */

Modal.open = function(e, el)
{
    // prevent default
    e.preventDefault();

    // prep
    var genooFrame;
    var genooTarget = el.getAttribute('data-target');
    var genooTargetInput = el.getAttribute('data-target-input');
    var genooCurrent = el.getAttribute('data-current-id');
    var genooTitle = el.getAttribute('data-title');
    var genooTitleButton = el.getAttribute('data-update-text');

    // if the frame already exists, reopen it
    if (typeof(genooFrame)!=="undefined"){ genooFrame.close(); }

    // custom uploader
    genooFrame = wp.media.frames.file_frame = wp.media({ title: genooTitle, button: { text: genooTitleButton }, multiple: false });

    // on select
    genooFrame.on('select', function(){
        // empty first
        document.getElementById(genooTarget).innerHTML = '';
        var attachment = genooFrame.state().get('selection').first().toJSON();
        Modal.appendImage(genooTarget, attachment.url);
        document.getElementById(genooTargetInput).value = attachment.id;
        el.setAttribute('data-current-id', attachment.id);
    });

    // on open
    genooFrame.on('open',function(){
        // if there's current
        if(genooCurrent !== null){
            var selection = genooFrame.state().get('selection');
            var attachment = wp.media.attachment(genooCurrent);
            attachment.fetch();
            selection.add(attachment);
        }
    });

    // open
    genooFrame.open();
};


/**
 * Empty image
 *
 * @param event
 * @param id
 * @return {*}
 */

Modal.emptyImage = function(event, id, img, btn)
{
    event.preventDefault();
    document.getElementById(id).innerHTML = '';
    document.getElementById(img).value = '';
    document.getElementById(btn).setAttribute('data-current-id','');
    return false;
};


/**
 * Append image
 *
 * @param target
 * @param src
 * @return {XML|Node}
 */

Modal.appendImage = function(target, src)
{
    var elem = document.createElement("img");
        elem.setAttribute("src", src);
    return document.getElementById(target).appendChild(elem);
};


/*********************************************************************/


/**
 * Admin Helper
 *
 * @type {*|Object}
 */

var Admin = Admin || {};


/**
 * Urlencdoe
 */
Admin.urlencode = function(str){
    str = (str+'').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
};


/**
 * Build Query
 *
 * @param formdata
 * @param numeric_prefix
 * @param arg_separator
 * @returns {string}
 */
Admin.buildQuery = function (formdata, numeric_prefix, arg_separator){
    var value, key, tmp = [], that = this;

    var _http_build_query_helper = function (key, val, arg_separator) {
        var k, tmp = [];
        if (val === true) {
            val = "1";
        } else if (val === false){
            val = "0";
        }
        if (val !== null && typeof(val) === "object") {
            for (k in val) {
                if (val[k] !== null) {
                    tmp.push(_http_build_query_helper(key + "[" + k + "]", val[k], arg_separator));
                }
            }
            return tmp.join(arg_separator);
        } else if (typeof(val) !== "function") {
            return Admin.urlencode(key) + "=" + Admin.urlencode(val);
        } else if (typeof(val) == "function") {
            return '';
        } else {
            throw new Error('There was an error processing for http_build_query().');
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
        tmp.push(_http_build_query_helper(key, value, arg_separator));
    }
    return tmp.join(arg_separator);
};


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

Genoo.elementExists = function(elem)
{
    if(elem != 'undefined' && elem != null){
        return true;
    }
    return false;
};


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
};


/**
 * Flush preview image
 */

Genoo.flush = function(){ jQuery('#' + GenooThemePreview).html(''); };


/**
 * Switch to init image
 */

Genoo.switchToInitImage = function(){ Genoo.switchImage(Genoo.getCurrentValue(document.getElementById(GenooThemeSwitcher))); };


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

Genoo.getCurrentValue = function(elem){ return elem.options[elem.selectedIndex].value; };


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
};


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
    } else {
        return false;
    }
};


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
                 * Step 3: Loop through steps, catch response
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
};

/*********************************************************************/

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
             * Step 3: Loop through steps, catch response
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
};



/**
 * Log percentage calc
 *
 * @param step
 * @param steps
 * @return {Number}
 */

Genoo.logPercentage = function(step, steps){ return (step / steps) * 100; };


/**
 * Start event log
 */

Genoo.startEventLog = function()
{
    jQuery("#genooLog").remove();
    jQuery(".metabox-holder").prepend('<div id="genooLog" class="strong update-nag">' +
        '<div id="genooHeader"></div>' +
        '</div>');
};


/**
 * Event log in
 */

Genoo.startEventLogIn = function(){ return jQuery('#genooLog').append('<div id="genooLogIn"></div>'); };


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
};


/**
 * Progress bar
 * @param perc
 */

Genoo.progressBar = function(perc)
{
    var cailed = Math.ceil(perc);
    document.getElementById('genooProgressBarText').innerHTML = cailed + '%';
    document.getElementById('genooProgressBarBG').style.width = cailed + '%';
};


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
};


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
};


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

jQuery(document).ready(function(){
    Genoo.init();
});


/*********************************************************************/

/**
 * Genoo TinyMCE plugin
 *
 * @version 2.5
 * @author latorante.name
 */

/**
 * Genoo TinyMCE object
 *
 * @type {GenooTinyMCE|*|{}}
 */
var GenooTinyMCE = GenooTinyMCE || {};


/**
 * Get Attributes
 *
 * @param s
 * @param n
 * @param tinymce
 */
GenooTinyMCE.getAttributes = function(s, n, tinymce)
{
    n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
    return n ? tinymce.DOM.decode(n[1]) : '';
};


GenooTinyMCE.trim = function(str, charlist)
{
    var whitespace, l = 0,
        i = 0;
    str += '';
    if (!charlist) {
        // default list
        whitespace =
            ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
    } else {
        // preg_quote custom list
        charlist += '';
        whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
    }
    l = str.length;
    for (i = 0; i < l; i++) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(i);
            break;
        }
    }
    l = str.length;
    for (i = l - 1; i >= 0; i--) {
        if (whitespace.indexOf(str.charAt(i)) === -1) {
            str = str.substring(0, i + 1);
            break;
        }
    }
    return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
};


/**
 * Remove Toolbar
 *
 * @param ed
 * @param selected
 * @param buttonToolbar
 */
GenooTinyMCE.removeToolbar = function (ed, selected, buttonToolbar)
{
    var toolbar = ed.dom.get('wp-image-toolbar-g');
    var toolbars = ed.dom.select('div[data-toolbar-id="'+ buttonToolbar +'"]');
    var toolbarId = null;
    if(toolbar !== undefined && toolbar !== null){
        toolbarId = toolbar.getAttribute('data-toolbar-id');
    }
    if ((toolbar !== undefined && toolbar !== null) && (toolbarId !== null && toolbarId !== undefined && toolbarId == buttonToolbar)){
        ed.dom.remove(toolbar);
    }
    ed.dom.setAttrib(ed.dom.select('img['+ selected +']'), selected, null);
};


/**
 * Remove Toolbar
 *
 * @param ed
 * @param evenCurrent
 */
GenooTinyMCE.removeToolbarAll = function (ed, evenCurrent)
{
    GenooTinyMCE.addPlugin.log('Removing toolbars');
    GenooTinyMCE.addPlugin.log('Removing genoo-selected attr.');
    var toolbars = jQuery(ed.getBody()).find('.genoo-toolbar');
    var selected = jQuery(ed.getBody()).find('.genooFormTemp > img[data-mce-selected="1"]');
    //data-genooform-image-select
    if(toolbars !== undefined && toolbars !== null && toolbars.length){
        if(evenCurrent === true){
            GenooTinyMCE.addPlugin.log('Removing toolbars - Even selected.');
            GenooTinyMCE.clenseSelected(ed);
            toolbars.remove();
        }
        if(selected !== undefined && selected !== null && selected.length){
            var uniqueid = selected.attr('data-genoo-id');
            GenooTinyMCE.addPlugin.log('Removing toolbars - Selected found:');
            GenooTinyMCE.addPlugin.log(selected);
            GenooTinyMCE.addPlugin.log('Removing toolbars - Selected unique id:');
            GenooTinyMCE.addPlugin.log(uniqueid);
            toolbars.not('[data-genoo-id=' + uniqueid + ']').remove();
            GenooTinyMCE.addPlugin.log('Removing toolbars - All gone except prev ID.');
        } else {
            GenooTinyMCE.addPlugin.log('Removing toolbars - No selected found.');
            toolbars.remove();
        }
    } else {
        GenooTinyMCE.addPlugin.log('No toolbars found.');
    }
};


/**
 * Clense
 * @param ed
 */
GenooTinyMCE.clenseSelected = function(ed)
{
    GenooTinyMCE.addPlugin.log('Cleansing all selected ids');
    jQuery(ed.getBody())
        .find('img[data-genooform-image-select]')
        .removeData('data-genooform-image-select');
};


/**
 * Remove Deselected itesm
 *
 * @param ed
 * @param selected
 */
GenooTinyMCE.removeDeselect = function (ed, selected)
{
    ed.dom.setAttrib(ed.dom.select('img['+ selected +']'), selected, null);
};


/**
 * Unique id
 *
 * @returns {string}
 */
GenooTinyMCE.uniqueID  = function()
{
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
        s4() + '-' + s4() + s4() + s4();
};



/**
 * Encode
 *
 * @param text
 */
GenooTinyMCE.encode = function(text)
{
    var charsRegex = /[<>&\"\']/g;
    var charsEntites = {
        '\"': '&quot;',
        "'": '&#39;',
        '<': '&lt;',
        '>': '&gt;',
        '&': '&amp;',
        '\u0060': '&#96;'
    };
    return ('' + text).replace(charsRegex, function(chr){
        return charsEntites[chr] || chr;
    });
};


/**
 * Merge Defaults
 *
 * @param obj1
 * @param obj2
 * @returns {*}
 */
GenooTinyMCE.mergeDefaults = function(obj1, obj2){
    for (var p in obj2) {
        try {
            // Property in destination object set; update its value.
            if (obj2[p].constructor == Object) {
                obj1[p] = MergeRecursive(obj1[p], obj2[p]);
            } else {
                obj1[p] = obj2[p];
            }
        } catch (e) {
            // Property in destination object not set; create it and set its value.
            obj1[p] = obj2[p];
        }
    }
    return obj1;
};


/**
 * Add toolbar
 *
 * @param ed
 * @param node
 * @param selected
 * @param buttonEdit
 * @param buttonRemove
 * @param buttonToolbar
 * @param uniqueId
 */
GenooTinyMCE.addToolbar = function(ed, node, selected, buttonEdit, buttonRemove, buttonToolbar, uniqueId)
{
    // Log
    GenooTinyMCE.addPlugin.log('Adding toolbar ID: ' + uniqueId);
    // Remove toolbars
    GenooTinyMCE.removeToolbarAll(ed, true);
    // Vars
    var rectangle,
        toolbarHtml,
        toolbar,
        left,
        dom = ed.dom;

    // Don't add to placeholders
    if (!node || node.nodeName !== 'IMG') { return; }

    dom.setAttrib(node, selected, 1 );
    rectangle = dom.getRect(node);

    // Toolbar inner
    toolbarHtml = '<div class="dashicons toolbar-bogus dashicons-edit '+ buttonEdit +'" data-mce-bogus="all"></div>' +
    '<div class="dashicons toolbar-bogus dashicons-no-alt '+ buttonRemove +'" data-mce-bogus="all"></div>';

    // Toolbar
    toolbar = dom.create(
        'div', {
            'id': buttonToolbar,
            'class': 'genoo-toolbar genoo-toolbar-current',
            'data-mce-bogus': 'all',
            'data-toolbar-id': buttonToolbar,
            'data-genoo-id': uniqueId,
            'contenteditable': false
        },
        toolbarHtml
    );

    // Rtl
    if (ed.rtl){
        left = rectangle.x + rectangle.w - 82;
    } else {
        left = rectangle.x;
    }

    // Append toolbar
    ed.getBody().appendChild(toolbar);
    // Set
    dom.setStyles(toolbar, { top: rectangle.y, left: left});
};


/**
 * Add the main plugin, this is where the magic happens
 *
 * @param varVersion
 * @param varFile
 * @param varCommand
 * @param varTitle
 * @param varImage
 * @param varAligned
 * @param varMessage
 * @param varOptions
 */
GenooTinyMCE.addPlugin = function(varVersion, varFile, varCommand, varImage, varTitle, varAligned, varMessage, varOptions)
{
    // Set up variables
    var debug = false;
    var TinyMCEVersion = varVersion;
    var query = GenooVars.GenooTinyMCE ? GenooVars.GenooTinyMCE : '';
    var aligned = varAligned;
    var buttonTitle = varTitle;
    var pluginFile = varFile;
    var buttonCommand = varCommand;
    var buttonCommandEdit = buttonCommand + 'Edit';
    var buttonCommandRefresh = buttonCommand + 'Refresh';
    var buttonCommandReplace = buttonCommand + 'Replace';
    var buttonToolbar = buttonCommand + 'Toolbar';
    var buttonImage = varImage;
    var buttonCommandSelected = 'data-' + buttonCommand + '-image-select';
        buttonCommandSelected.toLowerCase();

    // Keywords play
    var buttonEdit = buttonCommand + 'Edit';
    var buttonRemove = buttonCommand + 'Remove';
    var buttonShortcode = buttonCommand + 'Shortcode';
    var buttonRemoveConfirmMessage = varMessage;
    var Self = this;
    var optionsDefaults = {
        width: 200,
        height: 200
    };
    var options  = GenooTinyMCE.mergeDefaults(optionsDefaults, varOptions);


    /**
     * Log
     * @param msg
     */
    GenooTinyMCE.addPlugin.log = function(msg){
        if(debug == true){
            console.log(msg);
        }
    };


    GenooTinyMCE.addPlugin.log('Plugin initiated:');
    GenooTinyMCE.addPlugin.log('Button: ' + buttonTitle);


    /**
     * Replace Shortcode
     *
     * @param tinymce
     * @param content
     * @param buttonCommand
     * @param buttonShortcode
     */
    GenooTinyMCE.addPlugin.contentReplaceShortcode = function(tinymce, content, buttonCommand, buttonShortcode)
    {
        var regex = new RegExp('\\\['+ buttonCommand +'([^\\\]]*)\\\]', 'g');
        return content.replace(regex, function(a,b){
            var title = GenooTinyMCE.encode(b);
                title = title ? title : '';
                title = buttonCommand + ' ' + GenooTinyMCE.trim(title);
            var elClass = '';
            // Can be aligned?
            if(aligned){
                if(title.indexOf('left') >= 0){
                    elClass = 'genooLeft';
                } else if (title.indexOf('right') >= 0){
                    elClass = 'genooRight';
                }
            }
            var buttonShortcodeClass = '';
            return "<div class='genooFormTemp mceItem mceNonEditable "+ elClass + " " + buttonShortcodeClass + "' contenteditable='false'><img src='" + tinymce.Env.transparentSrc + "' data-mce-resize='false' contenteditable='false' data-mce-placeholder='1' class='" + buttonShortcode + "' title='"  + title + "' /></div> ";
        });
    };


    /**
     * Restore Shortcode
     *
     * @param tinymce
     * @param content
     * @param buttonCommand
     * @param buttonShortcode
     */
    GenooTinyMCE.addPlugin.contentRestoreShortcode = function(tinymce, content, buttonCommand, buttonShortcode)
    {
        return content.replace(/(?:<div[^>]*>)*(<img[^>]+>)(?:<\/div>)*/g, function(a,im){
            var cls = GenooTinyMCE.getAttributes(im, 'class', tinymce);
            if (cls.indexOf(buttonShortcode) != -1)
                return '<p>['+tinymce.trim(GenooTinyMCE.getAttributes(im, 'title', tinymce))+']</p>';
            return a;
        });
    };


    /**
     * Restore and Replace Shortcode
     *
     * @param tinymce
     * @param content
     * @param buttonCommand
     * @param buttonShortcode
     */
    GenooTinyMCE.addPlugin.contentRestoreAndReplaceShortcode = function(tinymce, content, buttonCommand, buttonShortcode){
        var contentNew;
        contentNew = this.contentReplaceShortcode(tinymce, content, buttonCommand, buttonShortcode);
        contentNew = this.contentRestoreShortcode(tinymce, contentNew, buttonCommand, buttonShortcode);
        return contentNew;
    };


    // Version 4 and above
    if(Tool.versionCompare(TinyMCEVersion, '4', '>='))
    {
        // Add Plugin
        tinymce.PluginManager.add(buttonCommand, function(ed, url){
            // On start and insert
            ed.on('BeforeSetContent', function(event){ event.content = GenooTinyMCE.addPlugin.contentReplaceShortcode(tinymce, event.content, buttonCommand, buttonShortcode); });
            // On post process
            ed.on('PostProcess', function(event){ if (event.get){ event.content = GenooTinyMCE.addPlugin.contentRestoreShortcode(tinymce, event.content, buttonCommand, buttonShortcode); }});
            // Mouseup
            ed.on('mouseup', function(event){
                var image,
                    node = event.target,
                    selected = jQuery(ed.getBody()).find('.genooFormTemp > img[data-mce-selected="1"]');
                if(event.button && event.button > 1){ return; }
                if(jQuery(node).hasClass(buttonShortcode)){
                    var uniqueId = GenooTinyMCE.uniqueID();
                    GenooTinyMCE.clenseSelected(ed);
                    GenooTinyMCE.addPlugin.log('Setting image unique ID: ' +  uniqueId);
                    jQuery(node).attr('data-genoo-id', uniqueId);
                    GenooTinyMCE.addToolbar(ed, node, buttonCommandSelected, buttonEdit, buttonRemove, buttonToolbar, uniqueId);
                } else if(jQuery(event.target).hasClass(buttonEdit) || jQuery(event.target).hasClass(buttonRemove)){
                    event.preventDefault();
                    // Nope, we don't do anything here
                    GenooTinyMCE.addPlugin.log('Clicking edit or remove button from toolbar, doing nothing.');
                } else if(jQuery(event.target).hasClass('toolbar-bogus')){
                    //event.preventDefault();
                    // Nope, nothing
                    GenooTinyMCE.addPlugin.log('Clicking toolbar bogus, doing nothing.');
                } else if(jQuery(event.target).hasClass('genooFormShortcode')){
                    event.preventDefault();
                    ed.selection.setCursorLocation(ed.getBody().firstChild, 1);
                    GenooTinyMCE.addPlugin.log('Clicking genooFormShortode, doing nothing.');
                    // Nope, nothing, just move
                } else if(jQuery(event.target).parent().hasClass('genooFormTemp') || jQuery(event.target).hasClass('genooFormTemp')){

                } else {
                    // Else
                    GenooTinyMCE.removeToolbarAll(ed, true);
                }
            });
            // On click
            ed.on('click', function(e){
                GenooTinyMCE.addPlugin.log('Clicking event.');
                if(jQuery(e.target).hasClass(buttonEdit)){
                    var img = jQuery(e.target).closest('body').find('img['+ buttonCommandSelected +'="1"]');
                    ed.execCommand(buttonCommandEdit, false, img.attr('title'));
                    // but back selected attribute
                    img.attr(buttonCommandSelected, '1');
                    // select imaga back ...
                    ed.execCommand("mceSelectNode", false, ed.dom.select('['+ buttonCommandSelected +'="1"]')[0]);
                } else if (jQuery(e.target).hasClass(buttonRemove)){
                    ed.windowManager.confirm(buttonRemoveConfirmMessage, function(s){
                        if (s){
                            var img = jQuery(e.target).closest('body').find('img['+ buttonCommandSelected +'="1"]');
                            img.parent().remove();
                            GenooTinyMCE.removeToolbarAll(ed, true);
                        }
                    });
                }
            });
            // On Keyup
            ed.on('keyup', function(e){
                // delete Keys
                if (jQuery(ed.selection.getNode()).hasClass('mceNonEditable')){
                    //ed.selection.setCursorLocation(ed.selection.getNode(), 0);
                    //ed.selection.setCursorLocation(ed.selection.getNode(), 0);
                    e.preventDefault();
                }
            });
            // Add button
            ed.addButton(buttonCommand, { title : buttonTitle, cmd : buttonCommand, image : url + '/' + buttonImage });
            // Add command {add shortcode)
            ed.addCommand(buttonCommand, function(){
                query['edit'] = '0';
                query['version'] = TinyMCEVersion;
                query['commandRefresh'] = buttonCommandRefresh;
                ed.windowManager.open({
                    file : url + '/' + pluginFile + '?' + Admin.buildQuery(query),
                    width : options.width + parseInt(ed.getLang('example.delta_width', 0)),
                    height : options.height + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
            });
            // Add command (edit)
            ed.addCommand(buttonCommandEdit, function(ui, string){
                // add selected
                query['version'] = TinyMCEVersion;
                query['edit'] = '1';
                query['selected'] = string;
                query['commandRefresh'] = buttonCommandRefresh;
                ed.windowManager.open({
                    file : url + '/' + pluginFile + '?' + Admin.buildQuery(query),
                    width : options.width + parseInt(ed.getLang('example.delta_width', 0)),
                    height : options.height + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
                GenooTinyMCE.removeToolbarAll(ed, true);
            });
            // Refresh content correctly ... :)
            ed.addCommand(buttonCommandRefresh, function(){
                ed.setContent(
                    GenooTinyMCE.addPlugin.contentRestoreAndReplaceShortcode(
                        tinymce,
                        ed.getContent(),
                        buttonCommand,
                        buttonShortcode
                    )
                );
                ed.focus();
            });
            // Replace Content
            ed.addCommand(buttonCommandReplace, function(output){});
        });
        // Version 3
    } else if(Tool.versionCompare(TinyMCEVersion, '3', '>=')){
        // Todo add version 3
    }
};