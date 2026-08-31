import { FormEvent, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import { Boxes, FileSpreadsheet, PackageOpen, Pencil, Plus, QrCode, Search, Trash2, UserCheck, Wrench, X } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import StatCard, { type Tone } from '@/components/stat-card';
import { useCan } from '@/lib/permissions';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

interface AssetRow {
    id: number;
    asset_code: string;
    asset_name: string;
    brand: string | null;
    model: string | null;
    serial_number: string | null;
    image_url: string | null;
    category: string | null;
    location: string | null;
    employee: string | null;
    status: string;
}

interface SummaryCard {
    label: string;
    value: number;
    description: string;
    /** null clears the filter; a comma joins statuses one card covers. */
    status: string | null;
    tone: Tone;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
}

interface Props {
    assets: Paginated<AssetRow>;
    assetTypes: Record<string, string>;
    statuses: Record<string, string>;
    summary: SummaryCard[];
    filters: { search: string | null; asset_type: string | null; status: string | null };
}

const ALL = '__all__';

const CARD_ICONS: Record<string, typeof Boxes> = {
    'Total assets': Boxes,
    'In use': UserCheck,
    'In storage': PackageOpen,
    'Under repair': Wrench,
    'Retired & disposed': Trash2,
};

const STATUS_VARIANTS: Record<string, string> = {
    IN_USE: 'bg-emerald-100 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950 dark:text-emerald-300',
    IN_STORAGE: 'bg-slate-100 text-slate-700 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300',
    UNDER_REPAIR: 'bg-amber-100 text-amber-800 hover:bg-amber-100 dark:bg-amber-950 dark:text-amber-300',
    RETIRED: 'bg-slate-200 text-slate-800 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200',
    DISPOSED: 'bg-rose-100 text-rose-800 hover:bg-rose-100 dark:bg-rose-950 dark:text-rose-300',
};

export default function AssetsIndex({ assets, assetTypes, statuses, summary, filters }: Props) {
    // mirrors the route guards, so a button never appears unless it would work
    const can = useCan();
    const canCreate = can('assets.create', 'assets.manage');
    const canEdit = can('assets.edit', 'assets.update', 'assets.manage');
    const canDelete = can('assets.delete', 'assets.manage');
    const canExport = can('assets.export', 'assets.manage');
    const [search, setSearch] = useState(filters.search ?? '');

    const apply = (patch: Record<string, string | null>) => {
        const next = {
            search,
            asset_type: filters.asset_type,
            status: filters.status,
            ...patch,
        };

        router.get('/inventory', clean(next), { preserveState: true, replace: true });
    };

    const clean = (params: Record<string, string | null>) =>
        Object.fromEntries(Object.entries(params).filter(([, v]) => v !== null && v !== ''));

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        apply({ search });
    };

    return (
        <AppLayout
            title="Assets"
            description={`${assets.total} assets on record`}
            actions={
                <>
                    {canExport && <Button variant="outline" asChild>
                        <a href="/inventory/export/excel">
                            <FileSpreadsheet /> Excel
                        </a>
                    </Button>}
                    {canExport && <Button variant="outline" asChild>
                        <a href="/inventory/export/pdf" target="_blank" rel="noopener">
                            <QrCode /> QR Codes
                        </a>
                    </Button>}
                    {canCreate && <Button asChild>
                        <Link href="/inventory/create">
                            <Plus /> Add Asset
                        </Link>
                    </Button>}
                </>
            }
        >
            {/* ---------- summary cards ---------- */}
            <div className="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                {summary.map((card) => (
                    <StatCard
                        key={card.label}
                        label={card.label}
                        value={card.value}
                        description={card.description}
                        tone={card.tone}
                        icon={CARD_ICONS[card.label] ?? Boxes}
                        onClick={() => apply({ status: card.status })}
                        active={(filters.status ?? null) === card.status}
                    />
                ))}
            </div>

            <Card>
                <CardContent className="pt-6">
                    {/* ---------- filters ---------- */}
                    <div className="mb-4 flex flex-wrap items-center gap-3">
                        <form onSubmit={submitSearch} className="relative flex-1 min-w-[240px]">
                            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search code, name, brand, model or serial..."
                                className="pl-9"
                            />
                        </form>

                        <Select
                            value={filters.asset_type ?? ALL}
                            onValueChange={(value) => apply({ asset_type: value === ALL ? null : value })}
                        >
                            <SelectTrigger className="w-[220px]">
                                <SelectValue placeholder="All types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={ALL}>All types</SelectItem>
                                {Object.entries(assetTypes).map(([key, label]) => (
                                    <SelectItem key={key} value={key}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {filters.status && (
                            <Button variant="ghost" size="sm" onClick={() => apply({ status: null })}>
                                <X className="size-4" />
                                {filters.status
                                    .split(',')
                                    .map((key) => statuses[key] ?? key)
                                    .join(' / ')}
                            </Button>
                        )}
                    </div>

                    {/* ---------- table ---------- */}
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[70px]">Image</TableHead>
                                    <TableHead>Asset Code</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Brand</TableHead>
                                    <TableHead>Model</TableHead>
                                    <TableHead>Serial</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead>Assigned To</TableHead>
                                    <TableHead>Location</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-[130px] text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {assets.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={11} className="h-24 text-center text-muted-foreground">
                                            No asset matches these filters.
                                        </TableCell>
                                    </TableRow>
                                )}

                                {assets.data.map((asset) => (
                                    <TableRow key={asset.id}>
                                        <TableCell>
                                            {asset.image_url ? (
                                                <img
                                                    src={asset.image_url}
                                                    alt={asset.asset_name}
                                                    className="size-11 rounded-md object-cover"
                                                />
                                            ) : (
                                                <div className="flex size-11 items-center justify-center rounded-md bg-muted text-xs text-muted-foreground">
                                                    &mdash;
                                                </div>
                                            )}
                                        </TableCell>
                                        <TableCell className="font-medium">{asset.asset_code}</TableCell>
                                        <TableCell>{asset.asset_name}</TableCell>
                                        <TableCell className="text-muted-foreground">{asset.brand ?? '-'}</TableCell>
                                        <TableCell className="text-muted-foreground">{asset.model ?? '-'}</TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {asset.serial_number ?? '-'}
                                        </TableCell>
                                        <TableCell>{asset.category ?? '-'}</TableCell>
                                        <TableCell>{asset.employee ?? '-'}</TableCell>
                                        <TableCell>{asset.location ?? '-'}</TableCell>
                                        <TableCell><Badge variant="secondary" className={STATUS_VARIANTS[asset.status] ?? ''}>{statuses[asset.status] ?? asset.status}</Badge></TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-1">
                                                <Button variant="ghost" size="icon" asChild title="Detail">
                                                    <Link href={`/inventory/${asset.id}`}>
                                                        <Search className="size-4" />
                                                    </Link>
                                                </Button>
                                                {canEdit && <Button variant="ghost" size="icon" asChild title="Edit">
                                                    <Link href={`/inventory/${asset.id}/edit`}>
                                                        <Pencil className="size-4" />
                                                    </Link>
                                                </Button>}

                                                {canDelete && <AlertDialog>
                                                    <AlertDialogTrigger asChild>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            title="Delete"
                                                            className="text-destructive hover:text-destructive"
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    </AlertDialogTrigger>
                                                    <AlertDialogContent>
                                                        <AlertDialogHeader>
                                                            <AlertDialogTitle>
                                                                Delete {asset.asset_code}?
                                                            </AlertDialogTitle>
                                                            <AlertDialogDescription>
                                                                This removes the asset, its specification and its
                                                                QR code. It cannot be undone.
                                                            </AlertDialogDescription>
                                                        </AlertDialogHeader>
                                                        <AlertDialogFooter>
                                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                            <AlertDialogAction
                                                                className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                                                                onClick={() =>
                                                                    router.delete(`/inventory/${asset.id}`)
                                                                }
                                                            >
                                                                Delete
                                                            </AlertDialogAction>
                                                        </AlertDialogFooter>
                                                    </AlertDialogContent>
                                                </AlertDialog>}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    {/* ---------- pagination ---------- */}
                    <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p className="text-sm text-muted-foreground">
                            Showing {assets.from ?? 0}&ndash;{assets.to ?? 0} of {assets.total}
                        </p>

                        <div className="flex flex-wrap gap-1">
                            {assets.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? 'default' : 'outline'}
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
