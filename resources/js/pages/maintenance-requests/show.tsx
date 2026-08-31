import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, Wrench } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  title: string;
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
  };
}

const statusStyles: Record<string, string> = {
  OPEN: 'bg-blue-100 text-blue-800',
  IN_PROGRESS: 'bg-amber-100 text-amber-800',
  COMPLETED: 'bg-emerald-100 text-emerald-800',
  CANCELLED: 'bg-slate-100 text-slate-700',
};

export default function MaintenanceRequestShow({ title, maintenanceRequest }: Props) {
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
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
