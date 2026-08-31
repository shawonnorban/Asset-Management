import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save, Wrench } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { SelectField, TextField, TextareaField, choicesFrom, choicesFromList } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Option { id: number; label: string; }
interface Props { title: string; record: Record<string, unknown> | null; assets: Option[]; statuses: Record<string, string>; }

export default function MaintenanceForm({ title, record, assets, statuses }: Props) {
    const editing = Boolean(record);
    const { data, setData, post, put, processing, errors } = useForm({
        asset_id: String(record?.asset_id ?? ''), title: String(record?.title ?? ''), maintenance_type: String(record?.maintenance_type ?? ''),
        description: String(record?.description ?? ''), vendor: String(record?.vendor ?? ''), scheduled_at: String(record?.scheduled_at ?? ''),
        completed_at: String(record?.completed_at ?? ''), cost: String(record?.cost ?? '0'), status: String(record?.status ?? 'SCHEDULED'),
    });
    const submit = (event: FormEvent) => { event.preventDefault(); editing ? put(`/maintenance/${record?.id}`) : post('/maintenance'); };
    return <AppLayout title={title} description="Record preventive and corrective asset service" actions={<Button variant="outline" asChild><Link href="/maintenance"><ArrowLeft /> Back to maintenance</Link></Button>}>
        <div className="mx-auto max-w-3xl"><Card><CardHeader><CardTitle className="flex items-center gap-3"><span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary"><Wrench className="size-5" /></span>{editing ? 'Update work order' : 'Schedule maintenance'}</CardTitle></CardHeader><CardContent><form onSubmit={submit} className="space-y-5"><SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={choicesFromList(assets)} emptyLabel="Select asset" onChange={(value) => setData('asset_id', value)} /><div className="grid gap-5 sm:grid-cols-2"><TextField name="title" label="Maintenance title" required value={data.title} error={errors.title} onChange={(value) => setData('title', value)} /><TextField name="maintenance_type" label="Type" placeholder="Preventive, repair..." value={data.maintenance_type} error={errors.maintenance_type} onChange={(value) => setData('maintenance_type', value)} /></div><TextareaField name="description" label="Description" rows={4} value={data.description} error={errors.description} onChange={(value) => setData('description', value)} /><div className="grid gap-5 sm:grid-cols-2"><TextField name="vendor" label="Vendor / technician" value={data.vendor} error={errors.vendor} onChange={(value) => setData('vendor', value)} /><TextField name="cost" label="Estimate cost" type="number" step="0.01" value={data.cost} error={errors.cost} onChange={(value) => setData('cost', value)} /></div><div className="grid gap-5 sm:grid-cols-2"><TextField name="scheduled_at" label="Scheduled date" type="date" value={data.scheduled_at} error={errors.scheduled_at} onChange={(value) => setData('scheduled_at', value)} /><TextField name="completed_at" label="Completed date" type="date" value={data.completed_at} error={errors.completed_at} onChange={(value) => setData('completed_at', value)} /></div><SelectField name="status" label="Status" required value={data.status} error={errors.status} choices={choicesFrom(statuses)} onChange={(value) => setData('status', value)} /><div className="flex justify-end gap-2 border-t pt-5"><Button type="button" variant="outline" asChild><Link href="/maintenance">Cancel</Link></Button><Button type="submit" disabled={processing}><Save /> {processing ? 'Saving...' : editing ? 'Save changes' : 'Schedule maintenance'}</Button></div></form></CardContent></Card></div>
    </AppLayout>;
}
