import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { SelectField, TextField, TextareaField, choicesFromList } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Option { id: number; label: string; }
interface Props { title: string; disposal: Record<string, any> | null; assets: Option[]; statuses: Record<string, string>; }

export default function DisposalForm({ title, disposal, assets, statuses }: Props) {
  const editing = Boolean(disposal);
  const { data, setData, post, put, processing, errors } = useForm({
    asset_id: String(disposal?.asset_id ?? ''),
    reason: String(disposal?.reason ?? ''),
    method: String(disposal?.method ?? ''),
    value_recovered: String(disposal?.value_recovered ?? ''),
    notes: String(disposal?.notes ?? ''),
  });

  const submit = (event: FormEvent) => {
    event.preventDefault();
    if (editing) {
      put(`/disposals/${disposal?.id}`);
      return;
    }
    post('/disposals');
  };

  return (
    <AppLayout title={title} description="Create asset disposal request" actions={<Button variant="outline" asChild><Link href="/disposals"><ArrowLeft /> Back</Link></Button>}>
      <div className="mx-auto max-w-3xl">
        <Card>
          <CardHeader>
            <CardTitle>{editing ? 'Update disposal request' : 'New disposal request'}</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-5">
              <SelectField name="asset_id" label="Asset" required value={data.asset_id} error={errors.asset_id} choices={choicesFromList(assets)} emptyLabel="Select asset" onChange={(value) => setData('asset_id', value)} />
              <TextField name="reason" label="Disposal reason" value={data.reason} error={errors.reason} onChange={(value) => setData('reason', value)} />
              <TextField name="method" label="Disposal method" value={data.method} error={errors.method} onChange={(value) => setData('method', value)} />
              <TextField name="value_recovered" label="Value recovered" type="number" min="0" step="0.01" value={data.value_recovered} error={errors.value_recovered} onChange={(value) => setData('value_recovered', value)} />
              <TextareaField name="notes" label="Notes" rows={4} value={data.notes} error={errors.notes} onChange={(value) => setData('notes', value)} />
              <div className="flex justify-end gap-3">
                <Button type="button" variant="outline" asChild><Link href="/disposals">Cancel</Link></Button>
                <Button type="submit" disabled={processing}><Save className="size-4" /> {processing ? 'Saving...' : 'Save disposal'}</Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
