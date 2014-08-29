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
         * Genoo CTA Shortcode
         */

        /****************************************************/

        tinymce.PluginManager.add('genooCTA', function(ed, url){

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
                return content.replace(/\[genooCTA([^\]]*)\]/g, function(a,b){
                    var title = tinymce.DOM.encode(b);
                    var elClass = '';
                    if(title.indexOf('left') >= 0){
                        elClass = 'genooLeft';
                    } else if (title.indexOf('right') >= 0){
                        elClass = 'genooRight';
                    }
                    return '<div class="genooFormTemp mceItem '+ elClass +'">' +
                        '<img src="'+tinymce.Env.transparentSrc+'" data-mce-resize="false" data-mce-placeholder="1" class="genooCTAShortcode" title="genooCTA'+ title +'" />' +
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
                    if (cls.indexOf('genooCTAShortcode') != -1)
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
                ed.dom.setAttrib( ed.dom.select('img[data-wp-imgselect]'), 'data-wp-imgselect', null );
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

                dom.setAttrib(node, 'data-wp-imgselect', 1 );
                rectangle = dom.getRect(node);

                toolbarHtml = '<div class="dashicons dashicons-edit editGenooCTA" data-mce-bogus="1"></div>' +
                    '<div class="dashicons dashicons-no-alt removeGenooCTA" data-mce-bogus="1"></div>';

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
                event.content = replaceGenooShortcodes(event.content);
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
                if(jQuery(node).hasClass('genooCTAShortcode')){
                    addToolbar(node);
                }
            });

            // on click
            ed.on('click', function(e){
                if(jQuery(e.target).hasClass('editGenooCTA')){
                    var img = jQuery(e.target).closest('body').find('img[data-mce-selected="1"]');
                    ed.execCommand('genooCTAEdit', true, img.attr('title'));
                } else if (jQuery(e.target).hasClass('removeGenooCTA')){
                    tinyMCE.activeEditor.windowManager.confirm("Are you sure? Please confirm to delete the cta.", function(s) {
                        if (s){
                            var img = jQuery(e.target).closest('body').find('img[data-mce-selected="1"]');
                            img.parent().remove();
                            removeToolbar();
                        }
                    });
                }
            });

            // add button
            ed.addButton('genooCTA',{
                title : 'Add Genoo CTA',
                cmd : 'genooCTA',
                image : url + '/bgTinyMCECTA.png?v=2'
            });

            // add command
            ed.addCommand('genooCTA', function(){
                ed.windowManager.open({
                    file : url + '/GenooTinyMCE.php?ver4=true&cta=true&edit=0&' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 200 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
            });

            // refresh content correctly ... :)
            ed.addCommand('genooCTARefresh', function(){
                var contentos = ed.getContent();
                contentos = restoreGenooShortcode(contentos);
                contentos = replaceGenooShortcodes(contentos);
                ed.setContent(contentos);
            });

            // edit command
            ed.addCommand('genooCTAEdit', function(ui, string){
                // add selected
                queryVars['selected'] = string;
                ed.windowManager.open({
                    file : url + '/GenooTinyMCE.php?ver4=true&cta=true&edit=1&' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 200 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                });
                removeToolbar();
            });

        });


    } else if(versionCompare(tinyMCEVer, '3', '>=')){
        /**
         * Older versions
         */
    }
})();