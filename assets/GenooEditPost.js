/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 */

/**
 * Document
 * @type {*|Object}
 */

var Document = Document || {};


/**
 * Document ready function
 *
 * @author Diego Perini (diego.perini at gmail.com)
 *
 * @param win
 * @param fn
 */

Document.ready = function(win, fn)
{
    var done = false, top = true,
        doc = win.document, root = doc.documentElement,
        add = doc.addEventListener ? 'addEventListener' : 'attachEvent',
        rem = doc.addEventListener ? 'removeEventListener' : 'detachEvent',
        pre = doc.addEventListener ? '' : 'on',
        init = function(e) {
            if (e.type == 'readystatechange' && doc.readyState != 'complete') return;
            (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
            if (!done && (done = true)) fn.call(win, e.type || e);
        },
        poll = function() {
            try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
            init('poll');
        };
    if (doc.readyState == 'complete') fn.call(win, 'lazy');
    else {
        if (doc.createEventObject && root.doScroll) {
            try { top = !win.frameElement; } catch(e) { }
            if (top) poll();
        }
        doc[add](pre + 'DOMContentLoaded', init, false);
        doc[add](pre + 'readystatechange', init, false);
        win[add](pre + 'load', init, false);
    }
};


/**
 * Element exitst in document?
 *
 * @param element
 * @returns {boolean}
 */

Document.elementExists = function(element)
{
    var el;
    if(typeof element == 'string'){
        el = document.getElementById(element)
    } else {
        el = element;
    }
    return (typeof(el) != 'undefined' && el != null);
};


/**
 * Event
 * @type {*|Object}
 */

var Event = Event || {};


/**
 * Attach event
 *
 * @param obj
 * @param type
 * @param fn
 */

Event.attach = function (obj, type, fn)
{
    if(obj == null) return;
    if (obj.addEventListener){
        obj.addEventListener(type, fn, false);
    } else if (obj.attachEvent){
        obj["e"+type+fn] = fn;
        obj[type+fn] = function() { obj["e"+type+fn]( window.event ); }
        obj.attachEvent( "on"+type, obj[type+fn] );
    } else {
        obj["on"+type] = obj["e"+type+fn];
    }
};


/**
 * Metabox
 * @type {*|Object}
 */

var Metabox = Metabox || {};


/**
 * Change cta link
 *
 * @param id
 */

Metabox.changeCTALink = function(id){ /* TODO: add change link */ };


/**
 * Check fields
 */

Metabox.checkFields = function(){ Metabox.checkEnabled(); };


/**
 * Check it all matey
 */

Metabox.checkEnabled = function()
{
    Metabox.hideAll();
    // do we show?
    if(Document.elementExists('genoo-cta-info')){
        // is form?
        if(document.getElementById('cta_type').options[document.getElementById('cta_type').selectedIndex].value == 'form'){
            document.getElementById('themeMetaboxRowform').style.display = 'block';
            document.getElementById('themeMetaboxRowform_theme').style.display = 'block';
            document.getElementById('themeMetaboxRowcta_type').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_type').style.display = 'block';
            document.getElementById('button_url').value = '';
            document.getElementById('open_in_new_window').checked = false;
            document.getElementById('themeMetaboxRowform_success_message').style.display = 'block';
            document.getElementById('themeMetaboxRowform_error_message').style.display = 'block';
            if(Document.elementExists(('themeMetaboxRowclass_list'))){
                document.getElementById('class_list').selectedIndex = 0;
            }
        } else if(document.getElementById('cta_type').options[document.getElementById('cta_type').selectedIndex].value == 'link') {
            document.getElementById('themeMetaboxRowcta_type').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_url').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_type').style.display = 'block';
            document.getElementById('themeMetaboxRowopen_in_new_window').style.display = 'block';
            document.getElementById('form').selectedIndex = 0;
            document.getElementById('form_theme').selectedIndex = 0;
            if(Document.elementExists(('themeMetaboxRowclass_list'))){
                document.getElementById('class_list').selectedIndex = 0;
            }
        } else if(document.getElementById('cta_type').options[document.getElementById('cta_type').selectedIndex].value == 'class'){
            document.getElementById('themeMetaboxRowcta_type').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_text').style.display = 'none';
            if(Document.elementExists(('themeMetaboxRowclass_list'))){
                document.getElementById('themeMetaboxRowclass_list').style.display = 'block';
            }
            document.getElementById('form').selectedIndex = 0;
            document.getElementById('form_theme').selectedIndex = 0;
        }
        // button type
        if(document.getElementById('button_type').options[document.getElementById('button_type').selectedIndex].value == 'html'){
            // Classlist doesn't really need button
            if(document.getElementById('cta_type').options[document.getElementById('cta_type').selectedIndex].value != 'class'){
                document.getElementById('themeMetaboxRowbutton_text').style.display = 'block';
            }
            document.getElementById('button_image').value = '';
            document.getElementById('button_hover_image').value = '';
        } else {
            document.getElementById('themeMetaboxRowbutton_image').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_hover_image').style.display = 'block';
            document.getElementById('button_text').value = '';
        }
    }
    if(Document.elementExists('genoo-cta')){
        if(document.getElementById('enable_cta_for_this_post').checked){
            // is form?
            document.getElementById('themeMetaboxRowselect_cta').style.display = 'block';
        }
    }
    if(Document.elementExists('repeatable_genoo-dynamic-cta')){
        if(document.getElementById('enable_cta_for_this_post_repeat').checked){
            // is form?
            document.getElementById('themeMetaboxRowselect_cta_repeat').style.display = 'block';
        }
    }
};


