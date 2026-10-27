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

use Glpi\Tests\DbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PluginSccmConfigTest extends DbTestCase
{
    public static function secretFieldProvider(): iterable
    {
        yield 'database password' => ['sccmdb_password'];
        yield 'inventory credentials' => ['auth_info'];
    }

    #[DataProvider('secretFieldProvider')]
    public function testSecretIsEncrypted(string $field): void
    {
        $config = $this->createItem(PluginSccmConfig::class, ['name' => 'test', $field => 'secret'], [$field]);

        $this->assertNotSame('secret', $config->fields[$field]);
        $this->assertSame('secret', (new GLPIKey())->decrypt($config->fields[$field]));
    }

    #[DataProvider('secretFieldProvider')]
    public function testEmptySecretKeepsStoredValue(string $field): void
    {
        $config = $this->createItem(PluginSccmConfig::class, ['name' => 'test', $field => 'secret'], [$field]);

        $this->updateItem(PluginSccmConfig::class, $config->getID(), [$field => ''], [$field]);

        $this->assertTrue($config->getFromDB($config->getID()));
        $this->assertSame('secret', (new GLPIKey())->decrypt($config->fields[$field]));
    }

    #[DataProvider('secretFieldProvider')]
    public function testClearCheckboxErasesSecret(string $field): void
    {
        $config = $this->createItem(PluginSccmConfig::class, ['name' => 'test', $field => 'secret'], [$field]);

        // The form still posts the displayed value along with the checkbox
        $this->updateItem(
            PluginSccmConfig::class,
            $config->getID(),
            [$field => 'secret', '_blank_' . $field => '1'],
            [$field, '_blank_' . $field],
        );

        $this->assertTrue($config->getFromDB($config->getID()));
        $this->assertEmpty($config->fields[$field]);
    }
}
