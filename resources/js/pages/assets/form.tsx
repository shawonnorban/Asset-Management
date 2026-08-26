import { FormEvent, useMemo } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import SpecFields from '@/components/spec-fields';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Category {
    id: number;
    category_name: string;
    asset_type: string;
}

interface Option {
    id: number;
    label: string;
}

type SpecValues = Record<string, string | number | boolean | null>;

interface AssetForm {
    asset_code: string;
    asset_name: string;
    brand: string;
    model: string;
    serial_number: string;
    description: string;
    added_date: string;
    vendor: string;
    invoice_no: string;
    purchase_date: string;
    purchase_cost: string;
    warranty_start: string;
    warranty_end: string;
    status: string;
    condition: string;
    category_id: string;
    location_id: string;
    employee_id: string;
    parent_asset_id: string;
    image: File | null;
    spec: SpecValues;
}

interface Props {
    asset: ({ id: number; image_url: string | null } & Partial<AssetForm>) | null;
    spec: SpecValues;
    categories: Category[];
    locations: Option[];
    employees: Option[];
    computers: Option[];
    statuses: Record<string, string>;
    conditions: Record<string, string>;
}

const NONE = '__none__';

export default function AssetFormPage({
    asset,
    spec,
    categories,
    locations,
    employees,
    computers,
    statuses,
    conditions,
}: Props) {
    const editing = Boolean(asset);

    const { data, setData, post, processing, errors } = useForm<AssetForm>({
        asset_code: asset?.asset_code ?? '',
        asset_name: asset?.asset_name ?? '',
        brand: asset?.brand ?? '',
        model: asset?.model ?? '',
        serial_number: asset?.serial_number ?? '',
        description: asset?.description ?? '',
        added_date: asset?.added_date ?? new Date().toISOString().slice(0, 10),
        vendor: asset?.vendor ?? '',
        invoice_no: asset?.invoice_no ?? '',
        purchase_date: asset?.purchase_date ?? '',
        purchase_cost: asset?.purchase_cost ?? '',
        warranty_start: asset?.warranty_start ?? '',
        warranty_end: asset?.warranty_end ?? '',
        status: asset?.status ?? 'IN_STORAGE',
        condition: asset?.condition ?? 'GOOD',
        category_id: asset?.category_id ?? '',
        location_id: asset?.location_id ?? '',
        employee_id: asset?.employee_id ?? '',
        parent_asset_id: asset?.parent_asset_id ?? '',
        image: null,
        spec: spec ?? {},
    });

    const assetType = useMemo(
        () => categories.find((c) => String(c.id) === String(data.category_id))?.asset_type ?? 'OTHER',
        [categories, data.category_id],
    );

    const setSpec = (name: string, value: string | number | boolean | null) =>
        setData('spec', { ...data.spec, [name]: value });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        // multipart needs a POST; Laravel reads _method for the update route
        post(editing ? `/inventory/${asset!.id}` : '/inventory', {
            forceFormData: true,
            ...(editing ? { headers: { 'X-HTTP-Method-Override': 'PUT' } } : {}),
        });
    };

    const field = (name: keyof AssetForm, label: string, required = false) => (
        <>
            <Label htmlFor={name} className="mb-2 block">
                {label} {required && <span className="text-destructive">*</span>}
            </Label>
            <Input
                id={name}
                value={(data[name] as string) ?? ''}
                onChange={(e) => setData(name, e.target.value as never)}
                aria-invalid={Boolean(errors[name])}
            />
            {errors[name] && <p className="mt-1 text-xs text-destructive">{errors[name]}</p>}
        </>
    );

    const dateField = (name: keyof AssetForm, label: string, required = false) => (
        <>
            <Label htmlFor={name} className="mb-2 block">
                {label} {required && <span className="text-destructive">*</span>}
            </Label>
            <Input
                id={name}
                type="date"
                value={(data[name] as string) ?? ''}
                onChange={(e) => setData(name, e.target.value as never)}
            />
            {errors[name] && <p className="mt-1 text-xs text-destructive">{errors[name]}</p>}
        </>
    );

    return (
        <AppLayout
            title={editing ? 'Edit Asset' : 'Add Asset'}
            description={editing ? asset!.asset_code : 'Register a new asset'}
            actions={
                <Button variant="outline" asChild>
                    <Link href={editing ? `/inventory/${asset!.id}` : '/inventory'}>
                        <ArrowLeft /> Back
                    </Link>
                </Button>
            }
        >
            <form onSubmit={submit} className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    {/* ---------- identity ---------- */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Identity</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-12 gap-4">
                            <div className="col-span-12 sm:col-span-4">{field('asset_code', 'Asset Code', true)}</div>
                            <div className="col-span-12 sm:col-span-8">{field('asset_name', 'Asset Name', true)}</div>
                            <div className="col-span-12 sm:col-span-4">{field('brand', 'Brand')}</div>
                            <div className="col-span-12 sm:col-span-4">{field('model', 'Model')}</div>
                            <div className="col-span-12 sm:col-span-4">{field('serial_number', 'Serial Number')}</div>

                            <div className="col-span-12 sm:col-span-4">
                                <Label className="mb-2 block">
                                    Category <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.category_id || NONE}
                                    onValueChange={(v) => setData('category_id', v === NONE ? '' : v)}
                                >
                                    <SelectTrigger aria-invalid={Boolean(errors.category_id)}>
                                        <SelectValue placeholder="Select category" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={NONE}>&mdash;</SelectItem>
                                        {categories.map((c) => (
                                            <SelectItem key={c.id} value={String(c.id)}>
                                                {c.category_name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.category_id && (
                                    <p className="mt-1 text-xs text-destructive">{errors.category_id}</p>
                                )}
                                <p className="mt-1 text-xs text-muted-foreground">
                                    Decides which specification fields appear below.
                                </p>
                            </div>

                            <div className="col-span-12 sm:col-span-4">
                                <Label className="mb-2 block">
                                    Location <span className="text-destructive">*</span>
                                </Label>
                                <Select
                                    value={data.location_id || NONE}
                                    onValueChange={(v) => setData('location_id', v === NONE ? '' : v)}
                                >
                                    <SelectTrigger aria-invalid={Boolean(errors.location_id)}>
                                        <SelectValue placeholder="Select location" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={NONE}>&mdash;</SelectItem>
                                        {locations.map((l) => (
                                            <SelectItem key={l.id} value={String(l.id)}>
                                                {l.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.location_id && (
                                    <p className="mt-1 text-xs text-destructive">{errors.location_id}</p>
                                )}
                            </div>

                            <div className="col-span-12 sm:col-span-4">
                                {dateField('added_date', 'Date Added', true)}
                            </div>

                            <div className="col-span-12">
                                <Label htmlFor="description" className="mb-2 block">
                                    Description
                                </Label>
                                <textarea
                                    id="description"
                                    rows={3}
                                    className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                />
                            </div>
                        </CardContent>
                    </Card>

                    {/* ---------- specification ---------- */}
                    <Card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Specification</CardTitle>
                            <Badge variant="secondary">{assetType.replace(/_/g, ' ')}</Badge>
                        </CardHeader>
                        <CardContent>
                            <SpecFields
                                assetType={assetType}
                                values={data.spec}
                                errors={errors as Record<string, string>}
                                onChange={setSpec}
                            />
                        </CardContent>
                    </Card>
                </div>

                {/* ---------- sidebar ---------- */}
                <div className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Status</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div>
                                <Label className="mb-2 block">Status</Label>
                                <Select value={data.status} onValueChange={(v) => setData('status', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(statuses).map(([k, v]) => (
                                            <SelectItem key={k} value={k}>
                                                {v}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label className="mb-2 block">Condition</Label>
                                <Select value={data.condition} onValueChange={(v) => setData('condition', v)}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(conditions).map(([k, v]) => (
                                            <SelectItem key={k} value={k}>
                                                {v}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div>
                                <Label className="mb-2 block">Assigned To</Label>
                                <Select
                                    value={data.employee_id || NONE}
                                    onValueChange={(v) => setData('employee_id', v === NONE ? '' : v)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Not assigned" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={NONE}>Not assigned</SelectItem>
                                        {employees.map((e) => (
                                            <SelectItem key={e.id} value={String(e.id)}>
                                                {e.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    For a dated handover use <b>Assign</b> on the asset page.
                                </p>
                            </div>

                            {assetType === 'PERIPHERAL' && (
                                <div>
                                    <Label className="mb-2 block">Attached To</Label>
                                    <Select
                                        value={data.parent_asset_id || NONE}
                                        onValueChange={(v) =>
                                            setData('parent_asset_id', v === NONE ? '' : v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Not attached" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value={NONE}>Not attached</SelectItem>
                                            {computers.map((c) => (
                                                <SelectItem key={c.id} value={String(c.id)}>
                                                    {c.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Purchase &amp; Warranty</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-2 gap-4">
                            <div className="col-span-2">{field('vendor', 'Vendor')}</div>
                            <div>{field('invoice_no', 'Invoice No')}</div>
                            <div>{field('purchase_cost', 'Cost')}</div>
                            <div className="col-span-2">{dateField('purchase_date', 'Purchase Date')}</div>
                            <div>{dateField('warranty_start', 'Warranty Start')}</div>
                            <div>{dateField('warranty_end', 'Warranty End')}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Image</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {asset?.image_url && !data.image && (
                                <img
                                    src={asset.image_url}
                                    alt=""
                                    className="mx-auto max-h-48 rounded-md object-contain"
                                />
                            )}
                            <Input
                                type="file"
                                accept="image/png,image/jpeg"
                                onChange={(e) => setData('image', e.target.files?.[0] ?? null)}
                            />
                            {errors.image && <p className="text-xs text-destructive">{errors.image}</p>}
                            <p className="text-xs text-muted-foreground">
                                Max 4MB.{editing ? ' Leave empty to keep the current image.' : ''}
                            </p>
                        </CardContent>
                    </Card>

                    <Button type="submit" className="w-full" disabled={processing}>
                        <Save /> {editing ? 'Update Asset' : 'Save Asset'}
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
