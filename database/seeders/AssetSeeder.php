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
 * Loads the IT inventory sheet. Each sheet row describes one desk, so it is
 * split into separate assets: the computer, its monitor, its printer and its
 * scanner. Monitors and scanners are attached to the computer of their row;
 * printers are left standalone because they are often shared.
 *
 * Anything the sheet recorded in the wrong column is kept in the asset
 * description rather than silently dropped - see $this->notes at the end.
 */
class AssetSeeder extends Seeder
{
    /** [ laptop, processor, motherboard, hard_disk, ram, monitor, printer, scanner ] */
    private const ROWS = [
        ['Macbook Pro', null, null, null, null, null, 'Canon LBP613cdw', null],
        ['Macbook Air', 'Core i5', 'Apple', '120GB', '4 GB', null, null, null],
        ['Macbook Air', null, null, null, null, null, 'Canon LBP7110Cw', null],
        ['Macbook Air', null, null, null, null, null, 'Epson L3210', null],
        ['ASUS', 'Core i3', 'Asus', '500 GB', '4 GB', null, 'HP 107', null],
        ['HP', 'Core i5', 'HP 14s-CF3032TU', '1 TB', '8GB', '128 SSD Card', 'HP 107+Epson L1300', null],
        ['Lenovo', 'Core i7', 'Lenovo', '1 TB', '8GB', null, 'Canon G1000+Canon6030', 'Canon 300'],
        [null, 'Core i3', 'Gigabyte H81 M', '500 GB', '4 GB', null, null, null],
        ['LenovoIP 130', 'Core i5', 'Lenovo-8250U', '1 TB', '8GB DDR4', null, null, null],
        ['Huawei - Idea Hub', null, null, null, null, null, null, null],
        [null, 'Core i7', 'Gigabyte z490', '1 TB', '64 GB', 'Samsung 27"', 'Canon Pixma G2020', null],
        ['iMac', 'Core i5', 'A1418-4K', '1 TB', '8GB DDR3', null, null, null],
        ['iMac', 'Core i5', 'A1418-4K', '1 TB', '8GB DDR3', null, null, null],
        [null, 'Core i3', 'Dell-040DDP', '500 GB', '4 GB', 'Samsung 18"', null, null],
        ['Macbook Pro', 'Apple M4', 'Apple', '500 GB', '16 GB', null, 'Canon Pixma G3020', 'Ipad Mini'],
        ['HP-Probook', 'Core i5', '10210U', '1TB', '8GB', null, 'Canon G1000', 'Monitor 24" HP'],
        ['Dell-5458', 'Core i5', 'Dell00DNF', '500 GB', '8 GB', null, null, null],
        ['Dell-Vostro 15-3530', 'Core i5', 'Intel', '500 GB', '8 GB', null, 'Canon LBP 6030', null],
        ['Dell Vostro 14 3400', 'Core i5', 'Dell 1135G7', '256 +1TB', '8GB DDR4', null, null, null],
        ['HP Probook 440', 'Core i5 11 Gen', 'Intel', '1 TB SSD', '8GB DDR4', null, 'HP LaserJet M12a', '18.06.2022'],
        ['Dell 14', 'Core i5', 'Dell 8265U', '1 TB', '8 GB', null, null, null],
        ['Acer Travelmate', 'Core i3', 'Intel', '1 TB', '4 GB', null, null, null],
        ['HP-250 G9', 'Core i3', null, '500 GB', '8 GB', null, null, null],
        ['Dell-14-3467', 'Core i5', null, '1 TB', '8 GB', null, null, null],
        ['HP-240 G6', 'Core i5', null, '500 GB', '8 GB', null, null, null],
        ['Dell', 'Core i3', null, '512GB-ssd', '4 GB', null, null, null],
        ['Dell Inspiron15 300', 'Core i3', 'Dell-1005G1', '500 GB', '8 GB', null, null, null],
        ['HP 15s', 'Core i5 11 Gen', 'Intel', '128+1 TB', '8 GBDDR4', null, null, null],
        ['Dell-3501', 'Core i3', null, '1 TB', '8 GB', null, null, null],
        ['HP Pavalion 14', 'Core i5 11 Gen', 'Intel', '512GB SSD', '8 GBDDR4', null, 'HP laserjet 1102', '29.09.2022'],
        ['Dell-3400', 'Core-i3', 'Latitude 14-3400', 'ITB', '8 GB', null, null, null],
        ['Victus HP', 'Core i5', 'Intel H61M', '500GB', '16 GB', 'View Sonic', null, null],
        ['Victus HP', 'Core i5', null, '500 GB', '16 GB', null, null, null],
        [null, 'Core i7', 'Intel', '1 TB', '16 GB', 'Samsung 14"', null, null],
        [null, 'Core i3', null, '500 GB', '4 GB', 'Samsung 14"', 'Canon G1010+HP 1102', null],
        [null, 'Core 2 Duo', 'Intel', '500 GB', '4 GB', "Samsung 22'", null, null],
        [null, 'Core i5', 'Intel', '500 GB', '4 GB', "Samsung 22'", null, 'Canon Scan 110'],
        [null, 'Core i5', 'Intel', '500 GB', '4 GB', "Samsung 22'", 'Canon G1010', null],
        ['Macbook Pro', 'Core i5', 'Apple M1 8Core chip', '500 GB', '8GB', null, null, null],
        [null, 'DuelCore', 'Intel-DG41RQ', '80 GB', '2 GB', 'Samsung 18"', 'Canon G1000', null],
        [null, 'DuelCore-2.70Ghz', 'Intel DG41RQ', '300GB', '1 GB', 'Samsung 18"', 'Canon LBP 6030', null],
        ['HP Pro Book-440', 'Core i5', 'Intel', '500 GB', '8 GB', null, null, null],
        [null, 'Core i3', 'Dell-040DDP', '500 GB', '4 GB', 'Samsung 18"', null, null],
        ['Dell 3501', 'Core i5', 'Intel', '500 GB', '8 GB', null, null, null],
        [null, 'Core i3-3.70Ghz', 'MSI-H110M', '1 TB', '4 GB', 'Asus 18"', null, null],
        ['Dell-15-3567', 'Core i3', 'Intel', '500 GB', '4 GB', null, null, null],
        [null, 'Core i3', 'Gigabyte-H61M', '500 GB', '8 GB', 'Samsung 18"', null, null],
        ['HP-240 G7', 'Core i3', 'Intel', '500 GB', '4 GB', null, null, null],
        ['HP-15s-dui', 'Core i3 10110U', 'HP 155dulxx', '500GB', '8 GB', null, null, null],
        ['HP', 'Corei3', 'Intel', '500 GB', '8 GB', 'Samsung 18"', null, null],
        ['HP 15s', 'Core i5 11 Gen', 'Intel', '1TB256ssd', '8 GBDDR4', null, 'Epson L3210', '27.07.2022'],
        [null, 'Pentium2.90', 'Gigabyte H-61M', '500 GB', '2 GB', 'Samsung 18"', null, null],
        [null, 'Core i3', null, '1 TB', '4 GB', 'Samsung 18"', 'HP Laserjet 1020', null],
        ['HP 15s', 'Core i5 11 Gen', 'Intel', '1TB256ssd', '8 GBDDR4', null, 'Epson L3210', 'Canon Scan 400'],
        [null, 'Pentium-2.90 Ghz', 'J & W', '500 GB', '4 GB', 'HP 18.5"', null, 'Canon Scan 300'],
        [null, 'Core i5', 'Gigabyte', '1 TB', '4 GB', 'Dell 22"', 'Canon LBP 3300', 'Canon Scan 300'],
        [null, 'Core i3 10105', 'Dell', '1 TB', '8 GB', 'Dell 18"', null, null],
        ['Dell', 'Core i3 6100', 'Dell', '500 GB', '8 GB', null, null, null],
        ['Fujitsu', 'Pentium-2.40 Ghz', 'Intel', '300 GB', '2 GB', null, null, 'Canon Scan 110'],
        [null, 'Pentium 2.90GHZ', 'Gigabyte H61M', '500 GB', '4 GB', 'HP 21"', null, 'Canon Scan 120'],
        ['Lenovo-Thinkpad', 'Corei5', 'Lenovo', '500 GB', '16 GB', null, null, 'Canon Scan 300'],
        [null, 'DuelCore-2.60Ghz', 'Intel 41RQ', '300 GB', '3 GB', 'Samsung 20"', null, 'Canon Scan 300'],
        ['Dell 15-3000', 'Core i3', 'Intel-1115G4', 'I TB', '4 GB', null, null, null],
        ['HP- 250', 'Core i5 11 Gen', 'Intel', '128+1 TB', '8 GBDDR4', null, 'HP LaserJet pro-M402', 'Canon Scan 400'],
        [null, 'Core i3', 'Accer', '500 GB', '8 GB', 'Accer 18"', 'Brand PC', null],
        [null, 'Pentium3.0 GHz', 'MSI H110M', '1 TB', '4 GB', 'Samsung 18"', null, null],
        ['Lenovo', 'Corei5', 'Lenovo B41-80', '1 TB', '8 GB', null, null, null],
        [null, 'DuelCore-2.70GHZ', 'JWIG41M', '500 GB', '6 GB', "DELL 18'", null, null],
        [null, 'Duel Core-E5700', 'Gigabyte', '480 GB', '4 GB', "Dell 18'", null, 'Canon Scan 400'],
        [null, 'Intel Pentium-2.90', 'Gigabyte', '1 TB', '4 GB', 'Samsung 18"', null, null],
        [null, 'Core i3 10 Gen', 'Gigabyte', '256+1 TB', '8 GB', "Dell 18'", null, 'HP Scan Pro 2500 FL'],
        [null, 'Corei3', 'Gigabyte', '500 GB', '4 GB', null, null, null],
        ['HP', 'Pentium', '1000 notebook', '128+512GB', '6GB', null, null, 'HP Laser 1008a'],
        ['MacbooPro15"', 'M4', 'Apple', '500 GB', '16 GB', null, 'Epson L3210', null],
        ['HP', 'Core i3 10110U', 'HP 155dulxx', '1 TB', '8 GB', null, 'HP Laserjet 1102', null],
        ['HP-15s-dui', 'Core i3 10110U', null, '500 GB', '8 GB', null, 'Canon LBP 214 NetworkP', null],
        [null, 'Intel Xeon@3.0', 'Intel', '1 TB', '16 GB', 'Samsung 18"', null, null],
        [null, 'Core i3 10Gn 3.6ghz', 'HP 280 Pro Intel', '128+1 GB', '8 GB', 'HP 18"', 'HP Laserjet 1102', null],
        [null, 'Pentium- 2.90Ghz', 'JW H61M', '500 GB', '2 GB', 'Samsung 18"', null, null],
        [null, 'Pentium- 2.90Ghz', 'JW H61M', '500 GB', '2 GB', 'Samsung 18"', 'HP Laserjet 1005', null],
        [null, null, null, null, null, 'HP 18"', null, null],
        [null, 'Pentium-2.90', 'JW H61M', '500 GB', '4 GB', 'Samsung 18"', null, 'Canon Lide 110'],
        ['Lenovo Ideapad310', 'Core i5', 'Intel 7200u', '1 TB', '8 GB', null, 'HP Laserjet 1008', null],
        [null, 'Core i3', 'Gigabyte H61M', '500 GB', '4 GB', 'HP 18"', null, null],
        [null, 'Core i3', null, '500 GB', '8 GB', 'Dell', null, null],
        [null, 'Core i3', 'Accer', '500 GB', '8 GB', 'Accer 18"', 'Brand PC', null],
        [null, 'DuelCore-2.60Ghz', 'Intel DG41RQ', '300 GB', '4 GB', 'Samsung 18"', 'HP Laserjet', 'Canon Lide 110'],
        ['HP', 'Core i3 10110U', 'HP 155dulxx', '500 GB', '8 GB', null, null, null],
        [null, 'Core i3', 'Accer', 'I TB', '4 GB', 'Accer 18"', 'Brand PC', null],
        [null, 'Pentium- 2.90Ghz', 'Gigabyte H61M', '500 GB', '4 GB', 'Samsung 18"', null, null],
        ['Acer Travelmate', 'Core i3', 'Acer', '1 TB', '4 GB', null, null, null],
        ['Dell -5567', 'Core i5', 'Dell', '500 GB', '4 GB', null, null, 'Canon LBP6000'],
        ['HP Probook 440', 'Core i5 11 Gen', 'Intel', '1 TB SSD', '8GB DDR4', null, 'Canon LBP 6030-Black', null],
        ['HP', 'Core i3', 'HP 831E', '500 GB', '8 GB', null, null, 'Canon Scan Lide 300'],
        ['ASUS', 'Core i5', 'Asus1135g7', '512GBssd', '8 GBDDR4', null, null, null],
        ['Dell-5567', 'Core i7', 'Intel', '250 GB', '8 GB', null, null, null],
        [null, 'Core i3', 'DH55PJ', '500 GB', '2 GB', 'DELL 18"', 'HP Laser Jet Pro M12a', 'Canon Scan Lide 300'],
        ['Lenovo-Thinkpad', 'Core i5', 'I02I0U', '1 TB', '8GB', null, 'Canon 1010', 'Epson V39'],
        [null, 'DuelCore', 'Jetway T141M', '500 GB', '2 GB', 'Benq 20"', 'HP Laserjet 1102', 'Canon Lide 110'],
        [null, 'Core i3-3.70 Ghz', 'MSI-H110M', '1 TB', '4 GB', 'Dell 18" Square', null, 'Canon Scan Lide 300'],
        [null, 'Pentium 3.0 Ghz', 'MSI-H81M', '300 GB', '4 GB', 'Samsung 18"', null, 'Canon Scan Lide 300'],
        [null, 'Pentium 3.0 Ghz', null, '500 GB', '4 GB', 'Acer', null, null],
        [null, 'Core i3-3.70 Ghz', null, '500 GB', '4 GB', 'Acer', null, null],
        [null, 'Pentium2.90', 'Intel DH61BF', '500 GB', '4 GB', 'Samsung 18"', null, null],
    ];

