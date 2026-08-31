import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, ClipboardCheck } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { SelectField, TextField, TextareaField, choicesFrom, choicesFromList } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const CONDITIONS = { NEW: 'New', GOOD: 'Good', FAIR: 'Fair', POOR: 'Poor' };
const today = () => new Date().toISOString().slice(0, 10);
interface Option { id: number; label: string; }
interface AssetOption extends Option { category: string | null; status: string; }
interface Props { assets: AssetOption[]; employees: Option[]; locations: Option[]; selectedAssetId: number | null; }

export default function AssignmentCreate({ assets, employees, locations, selectedAssetId }: Props) {
    const { data, setData, post, processing, errors } = useForm({ asset_id: selectedAssetId ? String(selectedAssetId) : '', employee_id: '', location_id: '', assigned_at: today(), condition_on_assign: '', note: '' });
    const submit = (event: FormEvent) => { event.preventDefault(); post('/assignments'); };

    return <AppLayout title="New Asset Assignment" description="Record a dated handover between an asset and an employee" actions={<Button variant="outline" asChild><Link href="/assignments"><ArrowLeft /> Back to assignments</Link></Button>}>
        <div className="mx-auto max-w-3xl"><Card><CardHeader className="border-b"><CardTitle className="flex items-center gap-3"><span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary"><ClipboardCheck className="size-5" /></span>Assignment details</CardTitle></CardHeader><CardContent className="p-6"><form onSubmit={submit} className="space-y-5"><SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={choicesFromList(assets)} emptyLabel="Select an available asset" onChange={(value) => setData('asset_id', value)} /><div className="grid gap-5 sm:grid-cols-2"><SelectField name="employee_id" label="Employee" required value={data.employee_id} error={errors.employee_id} choices={choicesFromList(employees)} emptyLabel="Select employee" onChange={(value) => setData('employee_id', value)} /><SelectField name="location_id" label="Handover location" value={data.location_id} error={errors.location_id} choices={choicesFromList(locations)} emptyLabel="Use current location" onChange={(value) => setData('location_id', value)} /></div><div className="grid gap-5 sm:grid-cols-2"><TextField name="assigned_at" label="Assigned on" type="date" required value={data.assigned_at} error={errors.assigned_at} onChange={(value) => setData('assigned_at', value)} /><SelectField name="condition_on_assign" label="Condition at handover" value={data.condition_on_assign} error={errors.condition_on_assign} choices={choicesFrom(CONDITIONS)} emptyLabel="Not recorded" onChange={(value) => setData('condition_on_assign', value)} /></div><TextareaField name="note" label="Note" rows={4} value={data.note} error={errors.note} onChange={(value) => setData('note', value)} /><div className="flex justify-end gap-2 border-t pt-5"><Button type="button" variant="outline" asChild><Link href="/assignments">Cancel</Link></Button><Button type="submit" disabled={processing}>{processing ? 'Saving...' : 'Record assignment'}</Button></div></form></CardContent></Card></div>
    </AppLayout>;
}
