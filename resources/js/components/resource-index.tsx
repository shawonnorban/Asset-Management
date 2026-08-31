import { Link } from '@inertiajs/react';
import { Eye, Pencil, Plus } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

export interface ResourceRow {
    id: number;
    [key: string]: unknown;
}

export interface ResourceColumn {
    key: string;
    label: string;
    badge?: boolean;
}

interface Props {
    title: string;
    description: string;
    rows: ResourceRow[];
    columns: ResourceColumn[];
    base: string;
    createLabel?: string;
    canManage?: boolean;
    detail?: boolean;
    detailPath?: (id: number) => string;
    createPath?: string;
}

const display = (value: unknown) => value === null || value === undefined || value === '' ? '-' : String(value);

export default function ResourceIndex({ title, description, rows, columns, base, createLabel = 'Add', canManage = false, detail = false, detailPath, createPath }: Props) {
    return (
        <AppLayout
            title={title}
            description={description}
            actions={canManage ? <Button asChild><Link href={createPath ?? `${base}/create`}><Plus /> {createLabel}</Link></Button> : undefined}
        >
            <Card>
                <CardContent className="pt-6">
                    <div className="overflow-x-auto rounded-md border">
                        <Table>
                            <TableHeader><TableRow>
                                <TableHead className="w-16">No</TableHead>
                                {columns.map((column) => <TableHead key={column.key}>{column.label}</TableHead>)}
                                {(canManage || detail) && <TableHead className="w-28 text-right">Actions</TableHead>}
                            </TableRow></TableHeader>
                            <TableBody>
                                {rows.length === 0 && <TableRow><TableCell colSpan={columns.length + 1 + Number(canManage || detail)} className="h-24 text-center text-muted-foreground">No records found.</TableCell></TableRow>}
                                {rows.map((row, index) => <TableRow key={row.id}>
                                    <TableCell className="text-muted-foreground">{index + 1}</TableCell>
                                    {columns.map((column) => <TableCell key={column.key}>
                                        {column.badge ? <Badge variant="secondary">{display(row[column.key])}</Badge> : display(row[column.key])}
                                    </TableCell>)}
                                    {(canManage || detail) && <TableCell><div className="flex justify-end gap-1">
                                        {detail && <Button variant="ghost" size="icon" asChild title="View"><Link href={detailPath ? detailPath(row.id) : `${base}/${row.id}`}><Eye className="size-4" /></Link></Button>}
                                        {canManage && <Button variant="ghost" size="icon" asChild title="Edit"><Link href={`${base}/${row.id}/edit`}><Pencil className="size-4" /></Link></Button>}
                                    </div></TableCell>}
                                </TableRow>)}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
