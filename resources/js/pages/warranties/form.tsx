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
  warranty: Record<string, any> | null;
  assets: Option[];
  statuses: Record<string, string>;
}

export default function WarrantyForm({ title, warranty, assets, statuses }: Props) {
  const editing = Boolean(warranty);
  const { data, setData, post, put, processing, errors } = useForm({
    asset_id: String(warranty?.asset_id ?? ''),
    vendor_name: String(warranty?.vendor_name ?? ''),
    warranty_type: String(warranty?.warranty_type ?? ''),
    start_date: String(warranty?.start_date ?? ''),
    end_date: String(warranty?.end_date ?? ''),
    status: String(warranty?.status ?? 'ACTIVE'),
    coverage_details: String(warranty?.coverage_details ?? ''),
    claim_status: String(warranty?.claim_status ?? 'PENDING'),
  });

  const submit = (event: FormEvent) => {
    event.preventDefault();
    if (editing) {
      put(`/warranties/${warranty?.id}`);
      return;
    }
    post('/warranties');
  };

  return (
    <AppLayout title={title} description="Register an asset warranty" actions={<Button variant="outline" asChild><Link href="/warranties"><ArrowLeft /> Back</Link></Button>}>
      <div className="mx-auto max-w-3xl">
        <Card>
          <CardHeader>
            <CardTitle>{editing ? 'Edit warranty' : 'Add warranty'}</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-5">
              <SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={choicesFromList(assets)} emptyLabel="Select asset" onChange={(value) => setData('asset_id', value)} />
              <div className="grid gap-5 sm:grid-cols-2">
                <TextField name="vendor_name" label="Vendor" value={data.vendor_name} error={errors.vendor_name} onChange={(value) => setData('vendor_name', value)} />
                <TextField name="warranty_type" label="Warranty type" value={data.warranty_type} error={errors.warranty_type} onChange={(value) => setData('warranty_type', value)} />
              </div>
              <div className="grid gap-5 sm:grid-cols-2">
                <TextField name="start_date" label="Start date" type="date" value={data.start_date} error={errors.start_date} onChange={(value) => setData('start_date', value)} />
                <TextField name="end_date" label="End date" type="date" value={data.end_date} error={errors.end_date} onChange={(value) => setData('end_date', value)} />
              </div>
              <div className="grid gap-5 sm:grid-cols-2">
                <SelectField name="status" label="Status" required value={data.status} error={errors.status} choices={Object.entries(statuses).map(([value, label]) => ({ value, label }))} onChange={(value) => setData('status', value)} />
                <SelectField name="claim_status" label="Claim status" required value={data.claim_status} error={errors.claim_status} choices={[
                  { value: 'PENDING', label: 'Pending' },
                  { value: 'IN_PROGRESS', label: 'In progress' },
                  { value: 'CLAIMED', label: 'Claimed' },
                  { value: 'REJECTED', label: 'Rejected' },
                ]} onChange={(value) => setData('claim_status', value)} />
              </div>
              <TextareaField name="coverage_details" label="Coverage details" rows={4} value={data.coverage_details} error={errors.coverage_details} onChange={(value) => setData('coverage_details', value)} />
              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" asChild><Link href="/warranties">Cancel</Link></Button>
                <Button type="submit" disabled={processing}><Save className="size-4" /> {processing ? 'Saving...' : 'Save warranty'}</Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
