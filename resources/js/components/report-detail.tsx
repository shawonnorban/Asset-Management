import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Check, MessageSquare, Play } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { TextareaField } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface Props {
    title: string;
    report: Record<string, unknown>;
    feedback?: Record<string, unknown> | null;
    feedbackReplies?: Record<string, unknown>[];
    mode: 'incoming' | 'review';
}

export default function ReportDetail({ title, report, feedback, feedbackReplies = [], mode }: Props) {
    const { data, setData, post, put, processing, errors } = useForm({
        decision_analysis: '',
        resolution: 'normal',
        feedback_replies: '',
    });
    const reportId = Number(report.id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (mode === 'review') {
            post(`/review-reports/detail/${reportId}`);
        } else {
            put(`/incoming-reports/detail/${reportId}/complete`);
        }
    };

    return <AppLayout title={title} actions={<Button variant="outline" asChild><Link href={mode === 'review' ? '/review-reports' : '/incoming-reports'}><ArrowLeft /> Back</Link></Button>}>
        <div className="grid gap-6 lg:grid-cols-[1fr_360px]">
            <Card><CardHeader><CardTitle>{String(report.title ?? 'Issue report')}</CardTitle></CardHeader><CardContent className="space-y-4">
                {(['status', 'asset_id', 'description', 'created_at'] as const).map((key) => <div key={key}><dt className="text-xs uppercase tracking-wide text-muted-foreground">{key.replace('_', ' ')}</dt><dd className="mt-1 text-sm">{String(report[key] ?? '-')}</dd></div>)}
                {report.asset && <div className="border-t pt-4"><p className="text-xs uppercase tracking-wide text-muted-foreground">Asset status</p><p className="mt-1 text-sm font-medium">{String((report.asset as { asset_name?: string; status?: string }).asset_name ?? '-')} · {String((report.asset as { status?: string }).status ?? '-')}</p></div>}
                {report.image && <div className="border-t pt-4"><p className="mb-2 text-xs uppercase tracking-wide text-muted-foreground">Attached image</p><img src={`/storage/${String(report.image)}`} alt="Issue attachment" className="max-h-80 max-w-full rounded-md border object-contain" /></div>}
                {feedback && <div className="border-t pt-4"><p className="mb-2 text-sm font-medium">Admin feedback</p><p className="text-sm text-muted-foreground">{String(feedback.decision_analysis ?? '-')}</p></div>}
                {feedbackReplies.length > 0 && <div className="space-y-2 border-t pt-4"><p className="text-sm font-medium">Replies</p>{feedbackReplies.map((reply, index) => <p className="rounded-md bg-muted p-3 text-sm" key={index}>{String(reply.feedback_reply ?? '-')}</p>)}</div>}
            </CardContent></Card>
            <Card><CardHeader><CardTitle>{mode === 'incoming' ? 'Review actions' : 'Reply to feedback'}</CardTitle></CardHeader><CardContent>
                <form onSubmit={submit} className="space-y-4">
                    {mode === 'incoming' ? <><TextareaField name="decision_analysis" label="Decision analysis" required value={data.decision_analysis} error={errors.decision_analysis} onChange={(value) => setData('decision_analysis', value)} /><div><label className="mb-2 block text-sm font-medium">Asset resolution</label><Select value={data.resolution} onValueChange={(value) => setData('resolution', value)}><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectItem value="normal">Normal / no maintenance</SelectItem><SelectItem value="maintenance">Send to maintenance</SelectItem></SelectContent></Select><p className="mt-1 text-xs text-destructive">{errors.resolution}</p></div><div className="flex gap-2"><Button type="button" variant="outline" onClick={() => post(`/incoming-reports/detail/${reportId}/review`)} disabled={processing}><Play /> Mark in review</Button><Button type="submit" disabled={processing}><Check /> {data.resolution === 'maintenance' ? 'Create maintenance' : 'Complete report'}</Button></div></> : <><TextareaField name="feedback_replies" label="Reply" required value={data.feedback_replies} error={errors.feedback_replies} onChange={(value) => setData('feedback_replies', value)} /><Button type="submit" disabled={processing}><MessageSquare /> Send reply</Button></>}
                </form>
            </CardContent></Card>
        </div>
    </AppLayout>;
}