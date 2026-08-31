import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Pencil, Plus, Save } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import ConfirmDelete from '@/components/confirm-delete';
import { SelectField, TextField } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import Pagination from '@/components/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

export interface SimpleRow {
    id: number;
    name: string;
    usage_count?: number;
    [key: string]: unknown;
}

interface IndexProps {
    title: string;
    description?: string;
    rows: SimpleRow[];
    base: string;
    nameHeading: string;
    detailHeading?: string;
    detailKey?: string;
    usageHeading?: string;
    canManage: boolean;
    extra?: React.ReactNode;
    pagination?: { links: { url: string | null; label: string; active: boolean }[]; from?: number; to?: number; total: number };
}

/**
 * Departments, positions, categories and locations are the same page with a
 * different noun, so they share one component instead of four near-copies.
 */
export function SimpleIndex({
    title,
    description,
    rows,
    base,
    nameHeading,
    detailHeading,
    detailKey,
    usageHeading,
    canManage,
    extra,
    pagination,
}: IndexProps) {
    return (
        <AppLayout
            title={title}
            description={description ?? `${rows.length} records`}
            actions={
                <>
                    {extra}
                    {canManage && (
                        <Button asChild>
                            <Link href={`${base}/create`}>
                                <Plus /> Add
                            </Link>
                        </Button>
                    )}
                </>
            }
        >
            <Card>
                <CardContent className="pt-6">
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-16">No</TableHead>
                                    <TableHead>{nameHeading}</TableHead>
                                    {detailHeading && <TableHead>{detailHeading}</TableHead>}
                                    {usageHeading && <TableHead className="w-40">{usageHeading}</TableHead>}
                                    {canManage && <TableHead className="w-32 text-right">Actions</TableHead>}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {rows.length === 0 && (
                                    <TableRow>
                                        <TableCell
                                            colSpan={2 + Number(Boolean(detailHeading)) + Number(Boolean(usageHeading)) + Number(canManage)}
                                            className="h-24 text-center text-muted-foreground"
                                        >
                                            Nothing recorded yet.
                                        </TableCell>
                                    </TableRow>
                                )}

                                {rows.map((row, index) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="text-muted-foreground">{index + 1}</TableCell>
                                        <TableCell className="font-medium">{row.name}</TableCell>
                                        {detailHeading && (
                                            <TableCell className="text-muted-foreground">
                                                {detailKey ? String(row[detailKey] ?? '') : ''}
                                            </TableCell>
                                        )}
                                        {usageHeading && (
                                            <TableCell className="text-muted-foreground">
                                                {row.usage_count ?? 0}
                                            </TableCell>
                                        )}
                                        {canManage && (
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button variant="ghost" size="icon" asChild title="Edit">
                                                        <Link href={`${base}/${row.id}/edit`}>
                                                            <Pencil className="size-4" />
                                                        </Link>
                                                    </Button>
                                                    <ConfirmDelete
                                                        url={`${base}/${row.id}`}
                                                        title={`Delete ${row.name}?`}
                                                        description="Records still pointing at it will block the delete."
                                                    />
                                                </div>
                                            </TableCell>
                                        )}
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                    {pagination && <Pagination {...pagination} />}
                </CardContent>
            </Card>
        </AppLayout>
    );
}

interface FormProps {
    title: string;
    base: string;
    field: string;
    fieldLabel: string;
    record: { id: number } & Record<string, unknown> | null;
    initial?: Record<string, string>;
    httpMethod?: 'post' | 'put';
    actionUrl?: string;
    fieldChoices?: { value: string; label: string }[];
    children?: (
        data: Record<string, string>,
        setData: (key: string, value: string) => void,
        errors: Record<string, string>,
    ) => React.ReactNode;
}

export function SimpleForm({
    title,
    base,
    field,
    fieldLabel,
    record,
    initial = {},
    children,
    httpMethod = 'post',
    actionUrl,
    fieldChoices,
}: FormProps) {
    const editing = Boolean(record);

    const { data, setData, post, put, processing, errors } = useForm<Record<string, string>>({
        [field]: (record?.[field] as string) ?? '',
        ...Object.fromEntries(
            Object.entries(initial).map(([key, fallback]) => [key, (record?.[key] as string) ?? fallback]),
        ),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing || httpMethod === 'put') {
            put(actionUrl ?? `${base}/${record!.id}`);
        } else {
            post(actionUrl ?? base);
        }
    };

    return (
        <AppLayout
            title={title}
            actions={
                <Button variant="outline" asChild>
                    <Link href={base}>
                        <ArrowLeft /> Back
                    </Link>
                </Button>
            }
        >
            <form onSubmit={submit} className="max-w-xl">
                <Card>
                    <CardContent className="space-y-4 pt-6">
                        {fieldChoices ? (
                            <SelectField name={field} label={fieldLabel} required value={data[field]} error={errors[field]} choices={fieldChoices} onChange={(v) => setData(field, v)} />
                        ) : (
                            <TextField name={field} label={fieldLabel} required value={data[field]} error={errors[field]} onChange={(v) => setData(field, v)} />
                        )}

                        {children?.(data, (key, value) => setData(key, value), errors as Record<string, string>)}

                        <Button type="submit" disabled={processing}>
                            <Save /> {editing ? 'Update' : 'Save'}
                        </Button>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
