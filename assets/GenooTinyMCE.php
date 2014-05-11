<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

/**
 * Parse shortcode attributes
 *
 * @param $text
 * @return array|string
 */

function parseAtts($text){
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
    if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)){
        foreach ($match as $m) {
            if (!empty($m[1]))
                $atts[strtolower($m[1])] = stripcslashes($m[2]);
            elseif (!empty($m[3]))
                $atts[strtolower($m[3])] = stripcslashes($m[4]);
            elseif (!empty($m[5]))
                $atts[strtolower($m[5])] = stripcslashes($m[6]);
            elseif (isset($m[7]) and strlen($m[7]))
                $atts[] = stripcslashes($m[7]);
            elseif (isset($m[8]))
                $atts[] = stripcslashes($m[8]);
        }
    } else {
        $atts = ltrim($text);
    }
    return $atts;
}


/**
 * Prep url and relative url
 */

$urlPrep = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$urlRel = substr($urlPrep, 0, strpos($urlPrep , "wp-content"));


/**
 * Decide what to do
 */

$edit = (isset($_GET['edit']) && $_GET['edit'] == '1') ? true : false;
$selected = $edit ? (isset($_GET['selected']) ? parseAtts($_GET['selected']) : array()) : array();
$title = $edit ? 'Edit' : 'Insert';
$ver = (isset($_GET['ver4']) && $_GET['ver4'] == 'true') ? true : false;
?>
<!DOCTYPE html>
<head>
	<title><?php echo $title; ?> Genoo Form</title>
	<script type="text/javascript" src="<?php echo $urlRel; ?>wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script type="text/javascript">
	</script>
	<style type="text/css">
		html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}html,body{overflow-x:hidden}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:'';content:none}table{border-collapse:collapse;border-spacing:0}*:focus{outline:0}textarea{resize:none !important}input[type="search"]::-webkit-search-decoration,input[type="search"]::-webkit-search-cancel-button{display:none}input[type="search"]:focus,input[type="text"]:focus{cursor:text}img{border:0;-ms-interpolation-mode:bicubic;vertical-align:middle}label{cursor:pointer}input:invalid,input:-moz-ui-invalid{border:0 !important;outline:0;box-shadow:none;-moz-box-shadow:none;-webkit-box-shadow:none}input,button,select,select option,textarea{-moz-box-sizing:border-box;-webkit-box-sizing:border-box;-o-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;background:0;border:0;outline:0;box-shadow:none;-moz-box-shadow:none;-webkit-box-shadow:none;font:inherit;cursor:pointer;font-family:inherit;font-weight:normal;font-size:inherit;}select::-ms-expand{display:none !important}input[type="checkbox"]{-webkit-appearance:checkbox;-moz-appearance:checkbox;appearance:checkbox}input[type="submit"]:hover{cursor:pointer}
		a:active{ background-color: transparent; }
        body
        {
            background: <?php echo $ver ? '#fff' : '#f1f1f1'; ?>;
            font-family: 'Helvetice', 'Arial', 'Tahoma', sans-serif;
            font-size: 13px;
            line-height: 1.2em;
            padding: <?php echo $ver ? '16px' : '10px 0'; ?>;
        }
        .submit { display: inline-block; float: right; }
        .submit
        {
			background: #2ea2cc;
			background: -webkit-gradient(linear, left top, left bottom, from(#2ea2cc), to(#1e8cbe));
			background: -webkit-linear-gradient(top, #2ea2cc 0%,#1e8cbe 100%);
			background: linear-gradient(top, #2ea2cc 0%,#1e8cbe 100%);
			filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#2ea2cc', endColorstr='#1e8cbe',GradientType=0 );
			border-color: #0074a2;
			-webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.5);
			box-shadow: inset 0 1px 0 rgba(120,200,230,0.5);
			color: #fff !important;
			text-decoration: none;
			text-shadow: 0 1px 0 rgba(0,86,132,0.7);
			padding: 0 10px 1px;
			font-size: 13px;
			height: 24px;
			line-height: 26px;
			cursor: pointer;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			border: 1px solid #adadad;
            font-weight: bold;
            margin-top: 15px;
        }
        select
        {
            padding: 6px 4px;
            margin: 10px 0;
            border: 1px solid #b5b5b5;
            background: #fafafa;
			-webkit-border-radius: 3px;
			border-radius: 3px;
            width: 99%;
        }
	</style>
	<script type="text/javascript">
		var GenooForm = {
			e: '',
			init: function(e) {
				GenooForm.e = e;
				tinyMCEPopup.resizeToInnerSize();
			},
			insert: function createGenooShortcode(e){
                // get vals
				var form = document.getElementById("form");
				var formTheme = document.getElementById("formTheme");
				var formVal = form.options[form.selectedIndex].value;
				var formThemeVal = formTheme.options[formTheme.selectedIndex].value;
				// output
                var output = '[genooForm';
                if(formVal){ output += ' id=\''+formVal+'\''; }
				if(formThemeVal){ output += ' theme=\''+formThemeVal+'\''; }
				output += ']';
				// bam
                tinyMCEPopup.execCommand('mceReplaceContent', false, output);
                tinyMCEPopup.execCommand('genooRefresh');
				tinyMCEPopup.close();
			}
		}
		tinyMCEPopup.onInit.add(GenooForm.init, GenooForm);
	</script>
</head>
<html>
<body>
<form id="formShortcode">
	<p>
		<label for="form">Form:</label><br/>
        <select name="form" id="form">
			<option value="">Default</option>
			<?php
                if(isset($_GET['forms']) && !empty($_GET['forms'])){
                    foreach($_GET['forms'] as $key => $value){
                        $selectedVal = in_array($key, $selected) ? ' selected' : '';
                        echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                    }
                }
            ?>
        </select>
	</p>
	<p>
		<label for="formTheme">Theme:</label><br/>
		<select name="formTheme" id="formTheme">
			<option value="">Default</option>
            <?php
            if(isset($_GET['themes']) && !empty($_GET['themes'])){
                foreach($_GET['themes'] as $key => $value){
                    $selectedVal = in_array($key, $selected) ? ' selected' : '';
                    echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                }
            }
            ?>
		</select>
	</p>
	<p><a class="submit" href="javascript:GenooForm.insert(GenooForm.e)"><?php echo $title; ?></a></p>
</form>
</body>
</html>