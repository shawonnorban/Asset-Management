import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type FieldKind = 'text' | 'number' | 'select' | 'checkbox';

interface FieldDef {
    name: string;
    label: string;
    kind: FieldKind;
    options?: string[];
    placeholder?: string;
    span?: 2 | 3 | 4 | 6;
}

interface Group {
    heading: string;
    fields: FieldDef[];
}

const opt = (values: string[]) => values;

/** Mirrors AssetSpecService::rules() on the server. */
export const SPEC_GROUPS: Record<string, Group[]> = {
    COMPUTER: [
        {
            heading: 'Hardware',
            fields: [
                { name: 'form_factor', label: 'Form Factor', kind: 'select', options: opt(['DESKTOP', 'LAPTOP', 'ALL_IN_ONE', 'WORKSTATION', 'SERVER']), span: 3 },
                { name: 'cpu', label: 'Processor (CPU)', kind: 'text', placeholder: 'Intel Core i5-11400 @ 2.60GHz', span: 6 },
                { name: 'cpu_cores', label: 'CPU Cores', kind: 'number', placeholder: '6', span: 3 },
                { name: 'ram_gb', label: 'RAM (GB)', kind: 'number', placeholder: '16', span: 3 },
                { name: 'ram_type', label: 'RAM Type', kind: 'text', placeholder: 'DDR4', span: 3 },
                { name: 'storage_type', label: 'Primary Storage', kind: 'select', options: opt(['HDD', 'SSD', 'NVME', 'HYBRID']), span: 3 },
                { name: 'storage_gb', label: 'Size (GB)', kind: 'number', placeholder: '512', span: 3 },
                { name: 'secondary_storage_type', label: 'Secondary Storage', kind: 'select', options: opt(['HDD', 'SSD', 'NVME']), span: 3 },
                { name: 'secondary_storage_gb', label: 'Size (GB)', kind: 'number', placeholder: '1000', span: 3 },
                { name: 'gpu', label: 'Graphics (GPU)', kind: 'text', span: 3 },
                { name: 'motherboard', label: 'Motherboard', kind: 'text', span: 3 },
                { name: 'psu', label: 'Power Supply', kind: 'text', placeholder: '500W', span: 3 },
            ],
        },
        {
            heading: 'Network identity',
            fields: [
                { name: 'hostname', label: 'Hostname', kind: 'text', placeholder: 'IT-PC-014', span: 3 },
                { name: 'domain', label: 'Domain / Workgroup', kind: 'text', placeholder: 'corp.local', span: 3 },
                { name: 'ip_type', label: 'IP Type', kind: 'select', options: opt(['STATIC', 'DHCP']), span: 2 },
                { name: 'ip_address', label: 'IP Address', kind: 'text', placeholder: '192.168.1.24', span: 2 },
                { name: 'mac_address', label: 'MAC Address', kind: 'text', placeholder: 'A4:BB:6D:11:22:33', span: 2 },
            ],
        },
        {
            heading: 'Software',
            fields: [
                { name: 'os', label: 'Operating System', kind: 'text', placeholder: 'Windows 11 Pro', span: 3 },
                { name: 'os_version', label: 'OS Version', kind: 'text', placeholder: '23H2', span: 2 },
                { name: 'os_license_key', label: 'OS License Key', kind: 'text', span: 3 },
                { name: 'office_license_key', label: 'Office Key', kind: 'text', span: 2 },
                { name: 'antivirus', label: 'Antivirus', kind: 'text', span: 2 },
            ],
        },
    ],

    PERIPHERAL: [
        {
            heading: 'Device',
            fields: [
                { name: 'peripheral_type', label: 'Peripheral Type', kind: 'select', options: opt(['MONITOR', 'KEYBOARD', 'MOUSE', 'UPS', 'DOCKING_STATION', 'HEADSET', 'WEBCAM', 'SCANNER', 'PROJECTOR', 'OTHER']), span: 4 },
                { name: 'connection', label: 'Connection', kind: 'select', options: opt(['USB', 'HDMI', 'DISPLAYPORT', 'VGA', 'DVI', 'BLUETOOTH', 'WIRELESS', 'PS2', 'OTHER']), span: 4 },
            ],
        },
        {
            heading: 'Display (monitors and projectors)',
            fields: [
                { name: 'screen_size_inch', label: 'Screen Size (inch)', kind: 'number', placeholder: '23.8', span: 3 },
                { name: 'resolution', label: 'Resolution', kind: 'text', placeholder: '1920x1080', span: 3 },
                { name: 'panel_type', label: 'Panel Type', kind: 'text', placeholder: 'IPS', span: 3 },
                { name: 'refresh_rate_hz', label: 'Refresh Rate (Hz)', kind: 'number', placeholder: '75', span: 3 },
            ],
        },
        {
            heading: 'Power backup (UPS)',
            fields: [
                { name: 'capacity_va', label: 'Capacity (VA)', kind: 'number', placeholder: '650', span: 3 },
                { name: 'backup_minutes', label: 'Backup (minutes)', kind: 'number', placeholder: '20', span: 3 },
            ],
        },
    ],

    PRINTER: [
        {
            heading: 'Device',
            fields: [
                { name: 'printer_type', label: 'Printer Type', kind: 'select', options: opt(['LASER', 'INKJET', 'DOT_MATRIX', 'THERMAL', 'PLOTTER']), span: 3 },
                { name: 'max_paper_size', label: 'Max Paper Size', kind: 'text', placeholder: 'A4', span: 3 },
                { name: 'is_color', label: 'Color', kind: 'checkbox', span: 2 },
                { name: 'is_multifunction', label: 'Scan / Copy', kind: 'checkbox', span: 2 },
                { name: 'supports_duplex', label: 'Duplex', kind: 'checkbox', span: 2 },
            ],
        },
        {
            heading: 'Consumables',
            fields: [
                { name: 'toner_model', label: 'Toner / Cartridge Model', kind: 'text', placeholder: 'CF217A', span: 4 },
                { name: 'drum_model', label: 'Drum Model', kind: 'text', span: 4 },
                { name: 'monthly_duty_cycle', label: 'Monthly Duty Cycle', kind: 'number', placeholder: '8000', span: 4 },
            ],
        },
        {
            heading: 'Network identity',
            fields: [
                { name: 'connection', label: 'Connection', kind: 'select', options: opt(['USB', 'ETHERNET', 'WIFI', 'SHARED']), span: 2 },
                { name: 'hostname', label: 'Hostname', kind: 'text', span: 2 },
                { name: 'ip_type', label: 'IP Type', kind: 'select', options: opt(['STATIC', 'DHCP']), span: 2 },
                { name: 'ip_address', label: 'IP Address', kind: 'text', span: 2 },
                { name: 'mac_address', label: 'MAC Address', kind: 'text', span: 2 },
                { name: 'management_url', label: 'Admin URL', kind: 'text', placeholder: 'http://192.168.1.50', span: 2 },
            ],
        },
    ],

    NETWORK_DEVICE: [
        {
            heading: 'Device',
            fields: [
                { name: 'device_role', label: 'Device Role', kind: 'select', options: opt(['ROUTER', 'SWITCH', 'ACCESS_POINT', 'FIREWALL', 'MODEM', 'NAS', 'SERVER', 'OTHER']), span: 3 },
                { name: 'hostname', label: 'Hostname', kind: 'text', placeholder: 'SW-CORE-01', span: 3 },
                { name: 'rack_position', label: 'Rack Position', kind: 'text', placeholder: 'Rack A / U12', span: 3 },
                { name: 'firmware_version', label: 'Firmware Version', kind: 'text', span: 3 },
            ],
        },
        {
            heading: 'Addressing',
            fields: [
                { name: 'ip_type', label: 'IP Type', kind: 'select', options: opt(['STATIC', 'DHCP']), span: 2 },
                { name: 'ip_address', label: 'IP Address', kind: 'text', span: 2 },
                { name: 'subnet_mask', label: 'Subnet Mask', kind: 'text', placeholder: '255.255.255.0', span: 2 },
                { name: 'gateway', label: 'Gateway', kind: 'text', span: 2 },
                { name: 'mac_address', label: 'MAC Address', kind: 'text', span: 2 },
                { name: 'vlan', label: 'VLAN', kind: 'text', placeholder: 'VLAN 10', span: 2 },
            ],
        },
        {
            heading: 'Capability',
            fields: [
                { name: 'port_count', label: 'Port Count', kind: 'number', placeholder: '24', span: 2 },
                { name: 'port_speed', label: 'Port Speed', kind: 'text', placeholder: '1Gbps', span: 2 },
                { name: 'wifi_standard', label: 'WiFi Standard', kind: 'text', placeholder: 'WiFi 6', span: 2 },
                { name: 'management_url', label: 'Admin URL', kind: 'text', span: 3 },
                { name: 'is_managed', label: 'Managed', kind: 'checkbox', span: 2 },
                { name: 'supports_poe', label: 'PoE', kind: 'checkbox', span: 2 },
            ],
        },
    ],
};

