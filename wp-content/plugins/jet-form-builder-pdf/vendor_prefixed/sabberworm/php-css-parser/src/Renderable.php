<?php

namespace JFB_PDF\Vendor\Sabberworm\CSS;

interface Renderable
{
    /**
     * @return string
     */
    public function __toString();
    /**
     * @return string
     */
    public function render(OutputFormat $oOutputFormat);
    /**
     * @return int
     */
    public function getLineNo();
}
