import AppLayout from '@/layouts/app-layout';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Calendar, Save, Wrench } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { SelectField, TextField } from '@/components/field';

interface Props {
  title: string;
  canManage: boolean;
  maintenanceRequest: {
    id: number;
    title: string;
    asset: { id: number; code: string | null; name: string | null } | null;
    maintenance_type: string | null;
    priority: string;
    description: string | null;
    vendor_name: string | null;
    status: string;
    status_label: string;
    scheduled_at: string | null;
    requested_at: string | null;
    requested_by: string | null;
    assigned_to: string | null;
    assigned_to_id: number | null;
  };
  users: { id: number; label: string }[];
}

const statusStyles: Record<string, string> = {
  OPEN: 'bg-blue-100 text-blue-800',
  IN_PROGRESS: 'bg-amber-100 text-amber-800',
  COMPLETED: 'bg-emerald-100 text-emerald-800',
  CANCELLED: 'bg-slate-100 text-slate-700',
};

export default function MaintenanceRequestShow({ title, canManage, maintenanceRequest, users }: Props) {
  const { data, setData, patch, processing, errors } = useForm({
    assigned_to: maintenanceRequest.assigned_to_id ? String(maintenanceRequest.assigned_to_id) : '',
    status: maintenanceRequest.status,
    scheduled_at: maintenanceRequest.scheduled_at ?? '',
    vendor_name: maintenanceRequest.vendor_name ?? '',
  });

  const submit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    patch(`/maintenance-requests/${maintenanceRequest.id}/assign`);
  };

  return (
    <AppLayout title={title} description="Maintenance request detail" actions={<Button variant="outline" asChild><Link href="/maintenance-requests"><ArrowLeft /> Back</Link></Button>}>
      <div className="space-y-6">
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between gap-4">
              <CardTitle className="flex items-center gap-2"><Wrench className="size-5" /> {maintenanceRequest.title}</CardTitle>
              <Badge className={statusStyles[maintenanceRequest.status] ?? ''}>{maintenanceRequest.status_label}</Badge>
            </div>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground">Asset</p>
                <p className="font-medium">{maintenanceRequest.asset?.code ?? '-'} · {maintenanceRequest.asset?.name ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Priority</p>
                <p className="font-medium">{maintenanceRequest.priority}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Type</p>
                <p className="font-medium">{maintenanceRequest.maintenance_type ?? 'General service'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Vendor / technician</p>
                <p className="font-medium">{maintenanceRequest.vendor_name ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Requested by</p>
                <p className="font-medium">{maintenanceRequest.requested_by ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Assigned to</p>
                <p className="font-medium">{maintenanceRequest.assigned_to ?? '-'}</p>
              </div>
            </div>

            <div className="rounded-lg border bg-muted/20 p-4">
              <p className="text-sm text-muted-foreground">Description</p>
              <p className="mt-2 whitespace-pre-wrap">{maintenanceRequest.description ?? 'No description provided.'}</p>
            </div>

            <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
              <span className="inline-flex items-center gap-2"><Calendar className="size-4" /> Requested: {maintenanceRequest.requested_at ?? '-'}</span>
              <span className="inline-flex items-center gap-2"><Calendar className="size-4" /> Scheduled: {maintenanceRequest.scheduled_at ?? '-'}</span>
            </div>

            {canManage && (
              <form onSubmit={submit} className="rounded-lg border bg-slate-50 p-4 space-y-4">
                <div className="flex items-center justify-between gap-3">
                  <h3 className="font-semibold">Manager actions</h3>
                  <Button type="submit" size="sm" disabled={processing}>
                    <Save className="size-4" /> {processing ? 'Saving...' : 'Assign & schedule'}
                  </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                  <SelectField
                    name="assigned_to"
                    label="Assign technician"
                    value={data.assigned_to}
                    error={errors.assigned_to}
                    emptyLabel="Unassigned"
                    choices={users.map((user) => ({ value: String(user.id), label: user.label }))}
                    onChange={(value) => setData('assigned_to', value)}
                  />

                  <SelectField
                    name="status"
                    label="Status"
                    value={data.status}
                    error={errors.status}
                    choices={Object.entries({ OPEN: 'Open', IN_PROGRESS: 'In Progress', COMPLETED: 'Completed', CANCELLED: 'Cancelled' }).map(([value, label]) => ({ value, label }))}
                    onChange={(value) => setData('status', value)}
                  />

                  <TextField
                    name="scheduled_at"
                    label="Scheduled date"
                    type="date"
                    value={data.scheduled_at}
                    error={errors.scheduled_at}
                    onChange={(value) => setData('scheduled_at', value)}
                  />
                </div>

                <TextField
                  name="vendor_name"
                  label="Vendor / technician"
                  value={data.vendor_name}
                  error={errors.vendor_name}
                  onChange={(value) => setData('vendor_name', value)}
                />
              </form>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
