import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Eye, Plus } from 'lucide-react';

interface WarrantyItem {
  id: number;
  asset_code: string | null;
  asset_name: string | null;
  vendor_name: string | null;
  warranty_type: string | null;
  start_date: string | null;
  end_date: string | null;
  status: string;
  status_label: string;
  claim_status: string | null;
}

interface Props {
  title: string;
  description: string;
  warranties: WarrantyItem[];
  statuses: Record<string, string>;
}

const statusStyles: Record<string, string> = {
  ACTIVE: 'bg-emerald-100 text-emerald-800',
  EXPIRING_SOON: 'bg-amber-100 text-amber-800',
  EXPIRED: 'bg-rose-100 text-rose-800',
  CLAIMED: 'bg-blue-100 text-blue-800',
  VOID: 'bg-slate-100 text-slate-700',
};

export default function WarrantiesIndex({ title, description, warranties }: Props) {
  return (
    <AppLayout title={title} description={description} actions={<Button asChild><Link href="/warranties/create"><Plus className="size-4" /> Add warranty</Link></Button>}>
      <div className="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Warranty register</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {warranties.length === 0 ? (
                <p className="text-sm text-muted-foreground">No warranty records yet.</p>
              ) : (
                warranties.map((warranty) => (
                  <div key={warranty.id} className="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between">
                    <div>
                      <div className="flex items-center gap-2">
                        <p className="font-semibold">{warranty.asset_code ?? '-'} · {warranty.asset_name ?? '-'}</p>
                        <Badge className={statusStyles[warranty.status] ?? ''}>{warranty.status_label}</Badge>
                      </div>
                      <p className="mt-1 text-sm text-muted-foreground">
                        {warranty.vendor_name ?? '-'} · {warranty.warranty_type ?? 'Standard'}
                      </p>
                      <p className="mt-1 text-sm text-muted-foreground">
                        {warranty.start_date ?? '-'} to {warranty.end_date ?? '-'}
                      </p>
                    </div>
                    <Button variant="outline" size="sm" asChild>
                      <Link href={`/warranties/${warranty.id}`}><Eye className="size-4" /> View</Link>
                    </Button>
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