    /** Sheet values that are not really what their column says. */
    private const NOT_A_MONITOR = ['128 SSD Card'];
    private const NOT_A_PRINTER = ['Brand PC'];

    private array $counters = ['PC' => 0, 'MON' => 0, 'PRN' => 0, 'SCN' => 0, 'TAB' => 0];
    private array $notes = [];
    private array $categories = [];
    private ?int $locationId = null;

    public function run(): void
    {
        $this->categories = AssetCategory::pluck('id', 'category_name')->all();
        $this->locationId = AssetLocation::where('location_name', 'NCL')->value('id');

        if (! $this->locationId) {
            $this->command->error('Location "NCL" not found. Run AssetLocationSeeder first.');
            return;
        }

        foreach (self::ROWS as $index => $row) {
            [$laptop, $cpu, $mobo, $hdd, $ram, $monitor, $printer, $scanner] = $row;
            $sheetRow = $index + 1;

            $computer = $this->makeComputer($sheetRow, $laptop, $cpu, $mobo, $hdd, $ram, $monitor, $scanner);

            $this->makeMonitor($sheetRow, $monitor, $computer);
            $this->makePrinters($sheetRow, $printer);
            $this->makeScanner($sheetRow, $scanner, $computer);
        }

        $this->report();
    }

    /**
     * =========================
     * BUILDERS
     * =========================
     */

