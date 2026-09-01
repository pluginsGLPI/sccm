-- -------------------------------------------------------------------------
-- SCCM plugin for GLPI - fixture database for local functional testing
-- -------------------------------------------------------------------------
-- Re-creates a minimal subset of the Microsoft SCCM / MECM database: only
-- the tables and views the plugin actually queries (see inc/sccm.class.php),
-- created as plain tables, plus a couple of fixture machines.
--
-- Idempotent: safe to run again to reset the fixture data.
--
--   docker compose exec -T mssql /opt/mssql-tools18/bin/sqlcmd \
--     -C -S localhost -U sa -P 'Glpi_Sccm_2026!' -i /sql/sccm-schema.sql
--
-- or simply:  make sccm-db-seed
-- -------------------------------------------------------------------------

IF DB_ID('CM_TST') IS NULL
BEGIN
    CREATE DATABASE CM_TST;
END
GO

USE CM_TST;
GO

-- ---- schema --------------------------------------------------------------

DROP TABLE IF EXISTS Computer_System_DATA;
DROP TABLE IF EXISTS Motherboard_DATA;
DROP TABLE IF EXISTS Operating_System_DATA;
DROP TABLE IF EXISTS PC_BIOS_DATA;
DROP TABLE IF EXISTS Processor_DATA;
DROP TABLE IF EXISTS Network_DATA;
DROP TABLE IF EXISTS System_DISC;
DROP TABLE IF EXISTS System_DATA;
DROP TABLE IF EXISTS v_R_System;
DROP TABLE IF EXISTS v_GS_WORKSTATION_STATUS;
DROP TABLE IF EXISTS v_GS_NETWORK_ADAPTER;
DROP TABLE IF EXISTS v_GS_ADD_REMOVE_PROGRAMS;
DROP TABLE IF EXISTS v_GS_ADD_REMOVE_PROGRAMS_64;
DROP TABLE IF EXISTS v_GS_PHYSICAL_MEMORY;
DROP TABLE IF EXISTS v_GS_VIDEO_CONTROLLER;
DROP TABLE IF EXISTS v_GS_SOUND_DEVICE;
DROP TABLE IF EXISTS v_GS_LOGICAL_DISK;
DROP TABLE IF EXISTS v_gs_Disk;
DROP TABLE IF EXISTS v_GS_CDROM;
DROP TABLE IF EXISTS v_FullCollectionMembership;
DROP TABLE IF EXISTS v_Collection;
GO

CREATE TABLE Computer_System_DATA (
    MachineID      int           NOT NULL,
    Description00   nvarchar(255) NULL,
    Domain00       nvarchar(255) NULL,
    Manufacturer00 nvarchar(255) NULL,
    Model00        nvarchar(255) NULL,
    Roles00        nvarchar(512) NULL,
    SystemType00   nvarchar(64)  NULL,
    UserName00     nvarchar(255) NULL,
    TimeKey        nvarchar(64)  NULL
);

CREATE TABLE Motherboard_DATA (
    MachineID    int           NOT NULL,
    SystemName00 nvarchar(255) NULL
);

CREATE TABLE Operating_System_DATA (
    MachineID                 int           NOT NULL,
    BuildNumber00             nvarchar(64)  NULL,
    Caption00                 nvarchar(255) NULL,
    CSDVersion00              nvarchar(128) NULL,
    BootDevice00              nvarchar(255) NULL,
    InstallDate00             nvarchar(64)  NULL,
    LastBootUpTime00          nvarchar(64)  NULL,
    Manufacturer00            nvarchar(255) NULL,
    Name00                    nvarchar(255) NULL,
    Organization00            nvarchar(255) NULL,
    RegisteredUser00          nvarchar(255) NULL,
    TotalVirtualMemorySize00  nvarchar(64)  NULL,
    TotalVisibleMemorySize00  nvarchar(64)  NULL,
    Version00                 nvarchar(64)  NULL
);

CREATE TABLE PC_BIOS_DATA (
    MachineID           int           NOT NULL,
    SerialNumber00      nvarchar(128) NULL,
    ReleaseDate00       datetime      NULL,
    Name00              nvarchar(255) NULL,
    SMBIOSBIOSVersion00 nvarchar(128) NULL,
    Version00           nvarchar(128) NULL,
    Manufacturer00      nvarchar(255) NULL
);

