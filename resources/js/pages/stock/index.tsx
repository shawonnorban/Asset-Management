import { Link } from '@inertiajs/react';
import { Boxes, Building2, Eye, PackageOpen } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import StatCard from '@/components/stat-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import Pagination from '@/components/pagination';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface StockAsset {
    id: number;
    asset_code: string;
    asset_name: string;
    category: string | null;
    location: string | null;
    condition: string;
    added_date: string | null;
    image_url: string | null;
}

interface Props {
    title: string;
    description: string;
    assets: StockAsset[];
    /** Counted across all of stock, not just the page on screen. */
    summary: { available: number; categories: number; locations: number };
    pagination?: {
        links: { url: string | null; label: string; active: boolean }[];
        from?: number;
        to?: number;
        total: number;
    };
}

const humanise = (value: string) =>
    value.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (letter) => letter.toUpperCase());

export default function StockIndex({ title, description, assets, summary, pagination }: Props) {
    return (
        <AppLayout title={title} description={description}>
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-3">
                    <StatCard label="Available in stock" value={summary.available} description="Unassigned and ready to allocate" icon={PackageOpen} accent="blue" />
                    <StatCard label="Asset categories" value={summary.categories} description="Distinct categories held in stock" icon={Boxes} accent="purple" />
                    <StatCard label="Locations holding stock" value={summary.locations} description="Sites with assets on the shelf" icon={Building2} accent="emerald" />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <PackageOpen className="size-5 text-primary" /> Stock inventory
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Asset</TableHead>
                                        <TableHead>Category</TableHead>
                                        <TableHead>Location</TableHead>
                                        <TableHead>Condition</TableHead>
                                        <TableHead>Stock in</TableHead>
                                        <TableHead className="text-right">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {assets.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={6} className="h-28 text-center text-muted-foreground">
                                                No unassigned assets are currently in stock.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        assets.map((asset) => (
                                            <TableRow key={asset.id}>
                                                <TableCell>
                                                    <div className="flex items-center gap-3">
                                                        {asset.image_url ? (
                                                            <img src={asset.image_url} alt="" className="size-10 rounded-md object-cover" />
                                                        ) : (
                                                            <span className="flex size-10 items-center justify-center rounded-md bg-muted">
                                                                <Boxes className="size-4 text-muted-foreground" />
                                                            </span>
                                                        )}
                                                        <div>
                                                            <p className="font-medium">{asset.asset_name}</p>
                                                            <p className="font-mono text-xs text-muted-foreground">{asset.asset_code}</p>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>{asset.category ?? '-'}</TableCell>
                                                <TableCell>{asset.location ?? '-'}</TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">{humanise(asset.condition)}</Badge>
                                                </TableCell>
                                                <TableCell className="text-muted-foreground">{asset.added_date ?? '-'}</TableCell>
                                                <TableCell className="text-right">
                                                    <Button variant="ghost" size="icon" asChild title="View asset">
                                                        <Link href={`/inventory/${asset.id}`}>
                                                            <Eye className="size-4" />
                                                        </Link>
                                                    </Button>
                                                    <Button size="sm" asChild>
                                                        <Link href={`/assignments/create?asset_id=${asset.id}`}>Assign</Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {pagination && <Pagination {...pagination} />}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
