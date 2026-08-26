<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\ComputerSpec;
use App\Models\NetworkDeviceSpec;
use App\Models\PeripheralSpec;
use App\Models\PrinterSpec;

/**
 * Every asset category declares an asset_type, and that type decides which
 * specification table the asset gets a row in. This service keeps the
 * validation rules and the write path for those tables in one place.
 */
class AssetSpecService
{
    /** asset_type => [relation method, model class] */
    private const TYPE_MAP = [
        'COMPUTER'       => ['computerSpec',      ComputerSpec::class],
        'PERIPHERAL'     => ['peripheralSpec',    PeripheralSpec::class],
        'PRINTER'        => ['printerSpec',       PrinterSpec::class],
        'NETWORK_DEVICE' => ['networkDeviceSpec', NetworkDeviceSpec::class],
    ];

    /**
     * Validation rules for the specification fields of one asset type.
     * All fields are optional - IT rarely has every detail on day one.
     */
    public function rules(string $assetType): array
    {
        return match ($assetType) {
            'COMPUTER' => [
                'form_factor'            => 'nullable|in:DESKTOP,LAPTOP,ALL_IN_ONE,WORKSTATION,SERVER',
                'cpu'                    => 'nullable|string|max:120',
                'cpu_cores'              => 'nullable|integer|min:1|max:512',
                'gpu'                    => 'nullable|string|max:120',
                'motherboard'            => 'nullable|string|max:120',
                'psu'                    => 'nullable|string|max:60',
                'ram_gb'                 => 'nullable|integer|min:1|max:4096',
                'ram_type'               => 'nullable|string|max:20',
                'storage_type'           => 'nullable|in:HDD,SSD,NVME,HYBRID',
                'storage_gb'             => 'nullable|integer|min:1',
                'secondary_storage_type' => 'nullable|in:HDD,SSD,NVME',
                'secondary_storage_gb'   => 'nullable|integer|min:1',
                'hostname'               => 'nullable|string|max:60',
                'domain'                 => 'nullable|string|max:60',
                'mac_address'            => 'nullable|string|max:17',
                'ip_address'             => 'nullable|ip',
                'ip_type'                => 'nullable|in:STATIC,DHCP',
                'os'                     => 'nullable|string|max:60',
                'os_version'             => 'nullable|string|max:40',
                'os_license_key'         => 'nullable|string|max:60',
                'office_license_key'     => 'nullable|string|max:60',
                'antivirus'              => 'nullable|string|max:60',
                'note'                   => 'nullable|string',
            ],
            'PERIPHERAL' => [
                'peripheral_type'  => 'nullable|in:MONITOR,KEYBOARD,MOUSE,UPS,DOCKING_STATION,HEADSET,WEBCAM,SCANNER,PROJECTOR,OTHER',
                'connection'       => 'nullable|in:USB,HDMI,DISPLAYPORT,VGA,DVI,BLUETOOTH,WIRELESS,PS2,OTHER',
                'screen_size_inch' => 'nullable|numeric|min:1|max:200',
                'resolution'       => 'nullable|string|max:20',
                'panel_type'       => 'nullable|string|max:20',
                'refresh_rate_hz'  => 'nullable|integer|min:1|max:1000',
                'capacity_va'      => 'nullable|integer|min:1',
                'backup_minutes'   => 'nullable|integer|min:1',
                'note'             => 'nullable|string',
            ],
            'PRINTER' => [
                'printer_type'       => 'nullable|in:LASER,INKJET,DOT_MATRIX,THERMAL,PLOTTER',
                'is_color'           => 'nullable|boolean',
                'is_multifunction'   => 'nullable|boolean',
                'supports_duplex'    => 'nullable|boolean',
                'max_paper_size'     => 'nullable|string|max:20',
                'toner_model'        => 'nullable|string|max:60',
                'drum_model'         => 'nullable|string|max:60',
                'monthly_duty_cycle' => 'nullable|integer|min:1',
                'connection'         => 'nullable|in:USB,ETHERNET,WIFI,SHARED',
                'hostname'           => 'nullable|string|max:60',
                'ip_address'         => 'nullable|ip',
                'mac_address'        => 'nullable|string|max:17',
                'ip_type'            => 'nullable|in:STATIC,DHCP',
                'management_url'     => 'nullable|url|max:191',
                'note'               => 'nullable|string',
            ],
            'NETWORK_DEVICE' => [
                'device_role'      => 'nullable|in:ROUTER,SWITCH,ACCESS_POINT,FIREWALL,MODEM,NAS,SERVER,OTHER',
                'hostname'         => 'nullable|string|max:60',
                'ip_address'       => 'nullable|ip',
                'mac_address'      => 'nullable|string|max:17',
                'ip_type'          => 'nullable|in:STATIC,DHCP',
                'subnet_mask'      => 'nullable|string|max:45',
                'gateway'          => 'nullable|ip',
                'vlan'             => 'nullable|string|max:40',
                'port_count'       => 'nullable|integer|min:1|max:1000',
                'port_speed'       => 'nullable|string|max:20',
                'is_managed'       => 'nullable|boolean',
                'supports_poe'     => 'nullable|boolean',
                'wifi_standard'    => 'nullable|string|max:20',
                'firmware_version' => 'nullable|string|max:40',
                'management_url'   => 'nullable|url|max:191',
                'rack_position'    => 'nullable|string|max:40',
                'note'             => 'nullable|string',
            ],
            default => [],
        };
    }

