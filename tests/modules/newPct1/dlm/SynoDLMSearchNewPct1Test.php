<?php

require_once dirname(__FILE__) . '\..\..\..\..\..\vendor\autoload.php';
require_once dirname(__FILE__) . '\..\..\..\..\..\SynDsEsTorrent\modules\newPct1\dlm\SynoDLMSearchNewPct1.php';

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-09-13 at 04:16:04.
 */
class SynoDLMSearchNewPct1Test extends \SynDsEsTorrent\utils\baseDlmTest
{
    public function setUp()
    {
        parent::setObject(new modules\newPct1\dlm\SynoDLMSearchNewPct1());
    }

    /**
     * @covers SynoDLMSearchNewPct1::parse
     */
    public function testParse()
    {
        parent::parse();
    }

}