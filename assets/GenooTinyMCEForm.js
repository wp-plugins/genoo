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
 * @version 1.3.1
 * @author latorante.name
 */


(function(){


    /**
     * Version Compare
     *
     * @param h
     * @param g
     * @param c
     * @returns {*}
     */

    function versionCompare(h,g,c){
        this.php_js=this.php_js||{};this.php_js.ENV=this.php_js.ENV||{};var d=0,b=0,f=0,e={dev:-6,alpha:-5,a:-5,beta:-4,b:-4,RC:-3,rc:-3,"#":-2,p:1,pl:1},a=function(i){i=(""+i).replace(/[_\-+]/g,".");i=i.replace(/([^.\d]+)/g,".$1.").replace(/\.{2,}/g,".");return(!i.length?[-8]:i.split("."))};numVersion=function(i){return !i?0:(isNaN(i)?e[i]||-7:parseInt(i,10))};h=a(h);g=a(g);b=Math.max(h.length,g.length);for(d=0;d<b;d++){if(h[d]==g[d]){continue}h[d]=numVersion(h[d]);g[d]=numVersion(g[d]);if(h[d]<g[d]){f=-1;break}else{if(h[d]>g[d]){f=1;break}}}if(!c){return f}switch(c){case">":case"gt":return(f>0);case">=":case"ge":return(f>=0);case"<=":case"le":return(f<=0);case"==":case"=":case"eq":return(f===0);case"<>":case"!=":case"ne":return(f!==0);case"":case"<":case"lt":return(f<0);default:return null}
    }


    /**
     * Vars
     */

    /** TinyMCE version */
    var tinyMCEVer = tinymce.majorVersion + '.' + tinymce.minorVersion;

    /**
     * Go!
     */

    if(versionCompare(tinyMCEVer, '4', '>=')){

        /**
         * Genoo Form Shortcode
         */

        /****************************************************/

        tinymce.PluginManager.add('genoo', function(ed, url){

            var t = this;
            var queryVars = '';
            toolbarActive = true;
            t.url = url;

            // variables
            if(GenooVars.GenooTinyMCE){ queryVars = GenooVars.GenooTinyMCE; }

            /**
             * Replace gallery shortcodes
             */

            function replaceGenooShortcodes(content){
                return content.replace(/\[genooForm([^\]]*)\]/g, function(a,b){
                    return '<div class="genooFormTemp mceItem">' +
                        '<img src="'+tinymce.Env.transparentSrc+'" data-mce-resize="false" data-mce-placeholder="1" class="genooFormShortcode" title="genooForm'+tinymce.DOM.encode(b)+'" />' +
                        '</div>';
                });
            }

            /**
             * Restore genoo shortcode
             */

            function restoreGenooShortcode(content){
                function getAttr(s, n){
                    n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
                    return n ? tinymce.DOM.decode(n[1]) : '';
                };
                return content.replace(/(?:<div[^>]*>)*(<img[^>]+>)(?:<\/div>)*/g, function(a,im) {
                    var cls = getAttr(im, 'class');
                    if (cls.indexOf('genooFormShortcode') != -1)
                        return '<p>['+tinymce.trim(getAttr(im, 'title'))+']</p>';
                    return a;
                });
            }

            /**
             * Remove toolbar
             */

            function removeToolbar(){
                var toolbar = ed.dom.get('wp-image-toolbar');
                if (toolbar){ ed.dom.remove( toolbar ); }
                ed.dom.setAttrib( ed.dom.select( 'img[data-wp-imgselect]' ), 'data-wp-imgselect', null );
                toolbarActive = false;
            }

            /**
             * Add toolbar
             * @param node
             */

            function addToolbar(node){
                var rectangle, toolbarHtml, toolbar, left,
                    dom = ed.dom;

                // remove toolbars
                removeToolbar();

                // Don't add to placeholders
                if (!node || node.nodeName !== 'IMG') { return; }

                dom.setAttrib( node, 'data-wp-imgselect', 1 );
                rectangle = dom.getRect( node );

                toolbarHtml = '<div class="dashicons dashicons-edit editGenoo" data-mce-bogus="1"></div>' +
                    '<div class="dashicons dashicons-no-alt removeGenoo" data-mce-bogus="1"></div>';

                toolbar = dom.create( 'div', {
                    'id': 'wp-image-toolbar',
                    'data-mce-bogus': '1',
                    'contenteditable': false
                }, toolbarHtml );

                if ( ed.rtl ){ left = rectangle.x + rectangle.w - 82;  } else { left = rectangle.x; }

                ed.getBody().appendChild( toolbar );
                dom.setStyles( toolbar, { top: rectangle.y, left: left });
                toolbarActive = true;
            }

            /**
             * EVENTS
             */

            // on start and insert
            ed.on('BeforeSetContent', function(event){
                event.content = replaceGenooShortcodes( event.content );
            });

            // on post process
            ed.on('PostProcess', function(event){
                if (event.get){ event.content = restoreGenooShortcode(event.content); }
            });

            // mouseup
            ed.on('mouseup', function(event){
                var image,
                    node = event.target,
                    dom = ed.dom;
                if (event.button && event.button > 1){ return; }
                function unselect() { dom.removeClass( dom.select('img.wp-media-selected'), 'wp-media-selected'); }
                if(jQuery(node).hasClass('genooFormShortcode')){
                    addToolbar(node);
                }
            });

            // on click
            ed.on('click', function(e){
                if(jQuery(e.target).hasClass('editGenoo')){
                    var img = jQuery(e.target).closest('body').find('img[data-mce-selected="1"]');
                    ed.execCommand('genooFormEdit', true, img.attr('title'));
                } else if (jQuery(e.target).hasClass('removeGenoo')){
                    tinyMCE.activeEditor.windowManager.confirm("Are you sure? Please confirm to delete the form.", function(s){
                        if (s){
                            var img = jQuery(e.target).closest('body').find('img[data-mce-selected="1"]');
                            img.parent().remove();
                            //data-wp-imgselect="1" data-mce-selected="1"
                            removeToolbar();
                        }
                    });
                }
            });

            // add button
            ed.addButton('genooForm',{
                title : 'Add a default Genoo Form',
                cmd : 'genooForm',
                image : url + '/bgTinyMCE.png?v=2'
            });

            // add command
            ed.addCommand('genooForm', function(){
                ed.windowManager.open({
                    file : url + '/GenooTinyMCE.php?ver4=true&edit=0&' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 375 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
            });

            // refresh content correctly ... :)
            ed.addCommand('genooRefresh', function(){
                var contentos = ed.getContent();
                contentos = restoreGenooShortcode(contentos);
                contentos = replaceGenooShortcodes(contentos);
                ed.setContent(contentos);
            });

            // edit command
            ed.addCommand('genooFormEdit', function(ui, string){
                // add selected
                queryVars['selected'] = string;
                ed.windowManager.open({
                    file : url + '/GenooTinyMCE.php?ver4=true&edit=1&' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 375 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
                removeToolbar();
            });

        });


    } else if(versionCompare(tinyMCEVer, '3', '>=')){

        /**
         * Older versions
         */

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

                // nothing
                ed.addCommand('genooRefresh', function(){});

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
    }
})();
