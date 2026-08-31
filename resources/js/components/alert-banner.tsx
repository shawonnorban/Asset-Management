import { AlertTriangle, CheckCircle2, Info, ShieldAlert } from 'lucide-react';

import { cn } from '@/lib/utils';

export type AlertTone = 'info' | 'success' | 'warning' | 'danger';

const toneStyles: Record<AlertTone, { box: string; icon: typeof Info }> = {
    info: {
        box: 'border-blue-200 bg-blue-50 text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200',
        icon: Info,
    },
    success: {
        box: 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200',
        icon: CheckCircle2,
    },
    warning: {
        box: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-200',
        icon: AlertTriangle,
    },
    danger: {
        box: 'border-rose-200 bg-rose-50 text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-200',
        icon: ShieldAlert,
    },
};

interface Props {
    tone?: AlertTone;
    title: string;
    children?: React.ReactNode;
    action?: React.ReactNode;
    className?: string;
}

/** Standing alert strip used above lists that carry risk (expiry, overdue work). */
export default function AlertBanner({ tone = 'info', title, children, action, className }: Props) {
    const styles = toneStyles[tone] ?? toneStyles.info;
    const Icon = styles.icon;

    return (
        <div className={cn('flex flex-col gap-3 rounded-lg border px-4 py-3 sm:flex-row sm:items-center sm:justify-between', styles.box, className)}>
            <div className="flex items-start gap-3">
                <Icon className="mt-0.5 size-4 shrink-0" />
                <div className="text-sm">
                    <p className="font-semibold">{title}</p>
                    {children && <div className="mt-0.5 opacity-90">{children}</div>}
                </div>
            </div>
            {action && <div className="shrink-0">{action}</div>}
        </div>
    );
}
