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
 * Metabox
 * @type {*|Object}
 */

var Metabox = Metabox || {};


/**
 * Change cta link
 *
 * @param id
 */

Metabox.changeCTALink = function(id){
    // TODO: add change link
};


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
    if (typeof(document.getElementById('genoo-cta-info')) != 'undefined' && document.getElementById('genoo-cta-info') != null){
        // is form?
        if(document.getElementById('cta_type').options[document.getElementById('cta_type').selectedIndex].value == 'form'){
            document.getElementById('themeMetaboxRowform').style.display = 'block';
            document.getElementById('themeMetaboxRowform_theme').style.display = 'block';
            document.getElementById('themeMetaboxRowcta_type').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_type').style.display = 'block';
            document.getElementById('button_url').value = '';
            document.getElementById('open_in_new_window').checked = false;
        } else {
            document.getElementById('themeMetaboxRowcta_type').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_url').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_type').style.display = 'block';
            document.getElementById('themeMetaboxRowopen_in_new_window').style.display = 'block';
        }
        // button type
        if(document.getElementById('button_type').options[document.getElementById('button_type').selectedIndex].value == 'html'){
            document.getElementById('themeMetaboxRowbutton_text').style.display = 'block';
            document.getElementById('button_image').value = '';
            document.getElementById('button_hover_image').value = '';
        } else {
            document.getElementById('themeMetaboxRowbutton_image').style.display = 'block';
            document.getElementById('themeMetaboxRowbutton_hover_image').style.display = 'block';
            document.getElementById('button_text').value = '';
        }
    }
    // page metabox
    if (typeof(document.getElementById('genoo-cta')) != 'undefined' && document.getElementById('genoo-cta') != null){
        if(document.getElementById('enable_cta_for_this_post').checked){
            // is form?
            document.getElementById('themeMetaboxRowselect_cta').style.display = 'block';
            document.getElementById('themeMetaboxRowhtml').style.display = 'block';
        }
    }
};


/**
 * Hide all
 */

Metabox.hideAll = function()
{
    if (typeof(document.getElementById('genoo-cta-info')) != 'undefined' && document.getElementById('genoo-cta-info') != null){
        document.getElementById('themeMetaboxRowcta_type').style.display = 'none';
        document.getElementById('themeMetaboxRowform').style.display = 'none';
        document.getElementById('themeMetaboxRowform_theme').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_type').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_url').style.display = 'none';
        document.getElementById('themeMetaboxRowopen_in_new_window').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_text').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_image').style.display = 'none';
        document.getElementById('themeMetaboxRowbutton_hover_image').style.display = 'none';
    }
    if (typeof(document.getElementById('genoo-cta')) != 'undefined' && document.getElementById('genoo-cta') != null){
        // is form?
        document.getElementById('themeMetaboxRowselect_cta').style.display = 'none';
        document.getElementById('themeMetaboxRowhtml').style.display = 'none';
    }
};


/**
 * Ready, go
 */

jQuery(function(){
    Metabox.checkFields();
    jQuery('input#enable_cta_for_this_post, select#cta_type, select#button_type').live('change', function(event){
        Metabox.checkFields();
    });
});