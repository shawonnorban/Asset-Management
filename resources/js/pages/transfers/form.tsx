import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { SelectField, TextField, TextareaField, choicesFromList } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Option { id: number; label: string; }
interface Props { title: string; transfer: Record<string, any> | null; assets: Option[]; locations: Option[]; employees: Option[]; statuses: Record<string, string>; }

export default function TransferForm({ title, transfer, assets, locations, employees, statuses }: Props) {
  const editing = Boolean(transfer);
  const { data, setData, post, put, processing, errors } = useForm({
    asset_id: String(transfer?.asset_id ?? ''),
    from_location_id: String(transfer?.from_location_id ?? ''),
    to_location_id: String(transfer?.to_location_id ?? ''),
    from_employee_id: String(transfer?.from_employee_id ?? ''),
    to_employee_id: String(transfer?.to_employee_id ?? ''),
    reason: String(transfer?.reason ?? ''),
    notes: String(transfer?.notes ?? ''),
  });

  const submit = (event: FormEvent) => {
    event.preventDefault();
    if (editing) {
      put(`/transfers/${transfer?.id}`);
      return;
    }
    post('/transfers');
  };

  return (
    <AppLayout title={title} description="Create asset transfer request" actions={<Button variant="outline" asChild><Link href="/transfers"><ArrowLeft /> Back</Link></Button>}>
      <div className="mx-auto max-w-3xl">
        <Card>
          <CardHeader>
            <CardTitle>{editing ? 'Update transfer request' : 'New transfer request'}</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-5">
              <SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={choicesFromList(assets)} emptyLabel="Select asset" onChange={(value) => setData('asset_id', value)} />
              <div className="grid gap-5 sm:grid-cols-2">
                <SelectField name="from_location_id" label="From location" value={data.from_location_id} error={errors.from_location_id} choices={choicesFromList(locations)} emptyLabel="Select location" onChange={(value) => setData('from_location_id', value)} />
                <SelectField name="to_location_id" label="To location" value={data.to_location_id} error={errors.to_location_id} choices={choicesFromList(locations)} emptyLabel="Select location" onChange={(value) => setData('to_location_id', value)} />
              </div>
              <div className="grid gap-5 sm:grid-cols-2">
                <SelectField name="from_employee_id" label="From employee" value={data.from_employee_id} error={errors.from_employee_id} choices={choicesFromList(employees)} emptyLabel="Select employee" onChange={(value) => setData('from_employee_id', value)} />
                <SelectField name="to_employee_id" label="To employee" value={data.to_employee_id} error={errors.to_employee_id} choices={choicesFromList(employees)} emptyLabel="Select employee" onChange={(value) => setData('to_employee_id', value)} />
              </div>
              <TextField name="reason" label="Transfer reason" value={data.reason} error={errors.reason} onChange={(value) => setData('reason', value)} />
              <TextareaField name="notes" label="Notes" rows={4} value={data.notes} error={errors.notes} onChange={(value) => setData('notes', value)} />
              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" asChild><Link href="/transfers">Cancel</Link></Button>
                <Button type="submit" disabled={processing}><Save className="size-4" /> {processing ? 'Saving...' : 'Save transfer'}</Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