    private function makeComputer(
        int $sheetRow, ?string $laptop, ?string $cpu, ?string $mobo,
        ?string $hdd, ?string $ram, ?string $monitor, ?string $scanner
    ): ?Asset {
        // nothing but a monitor on this row
        if (! $laptop && ! $cpu && ! $mobo && ! $hdd && ! $ram) {
            return null;
        }

        [$categoryName, $formFactor] = $this->computerKind($laptop);

        $description = ['Sheet row ' . $sheetRow];

        if ($monitor && in_array($monitor, self::NOT_A_MONITOR, true)) {
            $description[] = 'Monitor column held "' . $monitor . '"';
            $this->notes[] = "row $sheetRow: \"$monitor\" sat in the Monitor column - kept in the description";
        }

        if ($scanner && $this->looksLikeDate($scanner)) {
            $description[] = 'Scanner column held the date ' . $scanner;
            $this->notes[] = "row $sheetRow: date \"$scanner\" sat in the Scanner column - kept in the description";
        }

        $identity = $this->splitBrandModel($laptop ?: $mobo);

        $asset = $this->createAsset(
            prefix: 'PC',
            name: $laptop ?: trim(($mobo ?: 'Assembled') . ' Desktop'),
            categoryName: $categoryName,
            brand: $identity['brand'],
            model: $identity['model'],
            description: implode('. ', $description),
        );

        $storage = $this->parseStorage($hdd);
        $memory = $this->parseRam($ram);

        ComputerSpec::create([
            'asset_id'               => $asset->id,
            'form_factor'            => $formFactor,
            'cpu'                    => $cpu,
            'motherboard'            => $mobo,
            'ram_gb'                 => $memory['gb'],
            'ram_type'               => $memory['type'],
            'storage_type'           => $storage['type'],
            'storage_gb'             => $storage['gb'],
            'secondary_storage_type' => $storage['secondary_type'],
            'secondary_storage_gb'   => $storage['secondary_gb'],
        ]);

        return $asset;
    }

