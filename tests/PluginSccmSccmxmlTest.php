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

class PluginSccmSccmxmlTest extends GLPITestCase
{
    private PluginSccmSccmxml $xml;

    public function setUp(): void
    {
        parent::setUp();

        $this->xml = $this->getMockBuilder(PluginSccmSccmxml::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->xml->sxml = new SimpleXMLElement(
            "<?xml version='1.0' encoding='UTF-8'?><REQUEST><CONTENT><VERSIONCLIENT>test</VERSIONCLIENT></CONTENT><DEVICEID>test-device</DEVICEID><QUERY>INVENTORY</QUERY><PROLOG></PROLOG></REQUEST>",
        );
    }

    public function testSetHardwareUppercasesName(): void
    {
        $this->xml->username = '';
        $this->xml->data     = ['MD-SystemName' => 'workstation01', 'SD-UUID' => 'guid:1234', 'CSD-Domain' => ''];

        $this->xml->setHardware();

        $this->assertSame('WORKSTATION01', (string) $this->xml->sxml->CONTENT[0]->HARDWARE->NAME);
    }

    public function testSetHardwareStripsGuidPrefix(): void
    {
        $this->xml->username = '';
        $this->xml->data     = ['MD-SystemName' => 'pc', 'SD-UUID' => 'guid:1234-5678-ABCD', 'CSD-Domain' => ''];

        $this->xml->setHardware();

        $this->assertSame('1234-5678-ABCD', (string) $this->xml->sxml->CONTENT[0]->HARDWARE->UUID);
    }

    #[DataProvider('accessLogUsernameProvider')]
    public function testSetAccessLogResolvesUsername(array $data, string $expectedUsername): void
    {
        $this->xml->data = $data;

        $this->xml->setAccessLog();

        $this->assertSame($expectedUsername, $this->xml->username);
        $this->assertSame($expectedUsername, (string) $this->xml->sxml->CONTENT[0]->ACCESSLOG->USERID);
    }

    public static function accessLogUsernameProvider(): iterable
    {
        yield 'VrS takes priority over SDI and CSD' => [
            ['VrS-UserName' => 'alice', 'SDI-UserName' => 'bob', 'CSD-UserName' => 'DOMAIN charlie'],
            'alice',
        ];
        yield 'SDI used when VrS is empty' => [
            ['VrS-UserName' => '', 'SDI-UserName' => 'bob', 'CSD-UserName' => ''],
            'bob',
        ];
        yield 'CSD with domain prefix extracts login after space' => [
            ['VrS-UserName' => '', 'SDI-UserName' => '', 'CSD-UserName' => 'DOMAIN charlie'],
            'charlie',
        ];
        yield 'CSD without space used as-is' => [
            ['VrS-UserName' => '', 'SDI-UserName' => '', 'CSD-UserName' => 'localuser'],
            'localuser',
        ];
        yield 'all sources empty gives empty username' => [
            ['VrS-UserName' => '', 'SDI-UserName' => '', 'CSD-UserName' => ''],
            '',
        ];
    }

    #[DataProvider('biosDateProvider')]
    public function testSetBiosNormalizesReleaseDate(mixed $releaseDate, string $expectedBdate): void
    {
        $this->xml->data = [
            'CSD-Model'        => '',
            'SD-SystemRole'    => '',
            'CSD-Manufacturer' => '',
            'PBD-SerialNumber' => '',
            'PBD-ReleaseDate'  => $releaseDate,
            'PBD-Manufacturer' => '',
            'PBD-BiosVersion'  => '',
            'PBD-Version'      => '',
        ];

        $this->xml->setBios();

        $this->assertSame($expectedBdate, (string) $this->xml->sxml->CONTENT[0]->BIOS->BDATE);
    }

    public static function biosDateProvider(): iterable
    {
        yield 'valid string date' => ['Jan 01 2020', '01/01/2020'];
        yield 'end of year string date' => ['Dec 31 1999', '12/31/1999'];
        yield 'DateTime object' => [new DateTime('2020-06-15'), '06/15/2020'];
        yield 'invalid string preserved as-is' => ['invalid-date', 'invalid-date'];
    }

    #[DataProvider('networkTypeProvider')]
    public function testDetermineNetworkType(string $description, string $expected): void
    {
        $this->assertSame($expected, $this->xml->determineNetworkType($description));
    }

    public static function networkTypeProvider(): iterable
    {
        yield 'wi-fi keyword'    => ['Intel Wi-Fi 6 AX201',               'wifi'];
        yield 'wireless keyword' => ['Broadcom Wireless Network Adapter',  'wifi'];
        yield 'wifi keyword'     => ['Realtek WiFi 5GHz',                  'wifi'];
        yield 'ethernet default' => ['Intel I219-V Gigabit Ethernet',      'ethernet'];
        yield 'loopback'         => ['Software Loopback Interface',        'loopback'];
        yield 'bluetooth'        => ['Bluetooth RFCOMM Device',            'bluetooth'];
        yield 'bridge'           => ['VMware Network Bridge Adapter',      'bridge'];
        yield 'infiniband'       => ['InfiniBand HCA Adapter',             'infiniband'];
        yield 'aggregation'      => ['Link Aggregation Port',              'aggregate'];
        yield 'aggregate'        => ['Bond Aggregate Interface',           'aggregate'];
        yield 'alias'            => ['Ethernet Alias 0',                   'alias'];
        yield 'dialup'           => ['WAN Miniport Dialup',                'dialup'];
        yield 'dial-up'          => ['Generic Dial-Up Modem',              'dialup'];
        yield 'fibre channel'    => ['Emulex Fibre Channel HBA',           'fibrechannel'];
        yield 'fiber channel'    => ['QLogic Fiber Channel Adapter',       'fibrechannel'];
        yield 'case insensitive' => ['INTEL WI-FI 6',                     'wifi'];
        yield 'empty string'     => ['',                                   'ethernet'];
        yield 'unknown type'     => ['Generic Virtual Adapter',            'ethernet'];
    }

    public function testSetOSPopulatesNodes(): void
    {
        $this->xml->username = '';
        $this->xml->data     = [
            'MD-SystemName'  => 'pc',
            'SD-UUID'        => 'guid:1234',
            'CSD-Domain'     => '',
            'OSD-Version'    => '10.0.19041',
            'OSD-Caption'    => 'Microsoft Windows 10 Pro',
            'OSD-CSDVersion' => 'Service Pack 1',
            'CSD-SystemType' => 'x64-based PC',
        ];
        $this->xml->setHardware(); // creates HARDWARE node that setOS() appends to

        $this->xml->setOS();

        $os = $this->xml->sxml->CONTENT[0]->OPERATINGSYSTEM;
        $this->assertSame('10.0.19041', (string) $os->VERSION);
        $this->assertSame('Microsoft Windows 10 Pro', (string) $os->NAME);
        $this->assertSame('x64-based PC', (string) $os->ARCH);
        $this->assertSame('Service Pack 1', (string) $os->SERVICE_PACK);
        $this->assertSame('10.0.19041', (string) $this->xml->sxml->CONTENT[0]->HARDWARE->OSVERSION);
    }

    public function testSetAccountInfosSetsStaticValues(): void
    {
        $this->xml->setAccountInfos();

        $info = $this->xml->sxml->CONTENT[0]->ACCOUNTINFO;
        $this->assertSame('TAG', (string) $info->KEYNAME);
        $this->assertSame('SCCM', (string) $info->KEYVALUE);
    }

    public function testSetAntivirusAddsNode(): void
    {
        $this->xml->setAntivirus('Kaspersky Endpoint Security 12');

        $av = $this->xml->sxml->CONTENT[0]->ANTIVIRUS;
        $this->assertSame('Kaspersky Endpoint Security 12', (string) $av->NAME);
    }

    public function testSetUsersAddsLoginNode(): void
    {
        $this->xml->username = 'jdoe';

        $this->xml->setUsers();

        $this->assertSame('jdoe', (string) $this->xml->sxml->CONTENT[0]->USERS->LOGIN);
    }

    private function makeSccmMock(string ...$methods): PluginSccmSccm
    {
        return $this->getMockBuilder(PluginSccmSccm::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
    }

    private function makeSccmDbMock(): PluginSccmSccmdb
    {
        return $this->getMockBuilder(PluginSccmSccmdb::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testSetNetworksClassifiesIpv4(): void
    {
        $sccm = $this->makeSccmMock('getNetwork');
        $sccm->method('getNetwork')->willReturn([[
            'ND-IpAddress'  => '192.168.1.10',
            'ND-Name'       => 'Intel I219-V Gigabit Ethernet',
            'ND-IpSubnet'   => '255.255.255.0',
            'ND-DHCPServer' => '192.168.1.1',
            'ND-IpGateway'  => '192.168.1.254',
            'ND-MacAddress' => 'AA:BB:CC:DD:EE:FF',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setNetworks($this->makeSccmDbMock(), $sccm);

        $net = $this->xml->sxml->CONTENT[0]->NETWORKS[0];
        $this->assertSame('192.168.1.10', (string) $net->IPADDRESS);
        $this->assertSame(0, count($net->IPADDRESS6));
        $this->assertSame('ethernet', (string) $net->TYPE);
    }

    public function testSetNetworksClassifiesIpv6(): void
    {
        $sccm = $this->makeSccmMock('getNetwork');
        $sccm->method('getNetwork')->willReturn([[
            'ND-IpAddress'  => '2001:db8::1',
            'ND-Name'       => 'Intel I219-V Gigabit Ethernet',
            'ND-IpSubnet'   => '',
            'ND-DHCPServer' => '',
            'ND-IpGateway'  => '',
            'ND-MacAddress' => 'AA:BB:CC:DD:EE:FF',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setNetworks($this->makeSccmDbMock(), $sccm);

        $net = $this->xml->sxml->CONTENT[0]->NETWORKS[0];
        $this->assertSame(0, count($net->IPADDRESS));
        $this->assertSame('2001:db8::1', (string) $net->IPADDRESS6);
    }

    public function testSetNetworksSplitsCommaDelimitedIps(): void
    {
        $sccm = $this->makeSccmMock('getNetwork');
        $sccm->method('getNetwork')->willReturn([[
            'ND-IpAddress'  => '10.0.0.1,2001:db8::1',
            'ND-Name'       => 'Ethernet',
            'ND-IpSubnet'   => '',
            'ND-DHCPServer' => '',
            'ND-IpGateway'  => '',
            'ND-MacAddress' => 'AA:BB:CC:DD:EE:FF',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setNetworks($this->makeSccmDbMock(), $sccm);

        $networks = $this->xml->sxml->CONTENT[0]->NETWORKS;
        $this->assertCount(2, $networks);
        $this->assertSame('10.0.0.1', (string) $networks[0]->IPADDRESS);
        $this->assertSame('2001:db8::1', (string) $networks[1]->IPADDRESS6);
    }

    public function testSetSoftwaresFormatsInstallDate(): void
    {
        $sccm = $this->makeSccmMock('getSoftware');
        $sccm->method('getSoftware')->willReturn([[
            'ArPd-DisplayName' => 'My App',
            'ArPd-Version'     => '1.0',
            'ArPd-Publisher'   => 'Acme',
            'ArPd-InstallDate' => '20200115',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setSoftwares($this->makeSccmDbMock(), $sccm);

        $this->assertSame('15/01/2020', (string) $this->xml->sxml->CONTENT[0]->SOFTWARES[0]->INSTALLDATE);
    }

    public function testSetSoftwaresSkipsInvalidInstallDate(): void
    {
        $sccm = $this->makeSccmMock('getSoftware');
        $sccm->method('getSoftware')->willReturn([[
            'ArPd-DisplayName' => 'My App',
            'ArPd-InstallDate' => 'not-a-date',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setSoftwares($this->makeSccmDbMock(), $sccm);

        $this->assertSame(0, count($this->xml->sxml->CONTENT[0]->SOFTWARES[0]->INSTALLDATE));
    }

    public function testSetSoftwaresPreservesAmpersandInName(): void
    {
        $sccm = $this->makeSccmMock('getSoftware');
        $sccm->method('getSoftware')->willReturn([[
            'ArPd-DisplayName' => 'AT&T Software',
            'ArPd-Publisher'   => 'AT&T Corp',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setSoftwares($this->makeSccmDbMock(), $sccm);

        $sw = $this->xml->sxml->CONTENT[0]->SOFTWARES[0];
        $this->assertSame('AT&T Software', (string) $sw->NAME);
        $this->assertSame('AT&T Corp', (string) $sw->PUBLISHER);
    }

    public function testSetSoftwaresDetectsKasperskyAntivirus(): void
    {
        $sccm = $this->makeSccmMock('getSoftware');
        $sccm->method('getSoftware')->willReturn([[
            'ArPd-DisplayName' => 'Kaspersky Endpoint Security 12',
        ]]);
        $this->xml->device_id = 'test-device';

        $this->xml->setSoftwares($this->makeSccmDbMock(), $sccm);

        $this->assertSame(
            'Kaspersky Endpoint Security 12',
            (string) $this->xml->sxml->CONTENT[0]->ANTIVIRUS->NAME,
        );
    }

    public function testSetStoragesMultipliesSizeBy1024(): void
    {
        $sccm = $this->makeSccmMock('getStorages', 'getMedias');
        $sccm->method('getStorages')->willReturn([[
            'gld-TotalSize'    => '512',
            'gld-FreeSpace'    => '128',
            'gld-Description'  => 'Local Disk',
            'gld-Partition'    => 'C:',
            'gld-FileSystem'   => 'NTFS',
            'gld-MountingPoint' => 'C:',
            'gdi-Caption'      => 'Disk C:',
        ]]);
        $sccm->method('getMedias')->willReturn([]);
        $this->xml->device_id = 'test-device';

        $this->xml->setStorages($this->makeSccmDbMock(), $sccm);

        $drive = $this->xml->sxml->CONTENT[0]->DRIVES[0];
        $this->assertSame('524288', (string) $drive->TOTAL);
        $this->assertSame('131072', (string) $drive->FREE);
    }
}