CREATE TABLE Processor_DATA (
    MachineID                   int           NOT NULL,
    Manufacturer00              nvarchar(128) NULL,
    Name00                      nvarchar(255) NULL,
    NormSpeed00                 nvarchar(32)  NULL,
    AddressWidth00              nvarchar(16)  NULL,
    CPUKey00                    nvarchar(64)  NULL,
    NumberOfCores00             nvarchar(16)  NULL,
    NumberOfLogicalProcessors00 nvarchar(16)  NULL
);

CREATE TABLE Network_DATA (
    MachineID          int           NOT NULL,
    IPAddress00        nvarchar(512) NULL,
    MACAddress00       nvarchar(64)  NULL,
    IPSubnet00         nvarchar(128) NULL,
    DefaultIPGateway00 nvarchar(128) NULL,
    DHCPServer00       nvarchar(64)  NULL,
    DNSDomain00        nvarchar(255) NULL,
    ServiceName00      nvarchar(128) NULL
);

CREATE TABLE System_DISC (
    ItemKey    int           NOT NULL,
    User_Name0 nvarchar(255) NULL
);

CREATE TABLE System_DATA (
    MachineID   int           NOT NULL,
    SMSID0      nvarchar(64)  NULL,
    SystemRole0 nvarchar(64)  NULL
);

CREATE TABLE v_R_System (
    ResourceID int           NOT NULL,
    User_Name0 nvarchar(255) NULL,
    Name0      nvarchar(255) NULL
);

CREATE TABLE v_GS_WORKSTATION_STATUS (
    ResourceID int      NOT NULL,
    LastHWScan datetime NULL
);

CREATE TABLE v_GS_NETWORK_ADAPTER (
    ResourceID  int           NOT NULL,
    Name0       nvarchar(255) NULL,
    ServiceName0 nvarchar(128) NULL
);

CREATE TABLE v_GS_ADD_REMOVE_PROGRAMS (
    ResourceID  int           NOT NULL,
    DisplayName0 nvarchar(255) NULL,
    InstallDate0 nvarchar(32)  NULL,
    Version0    nvarchar(64)  NULL,
    Publisher0  nvarchar(255) NULL
);

CREATE TABLE v_GS_ADD_REMOVE_PROGRAMS_64 (
    ResourceID  int           NOT NULL,
    DisplayName0 nvarchar(255) NULL,
    InstallDate0 nvarchar(32)  NULL,
    Version0    nvarchar(64)  NULL,
    Publisher0  nvarchar(255) NULL
);

CREATE TABLE v_GS_PHYSICAL_MEMORY (
    ResourceID    int           NOT NULL,
    Capacity0     nvarchar(32)  NULL,
    Caption0      nvarchar(128) NULL,
    Description0  nvarchar(255) NULL,
    FormFactor0   nvarchar(32)  NULL,
    Manufacturer0 nvarchar(128) NULL,
    Removable0    nvarchar(16)  NULL,
    Speed0        nvarchar(32)  NULL,
    BankLabel0    nvarchar(64)  NULL,
    GroupID       int           NULL
);

CREATE TABLE v_GS_VIDEO_CONTROLLER (
    ResourceID                   int           NOT NULL,
    VideoProcessor0              nvarchar(255) NULL,
    AdapterRAM0                  bigint        NULL,
    Name0                        nvarchar(255) NULL,
    CurrentHorizontalResolution0 nvarchar(16)  NULL,
    CurrentVerticalResolution0   nvarchar(16)  NULL,
    GroupID                      int           NULL
);

CREATE TABLE v_GS_SOUND_DEVICE (
    ResourceID   int           NOT NULL,
    Description0  nvarchar(255) NULL,
    Manufacturer0 nvarchar(128) NULL,
    Name0        nvarchar(255) NULL
);

CREATE TABLE v_GS_LOGICAL_DISK (
    ResourceID  int           NOT NULL,
    Description0 nvarchar(255) NULL,
    DeviceID0   nvarchar(16)  NULL,
    FileSystem0 nvarchar(32)  NULL,
    Size0       bigint        NULL,
    FreeSpace0  bigint        NULL,
    VolumeName0 nvarchar(128) NULL,
    GroupID     int           NULL
);

CREATE TABLE v_gs_Disk (
    ResourceID int           NOT NULL,
    GroupID    int           NULL,
    Caption0   nvarchar(255) NULL
);

CREATE TABLE v_GS_CDROM (
    ResourceID   int           NOT NULL,
    Description0  nvarchar(255) NULL,
    Manufacturer0 nvarchar(128) NULL,
    Caption0     nvarchar(255) NULL,
    Name0        nvarchar(255) NULL,
    SCSITargetID0 nvarchar(16)  NULL,
    MediaType0   nvarchar(64)  NULL
);