    private function makeMonitor(int $sheetRow, ?string $monitor, ?Asset $computer): void
    {
        if (! $monitor || in_array($monitor, self::NOT_A_MONITOR, true)) {
            return;
        }

        $this->createMonitorAsset($sheetRow, $monitor, $computer);
    }

    private function createMonitorAsset(int $sheetRow, string $label, ?Asset $computer): void
    {
        $size = $this->parseInches($label);
        $identity = $this->splitBrandModel($label);

        $asset = $this->createAsset(
            prefix: 'MON',
            name: $label,
            categoryName: 'Monitor',
            brand: $identity['brand'],
            model: $identity['model'],
            description: 'Sheet row ' . $sheetRow,
            parentId: $computer?->id,
        );

        PeripheralSpec::create([
            'asset_id'         => $asset->id,
            'peripheral_type'  => 'MONITOR',
            'screen_size_inch' => $size,
        ]);
    }

    private function makePrinters(int $sheetRow, ?string $printer): void
    {
        if (! $printer) {
            return;
        }

        if (in_array($printer, self::NOT_A_PRINTER, true)) {
            $this->notes[] = "row $sheetRow: \"$printer\" sat in the Printer column - skipped, it is not a printer";
            return;
        }

        // "HP 107+Epson L1300" is two machines on one desk
        foreach (preg_split('/\s*\+\s*/', $printer) as $one) {
            $one = trim($one);

            if ($one === '') {
                continue;
            }

            $identity = $this->splitBrandModel($one);

            $asset = $this->createAsset(
                prefix: 'PRN',
                name: $one,
                categoryName: 'Printer',
                brand: $identity['brand'],
                model: $identity['model'],
                description: 'Sheet row ' . $sheetRow,
            );

            PrinterSpec::create([
                'asset_id'     => $asset->id,
                'printer_type' => $this->printerType($one),
            ]);
        }
    }

