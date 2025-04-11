<?php

/**
 * @package php-svg-lib
 * @link    http://github.com/PhenX/php-svg-lib
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license GNU LGPLv3+ http://www.gnu.org/copyleft/lesser.html
 */
namespace JFB_PDF\Vendor\Svg\Tag;

use JFB_PDF\Vendor\Sabberworm\CSS;
class StyleTag extends AbstractTag
{
    protected $text = "";
    public function end()
    {
        $parser = new CSS\Parser($this->text);
        $this->document->appendStyleSheet($parser->parse());
    }
    public function appendText($text)
    {
        $this->text .= $text;
    }
}