CREATE TABLE v_FullCollectionMembership (
    ResourceID   int          NOT NULL,
    CollectionID nvarchar(16) NOT NULL
);

CREATE TABLE v_Collection (
    CollectionID nvarchar(16)  NOT NULL,
    Name         nvarchar(255) NULL
);
GO

-- ---- fixture data -----------------------------------------------------
-- Two machines: 16777220 (PC-DEV-01, full hardware) and 16777221 (PC-DEV-02).

INSERT INTO Computer_System_DATA
    (MachineID, Description00, Domain00, Manufacturer00, Model00, Roles00, SystemType00, UserName00, TimeKey) VALUES
    (16777220, N'AT/AT COMPATIBLE', N'GLPI', N'LENOVO', N'20XW00 ThinkPad T14', N'SMS Client', N'x64-based PC', N'GLPI\alice',   N'2026-08-30 04:12:00'),
    (16777221, N'AT/AT COMPATIBLE', N'GLPI', N'Dell Inc.', N'OptiPlex 7090',    N'SMS Client', N'x64-based PC', N'GLPI\bob',     N'2026-08-30 04:12:30');

INSERT INTO Motherboard_DATA (MachineID, SystemName00) VALUES
    (16777220, N'PC-DEV-01'),
    (16777221, N'PC-DEV-02');

INSERT INTO Operating_System_DATA
    (MachineID, BuildNumber00, Caption00, CSDVersion00, BootDevice00, InstallDate00, LastBootUpTime00,
     Manufacturer00, Name00, Organization00, RegisteredUser00, TotalVirtualMemorySize00, TotalVisibleMemorySize00, Version00) VALUES
    (16777220, N'22631', N'Microsoft Windows 11 Enterprise', N'', N'\Device\HarddiskVolume1', N'20240102', N'20260830060000',
     N'Microsoft Corporation', N'Microsoft Windows 11 Enterprise|C:\WINDOWS|\Device\Harddisk0\Partition2', N'GLPI', N'alice',
     N'33554432', N'16777216', N'10.0.22631'),
    (16777221, N'19045', N'Microsoft Windows 10 Pro', N'', N'\Device\HarddiskVolume1', N'20230510', N'20260830055000',
     N'Microsoft Corporation', N'Microsoft Windows 10 Pro|C:\WINDOWS|\Device\Harddisk0\Partition2', N'GLPI', N'bob',
     N'16777216', N'8388608', N'10.0.19045');

INSERT INTO PC_BIOS_DATA
    (MachineID, SerialNumber00, ReleaseDate00, Name00, SMBIOSBIOSVersion00, Version00, Manufacturer00) VALUES
    (16777220, N'PF3ABCDE', '2023-02-01T00:00:00', N'N3AET42W (1.28)', N'N3AET42W', N'LENOVO - 1280', N'LENOVO'),
    (16777221, N'7ABCDX3',  '2022-11-15T00:00:00', N'2.14.0',          N'2.14.0',   N'DELL - 20220101', N'Dell Inc.');

INSERT INTO System_DATA (MachineID, SMSID0, SystemRole0) VALUES
    (16777220, N'GUID:0AA1B2C3-1111-2222-3333-444455556666', N'Workstation'),
    (16777221, N'GUID:0BB2C3D4-2222-3333-4444-555566667777', N'Workstation');

INSERT INTO System_DISC (ItemKey, User_Name0) VALUES
    (16777220, N'GLPI\alice'),
    (16777221, N'GLPI\bob');

INSERT INTO v_R_System (ResourceID, User_Name0, Name0) VALUES
    (16777220, N'GLPI\alice', N'PC-DEV-01'),
    (16777221, N'GLPI\bob',   N'PC-DEV-02');

INSERT INTO v_GS_WORKSTATION_STATUS (ResourceID, LastHWScan) VALUES
    (16777220, '2026-08-30T03:55:00'),
    (16777221, '2026-08-30T02:40:00');

-- --- machine 16777220: full hardware -------------------------------------
INSERT INTO Processor_DATA
    (MachineID, Manufacturer00, Name00, NormSpeed00, AddressWidth00, CPUKey00, NumberOfCores00, NumberOfLogicalProcessors00) VALUES
    (16777220, N'GenuineIntel', N'12th Gen Intel(R) Core(TM) i7-1265U', N'2700', N'64', N'CPU0', N'10', N'12');

