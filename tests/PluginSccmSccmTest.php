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
use PHPUnit\Framework\Attributes\DataProvider;

class PluginSccmSccmTest extends GLPITestCase
{
    private function callSanitizeRow(array $row): array
    {
        $sccm = $this->getMockBuilder(PluginSccmSccm::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        return (new \ReflectionMethod(PluginSccmSccm::class, 'sanitizeRow'))->invoke($sccm, $row);
    }

    public function testSanitizeRowPreservesCleanString(): void
    {
        $result = $this->callSanitizeRow(['name' => 'Intel(R) Corporation']);
        $this->assertSame('Intel(R) Corporation', $result['name']);
    }

    public function testSanitizeRowPreservesNonStringValues(): void
    {
        $result = $this->callSanitizeRow(['int' => 42, 'null' => null, 'bool' => true]);
        $this->assertSame(42, $result['int']);
        $this->assertNull($result['null']);
        $this->assertTrue($result['bool']);
    }

    public function testSanitizeRowPreservesValidXmlWhitespace(): void
    {
        $result = $this->callSanitizeRow(['ws' => "line1\ttabbed\nline2\rreturn"]);
        $this->assertSame("line1\ttabbed\nline2\rreturn", $result['ws']);
    }

    #[DataProvider('illegalXmlCharProvider')]
    public function testSanitizeRowStripsIllegalXmlChars(string $input, string $expected): void
    {
        $result = $this->callSanitizeRow(['v' => $input]);
        $this->assertSame($expected, $result['v']);
    }

    public static function illegalXmlCharProvider(): iterable
    {
        yield 'null byte NCHAR padding'        => ["Intel\x00\x00\x00",           'Intel'];
        yield 'null byte mid-string'           => ["Inte\x00l",                   'Intel'];
        yield 'U+FFFE noncharacter'            => ["Intel\xEF\xBF\xBE",           'Intel'];
        yield 'U+FFFF noncharacter'            => ["Intel\xEF\xBF\xBF",           'Intel'];
        yield 'U+FFFE and U+FFFF combined'     => ["Corp\xEF\xBF\xBE\xEF\xBF\xBF", 'Corp'];
        yield 'SOH U+0001'                     => ["Intel\x01Corp",               'IntelCorp'];
        yield 'control chars U+0001–U+0008'    => ["A\x01\x02\x03\x04\x05\x06\x07\x08Z", 'AZ'];
        yield 'vertical tab U+000B'            => ["Intel\x0BCorp",               'IntelCorp'];
        yield 'form feed U+000C'               => ["Intel\x0CCorp",               'IntelCorp'];
        yield 'control chars U+000E–U+001F'    => ["A\x0E\x0F\x10\x1FZ",         'AZ'];
        yield 'issue #181 production value'    => [
            "Intel(R) Corporation\xEF\xBF\xBE\xEF\xBF\xBF",
            'Intel(R) Corporation',
        ];
    }

    public function testSanitizeRowProducesLoadableXml(): void
    {
        $dirty = "Publisher\x01With\x0BControl\xEF\xBF\xBFChars\x00trailing";
        $result = $this->callSanitizeRow(['pub' => $dirty]);

        $sxml = new SimpleXMLElement(
            "<?xml version='1.0' encoding='UTF-8'?><R><PUBLISHER></PUBLISHER></R>",
        );
        $sxml->PUBLISHER[0] = $result['pub'];
        $file = tempnam(sys_get_temp_dir(), 'sccm_test_') . '.ocs';
        $sxml->asXML($file);

        libxml_use_internal_errors(true);
        $loaded = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        unlink($file);

        $this->assertNotFalse($loaded, 'XML must be loadable after sanitization');
        $this->assertEmpty($errors, 'No libxml errors expected');
        $this->assertSame('PublisherWithControlCharstrailing', (string) $loaded->PUBLISHER);
    }

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
