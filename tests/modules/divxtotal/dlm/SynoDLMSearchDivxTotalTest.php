<?php

/*
    This file is part of SynDsEsTorrent.

    SynDsEsTorrent is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    SynDsEsTorrent is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with SynDsEsTorrent.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace tests\modules\divxtotal\dlm;

use modules\divxtotal\dlm\SynoDLMSearchDivxTotal;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-09-13 at 04:16:04.
 */
class SynoDLMSearchDivxTotalTest extends \utils\baseDlmTest
{
    public function setUp()
    {
        parent::setObject(new SynoDLMSearchDivxTotal());
    }
    /**
     * @covers modules\divxtotal\dlm\SynoDLMSearchDivxTotal::parse
     */
    public function testParse()
    {
        parent::parse();
    }

    /**
     * @covers modules\divxtotal\dlm\SynoDLMSearchDivxTotal::__construct
     */
    public function testLoadInfo()
    {
        parent::loadInfoTest();
    }
}