    private function makeScanner(int $sheetRow, ?string $scanner, ?Asset $computer): void
    {
        if (! $scanner || $this->looksLikeDate($scanner)) {
            return;
        }

        // the sheet used this column for a monitor and a tablet as well
        if (stripos($scanner, 'monitor') !== false) {
            $this->notes[] = "row $sheetRow: \"$scanner\" sat in the Scanner column - recorded as a monitor";
            $this->createMonitorAsset($sheetRow, $scanner, $computer);
            return;
        }

        if (stripos($scanner, 'ipad') !== false) {
            $this->notes[] = "row $sheetRow: \"$scanner\" sat in the Scanner column - recorded as a tablet";
            $this->createAsset(
                prefix: 'TAB',
                name: $scanner,
                categoryName: 'Tablet',
                brand: 'Apple',
                model: trim($scanner),
                description: 'Sheet row ' . $sheetRow,
                parentId: $computer?->id,
            );
            return;
        }

        $identity = $this->splitBrandModel($scanner);

        $asset = $this->createAsset(
            prefix: 'SCN',
            name: $scanner,
            categoryName: 'Scanner',
            brand: $identity['brand'],
            model: $identity['model'],
            description: 'Sheet row ' . $sheetRow,
            parentId: $computer?->id,
        );

        PrinterSpec::create([
            'asset_id'         => $asset->id,
            'printer_type'     => 'LASER',
            'is_multifunction' => true,
        ]);
    }

