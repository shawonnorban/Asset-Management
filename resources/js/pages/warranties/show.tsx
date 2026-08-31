import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { ArrowLeft, ShieldCheck } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  title: string;
  warranty: {
    id: number;
    asset: { id: number; code: string | null; name: string | null } | null;
    vendor_name: string | null;
    warranty_type: string | null;
    start_date: string | null;
    end_date: string | null;
    status: string;
    status_label: string;
    coverage_details: string | null;
    claim_status: string | null;
  };
}

const statusStyles: Record<string, string> = {
  ACTIVE: 'bg-emerald-100 text-emerald-800',
  EXPIRING_SOON: 'bg-amber-100 text-amber-800',
  EXPIRED: 'bg-rose-100 text-rose-800',
  CLAIMED: 'bg-blue-100 text-blue-800',
  VOID: 'bg-slate-100 text-slate-700',
};

export default function WarrantyShow({ title, warranty }: Props) {
  return (
    <AppLayout title={title} description="Warranty detail" actions={<Button variant="outline" asChild><Link href="/warranties"><ArrowLeft /> Back</Link></Button>}>
      <div className="space-y-6">
        <Card>
          <CardHeader>
            <div className="flex items-center justify-between gap-4">
              <CardTitle className="flex items-center gap-2"><ShieldCheck className="size-5" /> Warranty</CardTitle>
              <Badge className={statusStyles[warranty.status] ?? ''}>{warranty.status_label}</Badge>
            </div>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground">Asset</p>
                <p className="font-medium">{warranty.asset?.code ?? '-'} · {warranty.asset?.name ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Warranty type</p>
                <p className="font-medium">{warranty.warranty_type ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Vendor</p>
                <p className="font-medium">{warranty.vendor_name ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Claim status</p>
                <p className="font-medium">{warranty.claim_status ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Start date</p>
                <p className="font-medium">{warranty.start_date ?? '-'}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">End date</p>
                <p className="font-medium">{warranty.end_date ?? '-'}</p>
              </div>
            </div>

            <div className="rounded-lg border bg-muted/20 p-4">
              <p className="text-sm text-muted-foreground">Coverage details</p>
              <p className="mt-2 whitespace-pre-wrap">{warranty.coverage_details ?? 'No coverage details provided.'}</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
