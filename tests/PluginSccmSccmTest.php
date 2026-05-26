<?php

/**
 * -------------------------------------------------------------------------
 * SCCM plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of SCCM.
 *
 * SCCM is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * SCCM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SCCM. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2014-2023 by SCCM plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * -------------------------------------------------------------------------
 */

use Glpi\Tests\GLPITestCase;

class PluginSccmSccmTest extends GLPITestCase
{
    public function testGetcomputerQueryBaseStructure(): void
    {
        $query = PluginSccmSccm::getcomputerQuery();

        $this->assertStringContainsString('FROM Computer_System_DATA csd', $query);
        $this->assertStringContainsString("WHERE csd.MachineID is not null and csd.MachineID != ''", $query);
    }

    public function testGetcomputerQueryWithoutCollectionHasNoFilter(): void
    {
        $query = PluginSccmSccm::getcomputerQuery();

        $this->assertStringNotContainsString('v_FullCollectionMembership', $query);
    }

    public function testGetcomputerQueryEmptyCollectionHasNoFilter(): void
    {
        $query = PluginSccmSccm::getcomputerQuery('');

        $this->assertStringNotContainsString('v_FullCollectionMembership', $query);
    }

    public function testGetcomputerQueryWithCollectionAddsSubquery(): void
    {
        $query = PluginSccmSccm::getcomputerQuery('All Workstations');

        $this->assertStringContainsString('AND csd.MachineID IN', $query);
        $this->assertStringContainsString('v_FullCollectionMembership', $query);
        $this->assertStringContainsString("WHERE vc.Name = N'All Workstations'", $query);
    }

    public function testGetcomputerQueryEscapesSingleQuote(): void
    {
        $query = PluginSccmSccm::getcomputerQuery("O'Brien");

        $this->assertStringContainsString("N'O''Brien'", $query);
        $this->assertStringNotContainsString("N'O'Brien'", $query);
    }

    public function testGetcomputerQueryEscapesMultipleSingleQuotes(): void
    {
        $query = PluginSccmSccm::getcomputerQuery("It's O'Brien's");

        $this->assertStringContainsString("N'It''s O''Brien''s'", $query);
    }
}