    /**
     * =========================
     * HELPERS
     * =========================
     */

    private function createAsset(
        string $prefix,
        string $name,
        string $categoryName,
        ?string $brand,
        ?string $model,
        string $description,
        ?int $parentId = null
    ): Asset {
        $this->counters[$prefix]++;

        return Asset::create([
            'asset_code'      => $prefix . '-' . str_pad((string) $this->counters[$prefix], 4, '0', STR_PAD_LEFT),
            'asset_name'      => $name,
            'brand'           => $brand,
            'model'           => $model,
            'description'     => $description,
            'added_date'      => now()->toDateString(),
            'status'          => 'IN_USE',
            'condition'       => 'GOOD',
            'category_id'     => $this->categories[$categoryName] ?? $this->categories['Furniture'],
            'location_id'     => $this->locationId,
            'parent_asset_id' => $parentId,
        ]);
    }

    /** Which category and form factor a machine belongs to. */
    private function computerKind(?string $laptop): array
    {
        if (! $laptop) {
            return ['Desktop', 'DESKTOP'];
        }

        if (stripos($laptop, 'imac') !== false) {
            return ['Desktop', 'ALL_IN_ONE'];
        }

        if (stripos($laptop, 'idea hub') !== false) {
            return ['Projector', 'DESKTOP'];
        }

        return ['Laptop', 'LAPTOP'];
    }

    /**
     * The sheet writes brand and model as one string ("Canon Scan Lide 300",
     * "Samsung 18\"", "Dell-5458"). Split them so the two fields carry
     * different information and a monitor can actually be told apart.
     *
     * [needle, canonical brand, strip] - strip=false keeps the needle in the
     * model because it is part of the product name (MacBook Pro, ThinkPad).
     */
    private const BRAND_RULES = [
        ['View Sonic', 'ViewSonic', true],
        ['Macboo',     'Apple',     false],   // sheet also writes "MacbooPro15\""
        ['iMac',       'Apple',     false],
        ['Ipad',       'Apple',     false],
        ['Apple',      'Apple',     true],
        ['Thinkpad',   'Lenovo',    false],
        ['Ideapad',    'Lenovo',    false],
        ['Lenovo',     'Lenovo',    true],
        ['Vostro',     'Dell',      false],
        ['Inspiron',   'Dell',      false],
        ['Latitude',   'Dell',      false],
        ['Dell',       'Dell',      true],
        ['Probook',    'HP',        false],
        ['Pro Book',   'HP',        false],
        ['Pavalion',   'HP',        false],
        ['Victus',     'HP',        false],
        ['HP',         'HP',        true],
        ['Accer',      'Acer',      true],
        ['Acer',       'Acer',      true],
        ['Asus',       'Asus',      true],
        ['Canon',      'Canon',     true],
        ['Epson',      'Epson',     true],
        ['Samsung',    'Samsung',   true],
        ['Benq',       'BenQ',      true],
        ['Gigabyte',   'Gigabyte',  true],
        ['Jetway',     'Jetway',    true],
        ['Fujitsu',    'Fujitsu',   true],
        ['Huawei',     'Huawei',    true],
        ['MSI',        'MSI',       true],
        ['Intel',      'Intel',     true],
    ];

    /** @return array{brand: ?string, model: ?string} */
    private function splitBrandModel(?string $label): array
    {
        if (! $label) {
            return ['brand' => null, 'model' => null];
        }

        foreach (self::BRAND_RULES as [$needle, $brand, $strip]) {
            if (stripos($label, $needle) === false) {
                continue;
            }

            if (! $strip) {
                // the product word stays ("Macbook Pro"), but a brand sitting in
                // front of it does not ("Dell Vostro 14" -> "Vostro 14")
                $model = preg_replace(
                    '/^\s*' . preg_quote($brand, '/') . '[\s\-]*/i',
                    '',
                    $label
                );

                return ['brand' => $brand, 'model' => trim((string) $model, " \t-_/,") ?: trim($label)];
            }

            // drop the brand word and any separator left behind
            $model = preg_replace('/' . preg_quote($needle, '/') . '/i', '', $label, 1);
            $model = trim((string) $model, " \t-_/,");

            return ['brand' => $brand, 'model' => $model !== '' ? $model : null];
        }

        return ['brand' => null, 'model' => trim($label)];
    }

