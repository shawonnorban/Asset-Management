import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { SelectField, TextField, TextareaField, choicesFromList } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Option { id: number; label: string; }
interface Props {
  title: string;
  request: Record<string, any> | null;
  assets: Option[];
  statuses: Record<string, string>;
}

export default function MaintenanceRequestForm({ title, request, assets, statuses }: Props) {
  const editing = Boolean(request);
  const { data, setData, post, put, processing, errors } = useForm({
    asset_id: String(request?.asset_id ?? ''),
    title: String(request?.title ?? ''),
    maintenance_type: String(request?.maintenance_type ?? ''),
    description: String(request?.description ?? ''),
    priority: String(request?.priority ?? 'MEDIUM'),
    status: String(request?.status ?? 'OPEN'),
    scheduled_at: String(request?.scheduled_at ?? ''),
    vendor_name: String(request?.vendor_name ?? ''),
  });

  const submit = (event: FormEvent) => {
    event.preventDefault();
    if (editing) {
      put(`/maintenance-requests/${request?.id}`);
      return;
    }
    post('/maintenance-requests');
  };

  return (
    <AppLayout title={title} description="Create a maintenance request for an asset" actions={<Button variant="outline" asChild><Link href="/maintenance-requests"><ArrowLeft /> Back</Link></Button>}>
      <div className="mx-auto max-w-3xl">
        <Card>
          <CardHeader>
            <CardTitle>{editing ? 'Update maintenance request' : 'New maintenance request'}</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-5">
              <SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={choicesFromList(assets)} emptyLabel="Select asset" onChange={(value) => setData('asset_id', value)} />
              <div className="grid gap-5 sm:grid-cols-2">
                <TextField name="title" label="Title" required value={data.title} error={errors.title} onChange={(value) => setData('title', value)} />
                <TextField name="maintenance_type" label="Maintenance type" value={data.maintenance_type} error={errors.maintenance_type} onChange={(value) => setData('maintenance_type', value)} />
              </div>
              <div className="grid gap-5 sm:grid-cols-2">
                <SelectField name="priority" label="Priority" required value={data.priority} error={errors.priority} choices={[
                  { value: 'LOW', label: 'Low' },
                  { value: 'MEDIUM', label: 'Medium' },
                  { value: 'HIGH', label: 'High' },
                  { value: 'CRITICAL', label: 'Critical' },
                ]} onChange={(value) => setData('priority', value)} />
                <SelectField name="status" label="Status" required value={data.status} error={errors.status} choices={Object.entries(statuses).map(([value, label]) => ({ value, label }))} onChange={(value) => setData('status', value)} />
              </div>
              <TextareaField name="description" label="Description" rows={4} value={data.description} error={errors.description} onChange={(value) => setData('description', value)} />
              <div className="grid gap-5 sm:grid-cols-2">
                <TextField name="vendor_name" label="Vendor / technician" value={data.vendor_name} error={errors.vendor_name} onChange={(value) => setData('vendor_name', value)} />
                <TextField name="scheduled_at" label="Scheduled date" type="date" value={data.scheduled_at} error={errors.scheduled_at} onChange={(value) => setData('scheduled_at', value)} />
              </div>
              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" asChild><Link href="/maintenance-requests">Cancel</Link></Button>
                <Button type="submit" disabled={processing}><Save className="size-4" /> {processing ? 'Saving...' : 'Save request'}</Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
