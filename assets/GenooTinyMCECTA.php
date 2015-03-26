<?php
/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 * Genoo TinyMCE plugin - CTA
 *
 * @version 1
 * @author latorante.name
 */

// Include parent class
if(file_exists('TinyMCEHanlder.php')){
    require_once 'TinyMCEHanlder.php';
}


/**
 * Class GenooTinyMCECTA
 */

class GenooTinyMCECTA extends TinyMCEHanlder
{
    /** @var string */
    public $cta;
    /** @var array */
    public $ctas;


    /**
     * Constructor
     */

    public function __construct()
    {
        parent::__construct('genooCTA');
    }


    /**
     * Resolve additional variables
     */

    public function resolveSecond()
    {
        $this->cta = (isset($_GET['cta']) && $_GET['cta'] == 'true') ? true : false;
        $this->ctas = !empty($_GET['ctas']) ? $_GET['ctas'] : array();
    }


    /**
     * Genoo CTA pop-up javascript
     */

    public function renderJavascript()
    {
        ?>
        // Output
        var output = '';
        // get vals
        var cta = document.getElementById("cta");
        var ctaVal = cta.options[cta.selectedIndex].value;
        var align = document.getElementById("align");
        var alignVal = align.options[align.selectedIndex].value;
        // output
        output += '[<?php echo $this->shortcode; ?>';
        if(ctaVal){ output += ' id=\''+ctaVal+'\''; }
        if(alignVal){ output += ' align=\''+alignVal+'\''; }
        output += ']';
        // bam
        tinyMCEPopup.execCommand('mceReplaceContent', false, output);
        tinyMCEPopup.execCommand('<?php echo $this->refresh; ?>');
        tinyMCEPopup.close();
        <?php
    }


    /**
     * Genoo CTA pop-up form
     */

    public function renderForm()
    {
        ?>
        <p>
            <label for="cta">CTA:</label><br/>
            <?php if(isset($this->ctas) && !empty($this->ctas)){ ?>
                <select name="cta" id="cta">
                    <?php
                    foreach($this->ctas as $key => $value){
                        $selectedVal = in_array($key, $this->selected) ? ' selected' : '';
                        echo '<option value="'. $key .'" '. $selectedVal .'>'. $value .'</option>';
                    }
                    ?>
                </select>
            <?php } else { ?>
                <strong>You don't have any CTA's in your WordPress installation.</strong>
            <?php } ?>
        </p>
        <p>
            <label for="align">Align:</label><br/>
            <select name="align" id="align">
                <option value="">None</option>
                <option value="left" <?php echo in_array('left', $this->selected) ? 'selected' : '' ?>>Left</option>
                <option value="right" <?php echo in_array('right', $this->selected) ? 'selected' : '' ?>>Right</option>
            </select>
        </p>
        <?php
    }
}

new GenooTinyMCECTA();