    private function printerType(string $label): string
    {
        if (stripos($label, 'pixma') !== false || stripos($label, 'G10') !== false
            || stripos($label, 'G20') !== false || stripos($label, 'G30') !== false
            || stripos($label, 'L3210') !== false || stripos($label, 'L1300') !== false) {
            return 'INKJET';
        }

        return 'LASER';
    }

    /** "Samsung 22'" and 'HP 18.5"' both give back the number. */
    private function parseInches(string $label): ?float
    {
        return preg_match('/(\d+(?:\.\d+)?)\s*["\']/', $label, $m) ? (float) $m[1] : null;
    }

    /** "8 GBDDR4" -> 8 GB DDR4 */
    private function parseRam(?string $ram): array
    {
        if (! $ram) {
            return ['gb' => null, 'type' => null];
        }

        $gb = preg_match('/(\d+)/', $ram, $m) ? (int) $m[1] : null;
        $type = preg_match('/(DDR\d)/i', $ram, $m) ? strtoupper($m[1]) : null;

        return ['gb' => $gb, 'type' => $type];
    }

    /**
     * Turns the free-text Hard Disk cell into primary and secondary drives.
     * Handles "500 GB", "1 TB", "512GB-ssd", "128+1 TB", "1TB256ssd", "ITB".
     */
    private function parseStorage(?string $hdd): array
    {
        $empty = ['type' => null, 'gb' => null, 'secondary_type' => null, 'secondary_gb' => null];

        if (! $hdd) {
            return $empty;
        }

        // the sheet writes a capital I for 1 in "ITB" / "I TB"
        $text = preg_replace('/\bI\s*TB\b/i', '1 TB', $hdd);
        $isSsd = preg_match('/ssd|nvme/i', $text) === 1;

        // every "<number><unit>" pair, in order
        if (! preg_match_all('/(\d+(?:\.\d+)?)\s*(TB|GB)?/i', $text, $m, PREG_SET_ORDER)) {
            return $empty;
        }

        $sizes = [];

        foreach ($m as $match) {
            $value = (float) $match[1];

            if ($value <= 0) {
                continue;
            }

            $unit = strtoupper($match[2] ?? '');

            // "128+1 TB" - the 128 has no unit of its own and means GB
            if ($unit === 'TB' || ($unit === '' && $value <= 4)) {
                $sizes[] = (int) ($value * 1024);
            } else {
                $sizes[] = (int) $value;
            }
        }

        if (! $sizes) {
            return $empty;
        }

        // Two real drives means a small boot SSD plus a larger data HDD,
        // whichever order the sheet wrote them in ("128+1 TB", "1TB256ssd").
        if (count($sizes) >= 2 && min($sizes[0], $sizes[1]) >= 64) {
            $ssd = min($sizes[0], $sizes[1]);
            $hdd = max($sizes[0], $sizes[1]);

            return [
                'type'           => 'SSD',
                'gb'             => $ssd,
                'secondary_type' => 'HDD',
                'secondary_gb'   => $hdd,
            ];
        }

        return [
            'type'           => $isSsd ? 'SSD' : 'HDD',
            'gb'             => $sizes[0],
            'secondary_type' => isset($sizes[1]) ? 'HDD' : null,
            'secondary_gb'   => $sizes[1] ?? null,
        ];
    }

    private function looksLikeDate(string $value): bool
    {
        return (bool) preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value);
    }

    private function report(): void
    {
        $this->command->info(sprintf(
            'Seeded %d computers, %d monitors, %d printers, %d scanners, %d tablets from %d sheet rows.',
            $this->counters['PC'],
            $this->counters['MON'],
            $this->counters['PRN'],
            $this->counters['SCN'],
            $this->counters['TAB'],
            count(self::ROWS)
        ));

        foreach ($this->notes as $note) {
            $this->command->warn('  ' . $note);
        }
    }
}
