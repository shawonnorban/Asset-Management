<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\ComputerSpec;
use App\Models\PeripheralSpec;
use App\Models\PrinterSpec;
use Illuminate\Database\Seeder;

/**
 * Loads the IT inventory from the equipment register.
 *
 * Each row is [name and model, serial number]. The serial is the natural key,
 * so re-running the seeder updates the existing asset instead of creating a
 * second copy of it.
 *
 * Laptops and desktops are not in the register yet - add them to COMPUTERS
 * below when they are, and run the seeder again.
 */
class AssetSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string}> */
    private const MONITORS = [
        ['Monitor 24" HP', 'CN49200A1X'],
        ['Samsung 27"', 'SAM27-88401'],
        ['Samsung 18"', 'SAM18-50214'],
        ['View Sonic 16"', 'VS16-339210'],
        ['Samsung 14"', 'SAM14-11048'],
        ['Samsung 22"', 'SAM22-99041'],
        ['Asus 18"', 'AS18-771239'],
        ['HP 18.5"', 'HP185-60291'],
        ['Dell 22"', 'CN-0D22-7721'],
        ['Dell 18"', 'CN-0D18-1094'],
        ['HP 21"', 'HP21-884022'],
        ['Samsung 20"', 'SAM20-44910'],
        ['Accer 18"', 'AC18-204918'],
        ['HP 18"', 'HP18-301928'],
        ['Dell 18"', 'CN-0D18-8830'],
        ["DELL 18'", 'CN-0D18-6512'],
        ["Dell 18'", 'CN-0D18-4409'],
        ['Benq 20"', 'BQ20-551029'],
        ['Dell 18" Square', 'CN-0D18S-903'],
        ['Acer 18"', 'AC18-118234'],
    ];

    /** @var array<int, array{0: string, 1: string}> */
    private const PRINTERS = [
        ['Canon LBP613cdw', 'CN-LBP613-001'],
        ['Canon LBP7110Cw', 'CN-LBP711-002'],
        ['Epson L3210', 'EP-L3210-501'],
        ['HP 107', 'HP-107A-8821'],
        ['Epson L1300', 'EP-L1300-302'],
        ['Canon 299', 'CN-299-10921'],
        ['Canon G1000', 'CN-G1000-441'],
        ['Canon 6030', 'CN-6030-9921'],
        ['Canon Pixma G2020', 'CN-G2020-112'],
        ['Canon Pixma G3020', 'CN-G3020-883'],
        ['Canon LBP 6030', 'CN-LBP6030-44'],
        ['HP Laserjet M12a', 'HP-M12A-7712'],
        ['HP laserjet 1102', 'HP-1102-3391'],
        ['Canon G1010', 'CN-G1010-005'],
        ['HP 1102', 'HP-1102-8812'],
        ['HP Laserjet 1020', 'HP-1020-9910'],
        ['Canon LBP 3300', 'CN-LBP3300-11'],
        ['HP Laserjet pro-M402', 'HP-M402-5512'],
        ['Canon LBP 214 NetworkP', 'CN-LBP214-88'],
        ['HP Laserjet 1005', 'HP-1005-4421'],
        ['HP Laserjet 1008', 'HP-1008-1109'],
        ['HP Laserjet', 'HP-LJ-992018'],
        ['Canon LBP 6030-Black', 'CN-6030B-77'],
        ['HP Laser Jet Pro M12a', 'HP-M12A-8831'],
        ['Canon 1010', 'CN-1010-4491'],
    ];

    /** @var array<int, array{0: string, 1: string}> */
    private const SCANNERS = [
        ['Canon Scan 110', 'CNS-110-8821'],
        ['Canon Scan 400', 'CNS-400-3392'],
        ['Canon Scan 300', 'CNS-300-1102'],
        ['Canon Scan 120', 'CNS-120-7741'],
        ['HP Scan Pro 2500 FL', 'HPS-2500-991'],
        ['HP Laser 1008a', 'HPL-1008A-55'],
        ['Canon Lide 110', 'CNL-110-3382'],
        ['Canon LBP6000', 'CNL-6000-119'],
        ['Canon Scan Lide 300', 'CNS-L300-44'],
        ['Epson V39', 'EPV-39-88210'],
    ];

    /**
     * Machines from the computer register.
     *
     * [ category, brand, model, serial, cpu, motherboard, hard disk, ssd, ram ]
     *
     * Transcribed as the register has it - spellings such as "Accer",
     * "Pavallon" and "DuelCore" are left alone so the two can be reconciled.
     *
     * @var array<int, array{0: string, 1: ?string, 2: ?string, 3: string, 4: ?string, 5: ?string, 6: ?string, 7: ?string, 8: ?string}>
     */
    private const COMPUTERS = [
        ['Laptop', 'Macbook Pro', null, 'LPT-DEMO-001', 'Core i5', 'Apple', '120 GB HDD', null, '4 GB'],
        ['Laptop', 'Macbook Air', null, 'LPT-DEMO-002', 'Core i5', 'Apple', '120 GB HDD', null, '4 GB'],
        ['Laptop', 'Macbook Air', null, 'LPT-DEMO-003', null, null, null, null, null],
        ['Laptop', 'Macbook Air', null, 'LPT-DEMO-004', null, null, null, null, null],
        ['Laptop', 'ASUS', null, 'LPT-DEMO-005', 'Core i3', 'Asus', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'HP', null, 'LPT-DEMO-006', 'Core i5', 'HP 14s-CF3032TU', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'Lenovo', null, 'LPT-DEMO-007', 'Core i7', 'Lenovo', '1 TB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-001', 'Core i3', 'Gigabyte H81 M', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'Lenovo', 'IP 130', 'LPT-DEMO-008', 'Core i5', 'Lenovo-8250U', '1 TB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-002', 'Core i7', 'Gigabyte z490', '1 TB HDD', null, '64 GB'],
        ['Laptop', 'iMac', null, 'LPT-DEMO-009', 'Core i5', 'A1418-4K', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'iMac', null, 'LPT-DEMO-010', 'Core i5', 'A1418-4K', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'iMac', null, 'LPT-DEMO-011', 'Core i5', 'Dell-O40DDP', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'Macbook Pro', null, 'LPT-DEMO-012', 'Apple M4', 'Apple', '500 GB HDD', null, '16 GB'],
        ['Laptop', 'HP', 'Probook', 'LPT-DEMO-013', 'Core i5', '10210U', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'Dell', 'S458', 'LPT-DEMO-014', 'Core i5', 'DellODDNF', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'Dell', 'Vostro 15-3530', 'LPT-DEMO-015', 'Core i5', 'Intel', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'Dell', 'Vostro 14 3400', 'LPT-DEMO-016', 'Core i5', 'Dell 1135G7', '1 TB HDD', '256 GB SSD', '8 GB'],
        ['Laptop', 'HP', 'Probook 440', 'LPT-DEMO-017', 'Core i5 11 Gen', 'Intel', null, '1 TB SSD', '8 GB'],
        ['Laptop', 'Dell', '14', 'LPT-DEMO-018', 'Core i3', 'Dell 8265U', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'Acer', 'Travelmate', 'LPT-DEMO-019', 'Core i3', 'Intel', '1 TB HDD', null, '4 GB'],
        ['Laptop', 'HP', '250 G9', 'LPT-DEMO-020', 'Core i3', null, '500 GB HDD', null, '8 GB'],
        ['Laptop', 'Dell', '14-3467', 'LPT-DEMO-021', 'Core i3', null, '1 TB HDD', null, '8 GB'],
        ['Laptop', 'HP', '240 G8', 'LPT-DEMO-022', 'Core i5', null, '500 GB HDD', null, '8 GB'],
        ['Laptop', 'Dell', null, 'LPT-DEMO-023', 'Core i3', 'Dell', null, '512 GB SSD', '4 GB'],
        ['Laptop', 'Dell', 'Inspiron15 300', 'LPT-DEMO-024', 'Core i3', 'Dell-1005G1', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'HP', '15s', 'LPT-DEMO-025', 'Core i5 11 Gen', 'Intel', '1 TB HDD', '128 GB SSD', '8 GB'],
        ['Laptop', 'Dell', '3501', 'LPT-DEMO-026', 'Core i5', 'Intel', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'HP', 'Pavallon 14', 'LPT-DEMO-027', 'Core i5 11 Gen', 'Intel', null, '512 GB SSD', '8 GB'],
        ['Laptop', 'Dell', '3400', 'LPT-DEMO-028', 'Core i3', 'Latitude 14-3400', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'Victus HP', null, 'LPT-DEMO-029', 'Core i5', 'Intel H61M', '500 GB HDD', null, '16 GB'],
        ['Laptop', 'Victus HP', null, 'LPT-DEMO-030', 'Core i5', null, '500 GB HDD', null, '16 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-003', 'Core i7', 'Intel', '1 TB HDD', null, '16 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-004', 'Core i3', null, '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-005', 'Core 2 Duo', 'Intel', '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-006', 'Core i3', 'Intel', '500 GB HDD', null, '4 GB'],
        ['Laptop', null, null, 'LPT-DEMO-031', 'Core i5', 'Intel', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'Macbook Pro', null, 'LPT-DEMO-032', 'Core i5', 'Apple M1 8Core chip', '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-007', 'DualCore', 'Intel DG41RQ', '80 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-008', 'DuelCore-2.70Ghz', 'Intel DG41RQ', '300 GB HDD', null, '1 GB'],
        ['Laptop', 'HP', 'Pro Book-440', 'LPT-DEMO-033', 'Core i5', 'Intel', '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-009', 'Core i5', 'Dell-O40DDP', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'Dell', '3501', 'LPT-DEMO-034', 'Core i3', 'Intel', '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-010', 'Core i3-3.70Ghz', 'MSI-H110M', '1 TB HDD', null, '4 GB'],
        ['Laptop', 'Dell', '15-3567', 'LPT-DEMO-035', 'Core i3', 'Intel', '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-011', 'Core i3', 'Gigabyte-H61M', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'HP', '240 G7', 'LPT-DEMO-036', 'Core i3', 'Intel', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'HP', '15s-du1', 'LPT-DEMO-037', 'Core i3 10110U', 'HP 155dukx', '1 TB HDD', null, '8 GB'],
        ['Desktop', 'HP', null, 'DSK-DEMO-012', 'Core i3 11 Gen', 'Intel', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'HP', '15s', 'LPT-DEMO-038', 'Core i5', 'Intel', '1 TB HDD', '256 GB SSD', '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-013', 'Pentium2.90', 'Gigabyte H-61M', '500 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-014', 'Core i3', 'Intel', '1 TB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-015', 'Core i3', 'Intel', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'HP', '15s', 'LPT-DEMO-039', 'Core i3 11 Gen', 'Intel', '1 TB HDD', '256 GB SSD', '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-016', 'Pentium-2.90 Ghz', 'J & W', '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-017', 'Core i5', 'Gigabyte', '1 TB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-018', 'Core i3 10105', 'Dell', '1 TB HDD', null, '8 GB'],
        ['Desktop', 'Dell', null, 'DSK-DEMO-019', 'Core i3 6100', 'Dell', '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Fujitsu', null, 'DSK-DEMO-020', 'Pentium-2.40 GHz', 'Intel', '300 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-021', 'Pentium 2.90GHz', 'Gigabyte H61M', '500 GB HDD', null, '8 GB'],
        ['Laptop', 'Lenovo', 'Thinkpad', 'LPT-DEMO-040', 'Core i5', 'Lenovo', '500 GB HDD', null, '16 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-022', 'DuelCore-2.60GHz', 'Intel 41RQ', '300 GB HDD', null, '3 GB'],
        ['Laptop', 'Dell', '15-3000', 'LPT-DEMO-041', 'Core i3', 'Intel-1115G4', '1 TB HDD', null, '4 GB'],
        ['Laptop', 'HP', '250', 'LPT-DEMO-042', 'Core i5 11 Gen', 'Intel', '1 TB HDD', '128 GB SSD', '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-023', 'Core i3', 'Accer', '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-024', 'Pentium3.0 Ghz', 'MSI H110M', '1 TB HDD', null, '4 GB'],
        ['Desktop', 'Lenovo', null, 'DSK-DEMO-025', 'Core i5', 'Lenovo 841-80', '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-026', 'DuelCore-2.70GHz', 'JWIG41M', '500 GB HDD', null, '6 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-027', 'Duel Core-E5700', 'Gigabyte', '480 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-028', 'Intel Pentium-2.90', 'Gigabyte', '1 TB HDD', null, '4 GB'],
        ['Laptop', 'HP', '13 Gen', 'LPT-DEMO-043', 'Core i3 10 Gen', 'Gigabyte', '1 TB HDD', '256 GB SSD', '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-029', 'Core i3', 'Gigabyte', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'HP', null, 'LPT-DEMO-044', 'Pentium', '1000 notebook', '512 GB SSD', '128 GB SSD', '6 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-030', null, null, null, null, null],
        ['Laptop', 'MacbookPro15"', null, 'LPT-DEMO-045', 'M4', 'Apple', '500 GB HDD', null, '16 GB'],
        ['Laptop', 'HP', null, 'LPT-DEMO-046', 'Core i3 10110U', 'HP 155dukx', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'HP', '15s-du1', 'LPT-DEMO-047', 'Core i3 10110U', null, '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-031', 'Intel Xeon@3.0', 'Intel', '1 TB HDD', null, '16 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-032', 'Core i3 10Gen 3.6GHz', 'HP 280 Pro Intel', '1 TB HDD', '128 GB SSD', '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-033', 'Pentium- 2.90GHz', 'JW H61M', '500 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-034', 'Pentium- 2.90GHz', 'JW H61M', '500 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-035', null, null, null, null, null],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-036', 'Pentium-2.90', 'JW H61M', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'Lenovo', 'Ideapad310', 'LPT-DEMO-048', 'Core i5', 'Intel 7200u', '1 TB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-037', 'Core i5', 'Gigabyte H61M', '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-038', 'Core i3', null, '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-039', 'Core i3', 'Accer', '500 GB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-040', 'DuelCore-2.60GHz', 'Intel DG41RQ', '300 GB HDD', null, '4 GB'],
        ['Laptop', 'HP', null, 'LPT-DEMO-049', 'Core i3 10110U', 'HP 155dukx', '1 TB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-041', 'Core i3', 'Accer', '1 TB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-042', 'Pentium- 2.90GHz', 'Gigabyte H61M', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'Acer', 'Travelmate', 'LPT-DEMO-050', 'Core i3', 'Acer', '1 TB HDD', null, '4 GB'],
        ['Laptop', 'Dell', '5567', 'LPT-DEMO-051', 'Core i5', 'Dell', '500 GB HDD', null, '4 GB'],
        ['Laptop', 'HP', 'Probook 440', 'LPT-DEMO-052', 'Core i5 11 Gen', 'Intel', null, '1 TB SSD', '8 GB'],
        ['Laptop', 'HP', null, 'LPT-DEMO-053', 'Core i3', 'HP 831E', '1 TB HDD', null, '8 GB'],
        ['Laptop', 'ASUS', null, 'LPT-DEMO-054', 'Core i3', 'Asus1135g7', null, '512 GB SSD', '8 GB'],
        ['Laptop', 'Dell', '5567', 'LPT-DEMO-055', 'Core i3', 'Intel', '250 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-043', 'Core i3', 'DH55PJ', '500 GB HDD', null, '2 GB'],
        ['Laptop', 'Lenovo', 'Thinkpad', 'LPT-DEMO-056', 'Core i3', 'I0210U', '1 TB HDD', null, '8 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-044', 'DuelCore', 'Jetway T141M', '500 GB HDD', null, '2 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-045', 'Core i3-3.70 GHz', 'MSI-H110M', '1 TB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-046', 'Pentium 3.0 Ghz', 'MSI-H81M', '300 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-047', 'Pentium 3.0 Ghz', null, '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-048', 'Core i3-3.70 GHz', null, '500 GB HDD', null, '4 GB'],
        ['Desktop', 'Desktop', null, 'DSK-DEMO-049', 'Pentium2.90', 'Intel DH613BF', '500 GB HDD', null, '4 GB'],
    ];

    private array $counters = ['LPT' => 0, 'DSK' => 0, 'MON' => 0, 'PRN' => 0, 'SCN' => 0];

    private array $categories = [];

    private ?int $locationId = null;

    public function run(): void
    {
        $this->categories = AssetCategory::pluck('id', 'category_name')->all();
        $this->locationId = AssetLocation::where('location_name', 'NCL')->value('id');

        if (! $this->locationId) {
            $this->command?->error('Location "NCL" not found. Run AssetLocationSeeder first.');

            return;
        }

        foreach (self::COMPUTERS as $row) {
            $this->makeComputer(...$row);
        }

        foreach (self::MONITORS as [$name, $serial]) {
            $this->makeMonitor($name, $serial);
        }

        foreach (self::PRINTERS as [$name, $serial]) {
            $this->makePrinter($name, $serial);
        }

        foreach (self::SCANNERS as [$name, $serial]) {
            $this->makeScanner($name, $serial);
        }

        $this->command?->info(sprintf(
            'Seeded %d laptops, %d desktops, %d monitors, %d printers, %d scanners (%d assets).',
            $this->counters['LPT'],
            $this->counters['DSK'],
            $this->counters['MON'],
            $this->counters['PRN'],
            $this->counters['SCN'],
            array_sum($this->counters),
        ));
    }

    /**
     * =========================
     * BUILDERS
     * =========================
     */

    private function makeComputer(
        string $category,
        ?string $brand,
        ?string $model,
        string $serial,
        ?string $cpu,
        ?string $motherboard,
        ?string $hardDisk,
        ?string $ssd,
        ?string $ram,
    ): void {
        $isLaptop = $category === 'Laptop';

        // "Desktop" in the brand column is the register saying "unbranded clone",
        // not a manufacturer.
        $brand = ($brand === null || strcasecmp($brand, 'Desktop') === 0) ? null : $brand;

        $asset = $this->createAsset(
            $isLaptop ? 'LPT' : 'DSK',
            $this->computerName($brand, $model, $motherboard, $isLaptop),
            $serial,
            $category,
            [
                'brand' => $this->canonicalBrand($brand),
                'model' => $model ?: $this->modelFromBrandColumn($brand),
            ],
        );

        $primary = $this->parseStorage($hardDisk);
        $secondary = $this->parseStorage($ssd);

        // some rows list the only drive in the SSD column - that drive is the
        // machine's primary storage, not a second one
        if ($primary['gb'] === null && $secondary['gb'] !== null) {
            [$primary, $secondary] = [$secondary, ['type' => null, 'gb' => null]];
        }

        ComputerSpec::updateOrCreate(
            ['asset_id' => $asset->id],
            [
                'form_factor' => $this->formFactor($brand, $isLaptop),
                'cpu' => $cpu,
                'motherboard' => $motherboard,
                'ram_gb' => $this->parseRam($ram),
                'storage_type' => $primary['type'],
                'storage_gb' => $primary['gb'],
                'secondary_storage_type' => $secondary['type'],
                'secondary_storage_gb' => $secondary['gb'],
            ],
        );
    }

    private function makeMonitor(string $name, string $serial): void
    {
        $asset = $this->createAsset('MON', $name, $serial, 'Monitor');

        PeripheralSpec::updateOrCreate(
            ['asset_id' => $asset->id],
            [
                'peripheral_type' => 'MONITOR',
                'screen_size_inch' => $this->parseInches($name),
            ],
        );
    }

    private function makePrinter(string $name, string $serial): void
    {
        $asset = $this->createAsset('PRN', $name, $serial, 'Printer');

        PrinterSpec::updateOrCreate(
            ['asset_id' => $asset->id],
            ['printer_type' => $this->printerType($name)],
        );
    }

    /**
     * The Scanner category is a PERIPHERAL, so its detail lives in
     * peripheral_specs - that is the only table the asset pages read for it.
     */
    private function makeScanner(string $name, string $serial): void
    {
        $asset = $this->createAsset('SCN', $name, $serial, 'Scanner');

        PeripheralSpec::updateOrCreate(
            ['asset_id' => $asset->id],
            [
                'peripheral_type' => 'SCANNER',
                'scanner_type' => 'FLATBED',
            ],
        );
    }

    /**
     * =========================
     * HELPERS
     * =========================
     */

    private function createAsset(
        string $prefix,
        string $name,
        string $serial,
        string $categoryName,
        ?array $identity = null,
    ): Asset {
        $this->counters[$prefix]++;

        // peripherals carry brand and model inside their name; computers arrive
        // with both already separated by the register's own columns
        $identity ??= $this->splitBrandModel($name);

        $asset = Asset::firstOrNew(['serial_number' => $serial]);

        $asset->fill([
            'asset_code' => $prefix . '-' . str_pad((string) $this->counters[$prefix], 4, '0', STR_PAD_LEFT),
            'asset_name' => $name,
            'brand' => $identity['brand'],
            'model' => $identity['model'],
            'category_id' => $this->categories[$categoryName] ?? $this->categories['Furniture'],
            'location_id' => $this->locationId,
        ]);

        // Only seed the opening state. Re-running the seeder refreshes what the
        // register owns, but must not resurrect a disposed asset or wipe a
        // condition somebody recorded after an inspection.
        if (! $asset->exists) {
            $asset->status = 'IN_STORAGE';
            $asset->condition = 'GOOD';
            $asset->added_date = now()->toDateString();
        }

        $asset->save();

        return $asset;
    }

    /**
     * Brand names as they should be recorded, matched against how the register
     * spells them. The flag says whether the matched word is only a brand
     * ("Canon LBP613cdw" -> Canon / LBP613cdw) or part of the model name too.
     */
    private const BRAND_RULES = [
        ['View Sonic', 'ViewSonic', true],
        ['Dell', 'Dell', true],
        ['HP', 'HP', true],
        ['Accer', 'Acer', true],
        ['Acer', 'Acer', true],
        ['Asus', 'Asus', true],
        ['Canon', 'Canon', true],
        ['Epson', 'Epson', true],
        ['Samsung', 'Samsung', true],
        ['Benq', 'BenQ', true],
        ['Lenovo', 'Lenovo', true],
        ['Fujitsu', 'Fujitsu', true],
        ['Monitor', null, true],
    ];

    /** @return array{brand: ?string, model: ?string} */
    private function splitBrandModel(?string $label): array
    {
        if (! $label) {
            return ['brand' => null, 'model' => null];
        }

        foreach (self::BRAND_RULES as [$needle, $brand]) {
            if (stripos($label, $needle) === false) {
                continue;
            }

            // drop the brand word and any separator left behind
            $model = preg_replace('/' . preg_quote($needle, '/') . '/i', '', $label, 1);
            $model = trim((string) $model, " \t-_/,");

            if ($brand === null) {
                // "Monitor 24\" HP" - strip the noise word and try again
                return $this->splitBrandModel($model !== '' ? $model : null);
            }

            return ['brand' => $brand, 'model' => $model !== '' ? $model : null];
        }

        return ['brand' => null, 'model' => trim($label)];
    }

    /** A readable name even when the register left the brand column blank. */
    private function computerName(?string $brand, ?string $model, ?string $motherboard, bool $isLaptop): string
    {
        $name = trim(($brand ?? '') . ' ' . ($model ?? ''));

        if ($name !== '') {
            return $name;
        }

        return $motherboard
            ? trim($motherboard . ($isLaptop ? ' Laptop' : ' Desktop'))
            : ($isLaptop ? 'Unbranded Laptop' : 'Assembled Desktop');
    }

    /**
     * With no Model column filled in, the brand column is only worth keeping as
     * a model when it holds a product name ("Macbook Pro", "Victus HP") rather
     * than just the manufacturer.
     */
    private function modelFromBrandColumn(?string $brand): ?string
    {
        if (! $brand) {
            return null;
        }

        return strcasecmp((string) $this->canonicalBrand($brand), $brand) === 0 ? null : $brand;
    }

    /** Apple and HP hide behind product names in the register's brand column. */
    private function canonicalBrand(?string $brand): ?string
    {
        if (! $brand) {
            return null;
        }

        foreach ([['macbook', 'Apple'], ['imac', 'Apple'], ['victus', 'HP']] as [$needle, $canonical]) {
            if (stripos($brand, $needle) !== false) {
                return $canonical;
            }
        }

        return $this->splitBrandModel($brand)['brand'] ?? $brand;
    }

    private function formFactor(?string $brand, bool $isLaptop): string
    {
        if ($brand && stripos($brand, 'imac') !== false) {
            return 'ALL_IN_ONE';
        }

        return $isLaptop ? 'LAPTOP' : 'DESKTOP';
    }

    /** '4 GB' and '6GB' both give back 4 / 6. */
    private function parseRam(?string $ram): ?int
    {
        return $ram && preg_match('/(\d+)/', $ram, $m) ? (int) $m[1] : null;
    }

    /**
     * '1 TB HDD' -> 1024 GB HDD, '256 GB SSD' -> 256 GB SSD.
     *
     * @return array{type: ?string, gb: ?int}
     */
    private function parseStorage(?string $value): array
    {
        if (! $value || ! preg_match('/(\d+(?:\.\d+)?)\s*(TB|GB)/i', $value, $m)) {
            return ['type' => null, 'gb' => null];
        }

        $size = (float) $m[1];
        $gb = strtoupper($m[2]) === 'TB' ? (int) round($size * 1024) : (int) round($size);

        return [
            'type' => stripos($value, 'ssd') !== false ? 'SSD' : 'HDD',
            'gb' => $gb,
        ];
    }

    private function printerType(string $label): string
    {
        $inkjet = ['pixma', 'G1000', 'G1010', 'G2020', 'G3020', 'L3210', 'L1300'];

        foreach ($inkjet as $needle) {
            if (stripos($label, $needle) !== false) {
                return 'INKJET';
            }
        }

        return 'LASER';
    }

    /** 'Samsung 22"' and 'HP 18.5"' both give back the number. */
    private function parseInches(string $label): ?float
    {
        return preg_match('/(\d+(?:\.\d+)?)\s*["\']/', $label, $m) ? (float) $m[1] : null;
    }
}
