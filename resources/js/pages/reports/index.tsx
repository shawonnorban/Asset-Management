import AppLayout from '@/layouts/app-layout';
import { Link } from '@inertiajs/react';
import { ArrowRight, Boxes, PackageMinus, ShieldAlert, Wrench } from 'lucide-react';

import AlertBanner from '@/components/alert-banner';
import StatCard, { type Tone } from '@/components/stat-card';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { PageProps } from '@/types';

interface SummaryItem {
    label: string;
    value: number;
    description: string;
    tone?: Tone;
    href?: string;
}

interface ReportLink {
    label: string;
    href: string;
    description: string;
}

interface Metrics {
    active_assets: number;
    assigned_assets: number;
    under_maintenance: number;
    warranty_alerts: number;
    overdue_transfers: number;
    disposed_assets: number;
    value_recovered: number;
    total_assets: number;
}

interface Props extends PageProps {
    title: string;
    description: string;
    summary: SummaryItem[];
    metrics: Metrics;
    totalAssets: number;
    maintenanceOpen: number;
    warrantyRisk: number;
    reportLinks: ReportLink[];
}

const linkIcons = [Wrench, ShieldAlert, PackageMinus];

export default function ReportsIndex({ title, description, summary, metrics, totalAssets, reportLinks }: Props) {
    const utilisation = metrics.active_assets > 0
        ? Math.round((metrics.assigned_assets / metrics.active_assets) * 100)
        : 0;

    return (
        <AppLayout title={title} description={description}>
            <div className="space-y-6">
                {metrics.warranty_alerts > 0 && (
                    <AlertBanner
                        tone="danger"
                        title={`${metrics.warranty_alerts} warranty ${metrics.warranty_alerts === 1 ? 'alert needs' : 'alerts need'} attention`}
                        action={
                            <Link href="/reports/warranty" className="inline-flex items-center gap-1 text-sm font-semibold underline underline-offset-4">
                                Open warranty report <ArrowRight className="size-3.5" />
                            </Link>
                        }
                    >
                        Cover has lapsed or expires within 30 days on these assets.
                    </AlertBanner>
                )}

                {metrics.overdue_transfers > 0 && (
                    <AlertBanner
                        tone="warning"
                        title={`${metrics.overdue_transfers} transfer ${metrics.overdue_transfers === 1 ? 'request has' : 'requests have'} been pending over a week`}
                        action={
                            <Link href="/reports/movement" className="inline-flex items-center gap-1 text-sm font-semibold underline underline-offset-4">
                                Review movements <ArrowRight className="size-3.5" />
                            </Link>
                        }
                    >
                        Assets are recorded in one place while they sit somewhere else.
                    </AlertBanner>
                )}

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {summary.map((item) => (
                        <StatCard
                            key={item.label}
                            label={item.label}
                            value={item.value}
                            description={item.description}
                            tone={item.tone}
                            href={item.href}
                        />
                    ))}
                </div>

                <div className="grid gap-4 lg:grid-cols-3">
                    <Card className="lg:col-span-1">
                        <CardHeader>
                            <CardTitle className="text-base">Estate at a glance</CardTitle>
                            <CardDescription>Every asset on the books, active or not.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex items-baseline gap-2">
                                <Boxes className="size-5 text-muted-foreground" />
                                <span className="text-3xl font-semibold tabular-nums">{totalAssets}</span>
                                <span className="text-sm text-muted-foreground">total assets</span>
                            </div>

                            <div>
                                <div className="mb-1.5 flex items-center justify-between text-sm">
                                    <span className="text-muted-foreground">Assigned to staff</span>
                                    <span className="font-medium tabular-nums">{utilisation}%</span>
                                </div>
                                <div className="h-2 overflow-hidden rounded-full bg-muted">
                                    <div className="h-full rounded-full bg-primary transition-all" style={{ width: `${Math.min(utilisation, 100)}%` }} />
                                </div>
                                <p className="mt-1.5 text-xs text-muted-foreground">
                                    {metrics.assigned_assets} of {metrics.active_assets} active assets are in use
                                </p>
                            </div>

                            <dl className="grid grid-cols-2 gap-3 border-t pt-4 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">Disposed</dt>
                                    <dd className="mt-0.5 font-semibold tabular-nums">{metrics.disposed_assets}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Value recovered</dt>
                                    <dd className="mt-0.5 font-semibold tabular-nums">{metrics.value_recovered.toLocaleString()}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="text-base">Detailed reports</CardTitle>
                            <CardDescription>Drill into any area and export it as PDF, Excel, or CSV.</CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-3">
                            {reportLinks.map((link, index) => {
                                const Icon = linkIcons[index] ?? Wrench;

                                return (
                                    <Link
                                        key={link.href}
                                        href={link.href}
                                        className="group flex h-full flex-col rounded-lg border p-4 transition-colors hover:border-primary/40 hover:bg-accent"
                                    >
                                        <Icon className="size-5 text-muted-foreground" />
                                        <p className="mt-3 font-semibold">{link.label}</p>
                                        <p className="mt-1 flex-1 text-sm text-muted-foreground">{link.description}</p>
                                        <span className="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary">
                                            Open <ArrowRight className="size-3.5 transition-transform group-hover:translate-x-0.5" />
                                        </span>
                                    </Link>
                                );
                            })}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