const humanise = (value: string) =>
    value.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (c) => c.toUpperCase());

const SPAN: Record<number, string> = {
    2: 'col-span-12 sm:col-span-6 lg:col-span-2',
    3: 'col-span-12 sm:col-span-6 lg:col-span-3',
    4: 'col-span-12 sm:col-span-6 lg:col-span-4',
    6: 'col-span-12 lg:col-span-6',
};

const NONE = '__none__';

export type SpecValues = Record<string, string | number | boolean | null>;

interface Props {
    assetType: string;
    values: SpecValues;
    errors: Record<string, string>;
    onChange: (name: string, value: string | number | boolean | null) => void;
}

export default function SpecFields({ assetType, values, errors, onChange }: Props) {
    const groups = SPEC_GROUPS[assetType];

    if (!groups) {
        return (
            <p className="text-sm text-muted-foreground">
                This category holds no detailed specification. Pick a category of another type to
                record hardware, network or software details.
            </p>
        );
    }

    return (
        <div className="space-y-6">
            {groups.map((group) => (
                <div key={group.heading}>
                    <h4 className="mb-3 text-sm font-medium text-muted-foreground">{group.heading}</h4>

                    <div className="grid grid-cols-12 gap-4">
                        {group.fields.map((field) => {
                            const error = errors[`spec.${field.name}`];
                            const raw = values[field.name];

                            return (
                                <div key={field.name} className={SPAN[field.span ?? 3]}>
                                    {field.kind === 'checkbox' ? (
                                        <label className="flex h-10 items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                className="size-4 rounded border-input accent-primary"
                                                checked={Boolean(raw)}
                                                onChange={(e) => onChange(field.name, e.target.checked)}
                                            />
                                            {field.label}
                                        </label>
                                    ) : (
                                        <>
                                            <Label htmlFor={`spec-${field.name}`} className="mb-2 block">
                                                {field.label}
                                            </Label>

                                            {field.kind === 'select' ? (
                                                <Select
                                                    value={(raw as string) || NONE}
                                                    onValueChange={(v) =>
                                                        onChange(field.name, v === NONE ? '' : v)
                                                    }
                                                >
                                                    <SelectTrigger id={`spec-${field.name}`}>
                                                        <SelectValue placeholder="Select" />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value={NONE}>&mdash;</SelectItem>
                                                        {field.options?.map((o) => (
                                                            <SelectItem key={o} value={o}>
                                                                {humanise(o)}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            ) : (
                                                <Input
                                                    id={`spec-${field.name}`}
                                                    type={field.kind === 'number' ? 'number' : 'text'}
                                                    step={field.kind === 'number' ? 'any' : undefined}
                                                    value={(raw as string) ?? ''}
                                                    placeholder={field.placeholder}
                                                    onChange={(e) => onChange(field.name, e.target.value)}
                                                />
                                            )}
                                        </>
                                    )}

                                    {error && <p className="mt-1 text-xs text-destructive">{error}</p>}
                                </div>
                            );
                        })}
                    </div>
                </div>
            ))}

            <div>
                <Label htmlFor="spec-note" className="mb-2 block">
                    Technical Note
                </Label>
                <textarea
                    id="spec-note"
                    rows={2}
                    className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    value={(values.note as string) ?? ''}
                    onChange={(e) => onChange('note', e.target.value)}
                />
            </div>
        </div>
    );
}
