import { FormEvent, useState } from "react";
import { Link, useForm } from "@inertiajs/react";
import { CalendarCog, CheckCircle2, Eye, Pencil, Plus, Wrench } from "lucide-react";
import AppLayout from "@/layouts/app-layout";
import ConfirmDelete from "@/components/confirm-delete";
import Pagination from "@/components/pagination";
import { TextField, TextareaField } from "@/components/field";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

interface RecordRow {
    id: number;
    title: string;
    asset_id: number;
    asset_code: string | null;
    asset_name: string | null;
    type: string | null;
    vendor: string | null;
    scheduled_at: string | null;
    cost: string | number;
    final_cost: string | number | null;
    status: string;
    status_label: string;
}
interface Props {
    title: string;
    description: string;
    records: RecordRow[];
    statuses: Record<string, string>;
    canManage: boolean;
    canView: boolean;
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from?: number; to?: number; total: number };
}
const statusStyles: Record<string, string> = {
    SCHEDULED: "bg-blue-100 text-blue-800",
    IN_PROGRESS: "bg-amber-100 text-amber-800",
    COMPLETED: "bg-emerald-100 text-emerald-800",
    CANCELLED: "bg-slate-100 text-slate-700",
};

function CompleteWorkOrder({ record }: { record: RecordRow }) {
    const [open, setOpen] = useState(false);
    const { data, setData, patch, processing, errors, reset } = useForm({
        final_cost: String(record.cost || "0"),
        completed_at: new Date().toISOString().slice(0, 10),
        completion_remarks: "",
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        patch(`/maintenance/${record.id}/complete`, {
            onSuccess: () => {
                reset();
                setOpen(false);
            },
        });
    };
    return (
        <>
            <Button
                variant="ghost"
                size="icon"
                title="Complete work order"
                onClick={() => setOpen(true)}
            >
                <CheckCircle2 className="size-4 text-emerald-600" />
            </Button>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <form onSubmit={submit}>
                        <DialogHeader>
                            <DialogTitle>Complete work order</DialogTitle>
                            <DialogDescription>
                                Save the final cost, completion date and closing
                                remarks for {record.title}.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-4 py-4">
                            <TextField
                                name="final_cost"
                                label="Final cost"
                                type="number"
                                step="0.01"
                                required
                                value={data.final_cost}
                                error={errors.final_cost}
                                onChange={(value) =>
                                    setData("final_cost", value)
                                }
                            />
                            <TextField
                                name="completed_at"
                                label="Complete date"
                                type="date"
                                required
                                value={data.completed_at}
                                error={errors.completed_at}
                                onChange={(value) =>
                                    setData("completed_at", value)
                                }
                            />
                            <TextareaField
                                name="completion_remarks"
                                label="Remarks"
                                rows={4}
                                value={data.completion_remarks}
                                error={errors.completion_remarks}
                                onChange={(value) =>
                                    setData("completion_remarks", value)
                                }
                            />
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setOpen(false)}
                            >
                                Cancel
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? "Saving..." : "Save and complete"}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}

export default function MaintenanceIndex({
    title,
    description,
    records,
    canManage,
    canView,
    pagination,
}: Props) {
    const open = records.filter(
        (record) =>
            record.status === "SCHEDULED" || record.status === "IN_PROGRESS",
    ).length;
    const totalCost = records.reduce(
        (sum, record) => sum + Number(record.final_cost ?? record.cost ?? 0),
        0,
    );
    return (
        <AppLayout
            title={title}
            description={description}
            actions={
                canManage ? (
                    <Button asChild>
                        <Link href="/maintenance/create">
                            <Plus /> Schedule maintenance
                        </Link>
                    </Button>
                ) : undefined
            }
        >
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-3">
                    <Card>
                        <CardContent className="flex items-center gap-4 p-5">
                            <Wrench className="size-5 text-primary" />
                            <div>
                                <p className="text-2xl font-semibold">{open}</p>
                                <p className="text-sm text-muted-foreground">
                                    Open work orders
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-4 p-5">
                            <CalendarCog className="size-5 text-primary" />
                            <div>
                                <p className="text-2xl font-semibold">
                                    {records.length}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Total records
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-5">
                            <p className="text-sm text-muted-foreground">
                                Final maintenance cost
                            </p>
                            <p className="mt-2 text-2xl font-semibold">
                                {totalCost.toLocaleString()}
                            </p>
                        </CardContent>
                    </Card>
                </div>
                <Card>
                    <CardHeader>
                        <CardTitle>Maintenance work orders</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Maintenance</TableHead>
                                        <TableHead>Asset</TableHead>
                                        <TableHead>Type</TableHead>
                                        <TableHead>Scheduled</TableHead>
                                        <TableHead>Vendor</TableHead>
                                        <TableHead>Estimate Cost</TableHead>
                                        <TableHead>Final Cost</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {records.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={9}
                                                className="h-28 text-center text-muted-foreground"
                                            >
                                                No maintenance records yet.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        records.map((record) => (
                                            <TableRow key={record.id}>
                                                <TableCell className="font-medium">
                                                    {record.title}
                                                </TableCell>
                                                <TableCell>
                                                    <Link
                                                        className="hover:underline"
                                                        href={`/inventory/${record.asset_id}`}
                                                    >
                                                        {record.asset_code ??
                                                            "-"}
                                                    </Link>
                                                    <p className="text-xs text-muted-foreground">
                                                        {record.asset_name ??
                                                            "-"}
                                                    </p>
                                                </TableCell>
                                                <TableCell>
                                                    {record.type ?? "-"}
                                                </TableCell>
                                                <TableCell>
                                                    {record.scheduled_at ?? "-"}
                                                </TableCell>
                                                <TableCell>
                                                    {record.vendor ?? "-"}
                                                </TableCell>
                                                <TableCell>
                                                    {Number(
                                                        record.cost || 0,
                                                    ).toLocaleString()}
                                                </TableCell>
                                                <TableCell>
                                                    {record.final_cost !==
                                                        null &&
                                                    record.final_cost !==
                                                        undefined
                                                        ? Number(
                                                              record.final_cost,
                                                          ).toLocaleString()
                                                        : "-"}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant="secondary"
                                                        className={
                                                            statusStyles[
                                                                record.status
                                                            ] ?? ""
                                                        }
                                                    >
                                                        {record.status_label}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    {canView && <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        asChild
                                                        title="View"
                                                    >
                                                        <Link
                                                            href={`/maintenance/${record.id}`}
                                                        >
                                                            <Eye className="size-4" />
                                                        </Link>
                                                    </Button>}
                                                    {canManage && (
                                                        <>
                                                            {record.status !==
                                                                "COMPLETED" &&
                                                                record.status !==
                                                                    "CANCELLED" && (
                                                                    <CompleteWorkOrder
                                                                        record={record}
                                                                    />
                                                                )}
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                asChild
                                                                title="Edit"
                                                            >
                                                                <Link
                                                                    href={`/maintenance/${record.id}/edit`}
                                                                >
                                                                    <Pencil className="size-4" />
                                                                </Link>
                                                            </Button>
                                                            <ConfirmDelete
                                                                url={`/maintenance/${record.id}`}
                                                                title={`Delete ${record.title}?`}
                                                                description="This maintenance record will be permanently removed."
                                                            />
                                                        </>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                        <Pagination {...pagination} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