INSERT INTO Network_DATA
    (MachineID, IPAddress00, MACAddress00, IPSubnet00, DefaultIPGateway00, DHCPServer00, DNSDomain00, ServiceName00) VALUES
    (16777220, N'10.20.30.40, fe80::1', N'AA:BB:CC:11:22:33', N'255.255.255.0', N'10.20.30.1', N'10.20.30.2', N'glpi.lan', N'e1dexpress');

INSERT INTO v_GS_NETWORK_ADAPTER (ResourceID, Name0, ServiceName0) VALUES
    (16777220, N'Intel(R) Ethernet Connection I219-V', N'e1dexpress');

INSERT INTO v_GS_ADD_REMOVE_PROGRAMS_64 (ResourceID, DisplayName0, InstallDate0, Version0, Publisher0) VALUES
    (16777220, N'7-Zip 23.01 (x64)',                 N'20240210', N'23.01',        N'Igor Pavlov'),
    (16777220, N'Mozilla Firefox (x64 en-US)',       N'20260805', N'128.2.0',      N'Mozilla'),
    (16777220, N'Kaspersky Endpoint Security 12.5',  N'20260101', N'12.5.0.539',   N'AO Kaspersky Lab');

INSERT INTO v_GS_ADD_REMOVE_PROGRAMS (ResourceID, DisplayName0, InstallDate0, Version0, Publisher0) VALUES
    (16777220, N'Notepad++ (32-bit x86)', N'20240115', N'8.6.2', N'Notepad++ Team');

-- Capacity0 in MB (matches what GLPI's native inventory expects for
-- MEMORIES/CAPACITY - no unit conversion is applied by the plugin, see
-- PluginSccmSccmxml::setMemories()). 8192 MB = 8 GB per stick.
INSERT INTO v_GS_PHYSICAL_MEMORY
    (ResourceID, Capacity0, Caption0, Description0, FormFactor0, Manufacturer0, Removable0, Speed0, BankLabel0, GroupID) VALUES
    (16777220, N'8192', N'Physical Memory', N'Physical Memory', N'12', N'Samsung', N'0', N'4800', N'Controller0-ChannelA', 1),
    (16777220, N'8192', N'Physical Memory', N'Physical Memory', N'12', N'Samsung', N'0', N'4800', N'Controller0-ChannelB', 2);

INSERT INTO v_GS_VIDEO_CONTROLLER
    (ResourceID, VideoProcessor0, AdapterRAM0, Name0, CurrentHorizontalResolution0, CurrentVerticalResolution0, GroupID) VALUES
    (16777220, N'Intel(R) Iris(R) Xe Graphics', 1073741824, N'Intel(R) Iris(R) Xe Graphics', N'1920', N'1080', 1);

INSERT INTO v_GS_SOUND_DEVICE (ResourceID, Description0, Manufacturer0, Name0) VALUES
    (16777220, N'Realtek(R) Audio', N'Realtek', N'Realtek(R) Audio');

INSERT INTO v_GS_LOGICAL_DISK
    (ResourceID, Description0, DeviceID0, FileSystem0, Size0, FreeSpace0, VolumeName0, GroupID) VALUES
    (16777220, N'Local Fixed Disk', N'C:', N'NTFS', 486000, 210000, N'Windows', 1);

INSERT INTO v_gs_Disk (ResourceID, GroupID, Caption0) VALUES
    (16777220, 1, N'KBG50ZNV512G KIOXIA');

INSERT INTO v_GS_CDROM (ResourceID, Description0, Manufacturer0, Caption0, Name0, SCSITargetID0, MediaType0) VALUES
    (16777220, N'CD-ROM Drive', N'(Standard CD-ROM drives)', N'HL-DT-ST DVDRAM', N'HL-DT-ST DVDRAM GUD1N', N'0', N'DVD Writer');

-- --- collections -------------------------------------------------------
INSERT INTO v_Collection (CollectionID, Name) VALUES
    (N'SMS00001', N'All Systems'),
    (N'GLP00010', N'Workstations'),
    (N'GLP00011', N'Site Bordeaux - O''Brien');

INSERT INTO v_FullCollectionMembership (ResourceID, CollectionID) VALUES
    (16777220, N'SMS00001'),
    (16777221, N'SMS00001'),
    (16777220, N'GLP00010'),
    (16777221, N'GLP00010'),
    (16777220, N'GLP00011');
GO

PRINT 'SCCM fixture database CM_TST ready.';
GO
