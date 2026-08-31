import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

interface Props { title: string; data: Record<string, unknown>; back: string; }

export default function ResourceDetail({ title, data, back }: Props) {
    return <AppLayout title={title} actions={<Button variant="outline" asChild><Link href={back}><ArrowLeft /> Back</Link></Button>}>
        <Card><CardContent className="grid gap-4 p-6 sm:grid-cols-2">
            {Object.entries(data).filter(([key]) => key !== 'id').map(([key, value]) => <div key={key} className="border-b pb-3">
                <dt className="text-xs uppercase tracking-wide text-muted-foreground">{key.replace(/_/g, ' ')}</dt>
                <dd className="mt-1 text-sm">{value === null || value === undefined ? '-' : typeof value === 'object' ? JSON.stringify(value) : String(value)}</dd>
            </div>)}
        </CardContent></Card>
    </AppLayout>;
}