/**
 * Hide all
 */

Metabox.hideAll = function()
{
    if(Document.elementExists('genoo-cta-info')){
        document.getElementById('themeMetaboxRowcta_type').style.display = 'none';
        document.getElementById('themeMetaboxRowform').style.display = 'none';
        document.getElementById('themeMetaboxRowform_theme').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_type').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_url').style.display = 'none';
        document.getElementById('themeMetaboxRowopen_in_new_window').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_text').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_image').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_hover_image').style.display = 'none';
        document.getElementById('themeMetaboxRowform_success_message').style.display = 'none';
        document.getElementById('themeMetaboxRowform_error_message').style.display = 'none';
        if(Document.elementExists(('themeMetaboxRowclass_list'))){
            document.getElementById('themeMetaboxRowclass_list').style.display = 'none';
        }
    }
    if(Document.elementExists('genoo-cta')){
        document.getElementById('themeMetaboxRowselect_cta').style.display = 'none';
    }
    if(Document.elementExists('repeatable_genoo-dynamic-cta')){
        document.getElementById('themeMetaboxRowselect_cta_repeat').style.display = 'none';
    }
};


/**
 * Init
 */

Document.ready(window, function(e){
    // we're ready
    // reset fields
    Metabox.checkFields();
    // Attach events
    Event.attach(document.getElementById('cta_type'), 'change', Metabox.checkFields);
    Event.attach(document.getElementById('button_type'), 'change', Metabox.checkFields);
    Event.attach(document.getElementById('enable_cta_for_this_post'), 'change', Metabox.checkFields);
    Event.attach(document.getElementById('enable_cta_for_this_post_repeat'), 'change', Metabox.checkFields);
    // Validate
    // TODO: rewrite to normal js, instead of jQuery
    var form = jQuery("form[name='post']");
    jQuery(form).find("#publish").click(function(e){
        // prevent default
        e.preventDefault();
        // found
        var found = false;
        // Do we have dynamic cta box?
        if(Document.elementExists('repeatable_genoo-dynamic-cta')){
            // Go throu selected options
            jQuery('#repeatable_genoo-dynamic-cta select').each(function(){
                if(!jQuery(this).hasClass('empty')){
                    var title = jQuery(this).find(':selected').text();
                    // Whooa we have a winner
                    if(title !== undefined && (title === 'Select CTA' || title === 'Select Sidebar')){
                        var r = confirm("It seems like you have forgotten to select a sidebar or CTA for one or more dynamic CTA's, would like to continue?");
                        if(r == true){
                            jQuery("#ajax-loading").show();
                            jQuery(form).submit();
                        } else {
                            jQuery("#publish").removeClass().addClass("button-primary");
                            jQuery("#ajax-loading").hide();
                        }
                        found = true;
                        return false;
                    }
                }
            });
            if(found == false){
                jQuery("#ajax-loading").show();
                jQuery(form).submit();
            }
        } else {
            jQuery("#ajax-loading").show();
            jQuery(form).submit();
        }
    });
});