    /**
     * Write the specification row that matches the asset's type, and drop any
     * row left over from a previous type (a category change, for instance).
     *
     * @param  array<string,mixed>  $data  already-validated spec fields
     */
    public function sync(Asset $asset, string $assetType, array $data): void
    {
        foreach (self::TYPE_MAP as $type => [$relation, $modelClass]) {
            if ($type === $assetType) {
                continue;
            }

            $asset->{$relation}()->delete();
        }

        if (! isset(self::TYPE_MAP[$assetType])) {
            return;
        }

        [$relation, $modelClass] = self::TYPE_MAP[$assetType];

        // Checkboxes are absent from the request when unticked.
        foreach ($this->booleanFields($assetType) as $field) {
            $data[$field] = ! empty($data[$field]);
        }

        $asset->{$relation}()->updateOrCreate(
            ['asset_id' => $asset->id],
            $data
        );
    }

    /** Fields that arrive as checkboxes and must be forced to a boolean. */
    private function booleanFields(string $assetType): array
    {
        return match ($assetType) {
            'PRINTER'        => ['is_color', 'is_multifunction', 'supports_duplex'],
            'NETWORK_DEVICE' => ['is_managed', 'supports_poe'],
            default          => [],
        };
    }

    /** Spec values currently stored for an asset, for repopulating the form. */
    public function current(Asset $asset): array
    {
        $spec = $asset->spec();

        return $spec ? $spec->toArray() : [];
    }

    /**
     * The same specification grouped and labelled for a read-only view.
     *
     * @return array<int, array{heading: string, entries: array<int, array{label: string, value: string}>}>
     */
    public function display(Asset $asset): array
    {
        $spec = $asset->spec();

        if (! $spec) {
            return [];
        }

        $groups = $this->displayMap($asset->asset_type);
        $out = [];

        foreach ($groups as $heading => $fields) {
            $entries = [];

            foreach ($fields as $field => $label) {
                $value = $spec->{$field} ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                if (is_bool($value)) {
                    $value = $value ? 'Yes' : 'No';
                }

                $entries[] = ['label' => $label, 'value' => (string) $value];
            }

            if ($entries) {
                $out[] = ['heading' => $heading, 'entries' => $entries];
            }
        }

        return $out;
    }

    /** field => label, grouped by heading, per asset type. */
    private function displayMap(string $assetType): array
    {
        return match ($assetType) {
            'COMPUTER' => [
                'Hardware' => [
                    'form_factor' => 'Form Factor', 'cpu' => 'Processor', 'cpu_cores' => 'CPU Cores',
                    'ram_gb' => 'RAM (GB)', 'ram_type' => 'RAM Type', 'storage_summary' => 'Storage',
                    'gpu' => 'Graphics', 'motherboard' => 'Motherboard', 'psu' => 'Power Supply',
                ],
                'Network' => [
                    'hostname' => 'Hostname', 'domain' => 'Domain', 'ip_address' => 'IP Address',
                    'ip_type' => 'IP Type', 'mac_address' => 'MAC Address',
                ],
                'Software' => [
                    'os' => 'Operating System', 'os_version' => 'OS Version',
                    'os_license_key' => 'OS Key', 'office_license_key' => 'Office Key',
                    'antivirus' => 'Antivirus',
                ],
                'Note' => ['note' => 'Technical Note'],
            ],
            'PERIPHERAL' => [
                'Device' => ['peripheral_type' => 'Type', 'connection' => 'Connection'],
                'Display' => [
                    'screen_size_inch' => 'Screen Size', 'resolution' => 'Resolution',
                    'panel_type' => 'Panel Type', 'refresh_rate_hz' => 'Refresh Rate (Hz)',
                ],
                'Power backup' => ['capacity_va' => 'Capacity (VA)', 'backup_minutes' => 'Backup (minutes)'],
                'Note' => ['note' => 'Technical Note'],
            ],
            'PRINTER' => [
                'Device' => [
                    'printer_type' => 'Printer Type', 'max_paper_size' => 'Max Paper Size',
                    'is_color' => 'Color', 'is_multifunction' => 'Scan / Copy',
                    'supports_duplex' => 'Duplex', 'monthly_duty_cycle' => 'Duty Cycle',
                ],
                'Consumables' => ['toner_model' => 'Toner', 'drum_model' => 'Drum'],
                'Network' => [
                    'connection' => 'Connection', 'hostname' => 'Hostname',
                    'ip_address' => 'IP Address', 'ip_type' => 'IP Type',
                    'mac_address' => 'MAC Address', 'management_url' => 'Admin URL',
                ],
                'Note' => ['note' => 'Technical Note'],
            ],
            'NETWORK_DEVICE' => [
                'Device' => [
                    'device_role' => 'Role', 'hostname' => 'Hostname',
                    'rack_position' => 'Rack Position', 'firmware_version' => 'Firmware',
                    'is_managed' => 'Managed', 'supports_poe' => 'PoE',
                ],
                'Addressing' => [
                    'ip_address' => 'IP Address', 'ip_type' => 'IP Type',
                    'subnet_mask' => 'Subnet Mask', 'gateway' => 'Gateway',
                    'mac_address' => 'MAC Address', 'vlan' => 'VLAN',
                ],
                'Capability' => [
                    'port_count' => 'Port Count', 'port_speed' => 'Port Speed',
                    'wifi_standard' => 'WiFi Standard', 'management_url' => 'Admin URL',
                ],
                'Note' => ['note' => 'Technical Note'],
            ],
            default => [],
        };
    }
}
