import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Eye } from 'lucide-react';

interface RequestItem {
  id: number;
  title: string;
  asset_code: string | null;
  asset_name: string | null;
  maintenance_type: string | null;
  priority: string;
  status: string;
  status_label: string;
  scheduled_at: string | null;
  requested_by: string | null;
  vendor_name: string | null;
}

interface Props {
  title: string;
  description: string;
  requests: RequestItem[];
  statuses: Record<string, string>;
  canManage: boolean;
  pagination: { links: { url: string | null; label: string; active: boolean }[]; total: number };
}

const statusStyles: Record<string, string> = {
  OPEN: 'bg-blue-100 text-blue-800',
  IN_PROGRESS: 'bg-amber-100 text-amber-800',
  COMPLETED: 'bg-emerald-100 text-emerald-800',
  CANCELLED: 'bg-slate-100 text-slate-700',
};

export default function MaintenanceRequestsIndex({ title, description, requests, canManage }: Props) {
  return (
    <AppLayout
      title={title}
      description={description}
      actions={
        <Button asChild>
          <Link href="/maintenance-requests/create">
            <Plus className="size-4" /> New request
          </Link>
        </Button>
      }
    >
      <div className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Open maintenance requests</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {requests.length === 0 ? (
                <p className="text-sm text-muted-foreground">No maintenance requests yet.</p>
              ) : (
                requests.map((request) => (
                  <div key={request.id} className="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between">
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-semibold">{request.title}</p>
                        <Badge className={statusStyles[request.status] ?? ''}>{request.status_label}</Badge>
                      </div>
                      <p className="mt-1 text-sm text-muted-foreground">
                        {request.asset_code ?? '-'} · {request.asset_name ?? '-'}
                      </p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        {request.maintenance_type ?? 'General service'} · Priority: {request.priority}
                      </p>
                    </div>
                    <div className="flex items-center gap-2">
                      <Button variant="outline" size="sm" asChild>
                        <Link href={`/maintenance-requests/${request.id}`}>
                          <Eye className="size-4" /> View
                        </Link>
                      </Button>
                    </div>
                  </div>
                ))
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
