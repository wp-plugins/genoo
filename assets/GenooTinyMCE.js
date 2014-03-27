/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 *
 * Genoo TinyMCE plugin
 *
 * @version 0.7
 * @author latorante.name
 */


(function(){
    tinymce.create('tinymce.plugins.Genoo',{
        init : function(ed, url){

            var t = this;
            var queryVars = '';
            t.url = url;
            t._createButtons(url);

            // variables
            if(GenooVars.GenooTinyMCE){ queryVars = GenooVars.GenooTinyMCE; }

            // add button
            ed.addButton('genooForm',{
                title : 'Add a default Genoo Form',
                cmd : 'genooForm',
                image : url + '/bgTinyMCE.png?v=2'
            });

            // add command
            ed.addCommand('genooForm', function(){
                ed.windowManager.open({
                    file : url + '/GenooTinyMCE.php?edit=0&' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 205 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
            });

            // edit command
            ed.addCommand('genooFormEdit', function(string){
                // add selected
                queryVars['selected'] = string;
                ed.windowManager.open({
                    file : url + '/GenooTinyMCE.php?edit=1&' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 205 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
            });

            // replace shortcode before editor content set
            ed.onBeforeSetContent.add(function(ed, o){ o.content = t._do_shortcode(o.content); });

            // replace shortcode as its inserted into editor (which uses the exec command)
            ed.onExecCommand.add(function(ed, cmd) {
                tinyMCE.activeEditor.setContent( t._do_shortcode(tinyMCE.activeEditor.getContent()));
            });

            // replace the image back to shortcode on save
            ed.onPostProcess.add(function(ed, o){ if (o.get){ o.content = t._get_shortcode(o.content); } });

            // on init
            ed.onInit.add(function(ed){
                // iOS6 doesn't show the buttons properly on click, show them on 'touchstart'
                if ('ontouchstart' in window){
                    ed.dom.events.add(ed.getBody(), 'touchstart', function(e){
                        var target = e.target;
                        if (target.nodeName == 'IMG' && ed.dom.hasClass(target, 'genooFormShortcode')){
                            ed.selection.select(target);
                            ed.dom.events.cancel(e);
                            ed.plugins.wordpress._hideButtons();
                            ed.plugins.wordpress._showButtons(target, 'genooButtons');
                        }
                    });
                }
            });

            // on mouse down
            ed.onMouseDown.add(function(ed, e){
                if (e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'genooFormShortcode')){
                    ed.plugins.wordpress._hideButtons();
                    ed.plugins.wordpress._showButtons(e.target, 'genooButtons');
                }
            });

            // save original hide buttons
            ed.plugins.wordpress._hideButtonsOriginal = ed.plugins.wordpress._hideButtons;

            // extend original _hideButtons
            ed.plugins.wordpress._hideButtons = function(){
                // hide our buttons
                tinymce.DOM.hide('genooButtons');
                // run original hiding function
                ed.plugins.wordpress._hideButtonsOriginal();
            }
        },

        // genoo shortcode
        _do_shortcode : function(co){
            return co.replace(/\[genooForm([^\]]*)\]/g, function(a,b){
                return '<img src="'+tinymce.baseURL+'/plugins/wpgallery/img/t.gif" class="genooFormShortcode mceItem" title="genooForm'+tinymce.DOM.encode(b)+'" />';
            });
        },

        // genoo shortcode
        _get_shortcode : function(co){
            function getAttr(s, n) {
                n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
                return n ? tinymce.DOM.decode(n[1]) : '';
            };
            return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {
                var cls = getAttr(im, 'class');
                if (cls.indexOf('genooFormShortcode') != -1)
                    return '<p>['+tinymce.trim(getAttr(im, 'title'))+']</p>';
                return a;
            });
        },

        // create buttons
        _createButtons : function(url){

            // prep
            var DOM = tinymce.DOM, editButton, dellButton;
            if(DOM.get('genooButtons')) { return; }

            // buttons
            DOM.add(document.body, 'div', { id: 'genooButtons', style : 'display:none;' });

            // buttons
            editButton = DOM.add('genooButtons', 'img', { src : url + '/bgShortcodeEdit.png', id : 'genooEditForm', width : '24', height : '24', title : 'Edit'});
            dellButton = DOM.add('genooButtons', 'img', { src : url + '/bgShortcodeRemove.png', id : 'genooDeleteForm', width : '24', height : '24', title : 'Remove' });

            // delete button event
            tinymce.dom.Event.add(dellButton, 'mousedown', function(e){
                tinyMCE.activeEditor.windowManager.confirm("Are you sure? Please confirm to delete the form.", function(s) {
                    if (s){
                        var ed = tinymce.activeEditor, el = ed.selection.getNode();
                        if (el.nodeName == 'IMG' && ed.dom.hasClass(el, 'genooFormShortcode')){
                            ed.dom.remove(el);
                            ed.execCommand('mceRepaint');
                            ed.dom.events.cancel(e);
                        }
                    }
                });
                ed.plugins.wordpress._hideButtons();
            });

            // edit button event
            tinymce.dom.Event.add(editButton, 'mousedown', function(e) {
                var ed = tinymce.activeEditor,
                    el = ed.selection.getNode(),
                    attrs = ed.dom.getAttrib(el, 'title');
                ed.execCommand('genooFormEdit', attrs);
                ed.plugins.wordpress._hideButtons();
            });

        },


        // plugin info
        getInfo : function(){
            return {
                longname : 'Genoo Form',
                author : 'latorante.name',
                authorurl : 'http://latorante.name',
                infourl : '',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('genoo', tinymce.plugins.Genoo);
})();
