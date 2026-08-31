import { Link } from '@inertiajs/react';
import { ArrowLeft, FileWarning } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Report {
    id: number;
    title: string;
    description: string;
    status: string;
    created: string | null;
    image_url: string | null;
    asset: { id: number; code: string; name: string; status: string } | null;
}

export default function MyReportShow({ title, report }: { title: string; report: Report }) {
    return (
        <AppLayout title={title} actions={<Button variant="outline" asChild><Link href="/my-reports"><ArrowLeft /> My reports</Link></Button>}>
            <div className="mx-auto max-w-3xl">
                <Card>
                    <CardHeader className="flex-row items-center justify-between space-y-0 border-b">
                        <CardTitle className="flex items-center gap-2"><FileWarning className="size-5 text-primary" /> {report.title}</CardTitle>
                        <Badge variant="secondary">{report.status}</Badge>
                    </CardHeader>
                    <CardContent className="space-y-6 p-6">
                        <div className="grid gap-4 sm:grid-cols-3">
                            <div><p className="text-xs text-muted-foreground">Asset</p><p className="mt-1 text-sm font-medium">{report.asset ? `${report.asset.code} - ${report.asset.name}` : '-'}</p></div>
                            <div><p className="text-xs text-muted-foreground">Asset status</p><p className="mt-1 text-sm font-medium">{report.asset?.status?.replace('_', ' ') ?? '-'}</p></div>
                            <div><p className="text-xs text-muted-foreground">Submitted</p><p className="mt-1 text-sm font-medium">{report.created ?? '-'}</p></div>
                        </div>
                        <div><p className="text-xs text-muted-foreground">Description</p><p className="mt-1 whitespace-pre-wrap text-sm leading-6">{report.description}</p></div>
                        {report.image_url && <div><p className="mb-2 text-xs text-muted-foreground">Attached image</p><img src={report.image_url} alt="Issue attachment" className="max-h-[32rem] max-w-full rounded-lg border object-contain" /></div>}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}