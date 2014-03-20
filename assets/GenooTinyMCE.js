/**
 * Genoo TinyMCE plugin
 *
 * @version 0.5.3
 * @author latorante.name
 */


(function(){
    tinymce.create('tinymce.plugins.Genoo', {
        init : function(ed, url){

            var queryVars = '';
            if(GenooVars.GenooTinyMCE){ queryVars = GenooVars.GenooTinyMCE; }

            ed.addButton('genooForm', {
                title : 'Add a default Genoo Form',
                cmd : 'genooForm',
                image : url + '/bgTinyMCE.png?v=2'
            });

            ed.addCommand('genooForm', function() {
                ed.windowManager.open({
                    file : url + '/GenooTineMCE.php?' + Admin.buildQuery(queryVars),
                    width : 200 + parseInt(ed.getLang('example.delta_width', 0)),
                    height : 205 + parseInt(ed.getLang('example.delta_height', 0)),
                    inline : 1
                }, {
                    plugin_url : url,
                    shortcodes: 'sss',
                    themes: 'bbb'
                });
            });
        },

//        not now Martin!
//        _do_gallery : function(co) {
//            return co.replace(/\[genooForm([^\]]*)\]/g, function(a,b){
//                return '<img src="'+tinymce.baseURL+'/plugins/wpgallery/img/t.gif" class="wpGallery mceItem" title="gallery'+tinymce.DOM.encode(b)+'" />';
//            });
//        },

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