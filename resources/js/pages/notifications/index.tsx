import AppLayout from '@/layouts/app-layout';
import { Link, router } from '@inertiajs/react';
import { ArrowRight, Bell, CheckCheck } from 'lucide-react';

import Pagination from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';

interface NotificationItem {
    id: number;
    type: string;
    type_label: string;
    title: string;
    message: string;
    is_read: boolean;
    link: string | null;
    sent_at: string | null;
}

interface Props {
    title: string;
    description: string;
    notifications: NotificationItem[];
    pagination: { links: { url: string | null; label: string; active: boolean }[]; from: number | null; to: number | null; total: number };
    types: Record<string, string>;
    filters: { type: string; status: string };
    unread_count: number;
}

/** Alert types that should read as urgent in the list. */
const urgentTypes = ['warranty_expired', 'maintenance_overdue', 'transfer_rejected', 'disposal_rejected'];

export default function NotificationsIndex({ title, description, notifications, pagination, types, filters, unread_count: unreadCount }: Props) {
    const applyFilters = (next: Partial<Props['filters']>) => {
        const merged = { ...filters, ...next };

        router.get(
            '/notifications',
            Object.fromEntries(Object.entries(merged).filter(([, value]) => value)),
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    return (
        <AppLayout
            title={title}
            description={description}
            actions={
                <div className="flex flex-wrap items-center gap-2">
                    <span className="inline-flex items-center gap-2 rounded-full border bg-muted px-3 py-1.5 text-sm">
                        <Bell className="size-4" /> {unreadCount} unread
                    </span>
                    {unreadCount > 0 && (
                        <Button variant="outline" onClick={() => router.post('/notifications/read-all', {}, { preserveScroll: true })}>
                            <CheckCheck className="size-4" /> Mark all read
                        </Button>
                    )}
                </div>
            }
        >
            <div className="space-y-6">
                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <CardTitle className="text-base">Notification centre</CardTitle>
                                <CardDescription>{pagination.total} notifications in total.</CardDescription>
                            </div>

                            <div className="flex flex-wrap items-center gap-2">
                                <select
                                    aria-label="Filter by read state"
                                    value={filters.status}
                                    onChange={(event) => applyFilters({ status: event.target.value })}
                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">All notifications</option>
                                    <option value="unread">Unread only</option>
                                    <option value="read">Read only</option>
                                </select>

                                <select
                                    aria-label="Filter by type"
                                    value={filters.type}
                                    onChange={(event) => applyFilters({ type: event.target.value })}
                                    className="h-9 rounded-md border border-input bg-background px-3 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="">All types</option>
                                    {Object.entries(types).map(([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {notifications.length === 0 ? (
                            <p className="py-10 text-center text-sm text-muted-foreground">
                                {filters.type || filters.status ? 'No notifications match these filters.' : 'No notifications yet.'}
                            </p>
                        ) : (
                            <div className="space-y-3">
                                {notifications.map((notification) => (
                                    <div
                                        key={notification.id}
                                        className={cn(
                                            'rounded-lg border p-4 transition-colors',
                                            notification.is_read
                                                ? 'bg-background'
                                                : urgentTypes.includes(notification.type)
                                                  ? 'border-rose-200 bg-rose-50/60 dark:border-rose-900/50 dark:bg-rose-950/30'
                                                  : 'border-blue-200 bg-blue-50/60 dark:border-blue-900/50 dark:bg-blue-950/30',
                                        )}
                                    >
                                        <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div className="min-w-0">
                                                <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">{notification.type_label}</p>
                                                <h3 className="mt-1 font-semibold">{notification.title}</h3>
                                                <p className="mt-1 text-sm text-muted-foreground">{notification.message}</p>
                                                <p className="mt-2 text-xs text-muted-foreground">{notification.sent_at ?? '-'}</p>
                                            </div>

                                            <div className="flex shrink-0 flex-wrap gap-2">
                                                {notification.link && (
                                                    <Button variant="outline" size="sm" asChild>
                                                        <Link href={notification.link}>
                                                            Open <ArrowRight className="size-4" />
                                                        </Link>
                                                    </Button>
                                                )}
                                                {!notification.is_read && (
                                                    <Button
                                                        type="button"
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true })}
                                                    >
                                                        <CheckCheck className="size-4" /> Mark read
                                                    </Button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        <Pagination links={pagination.links} from={pagination.from} to={pagination.to} total={pagination.total} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
