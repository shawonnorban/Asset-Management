import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

/**
 * One palette for every lifecycle status across maintenance, warranty, transfer,
 * and disposal, so the same word never means two colours in two places.
 */
const statusStyles: Record<string, string> = {
    // shared
    DRAFT: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    OPEN: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
    REQUESTED: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
    IN_PROGRESS: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    IN_TRANSIT: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    APPROVED: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    COMPLETED: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    CANCELLED: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    REJECTED: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
    // warranty
    ACTIVE: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    EXPIRING_SOON: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    EXPIRED: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
    CLAIMED: 'bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-300',
    VOID: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    // disposal
    DISPOSED: 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
    // priority
    LOW: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    MEDIUM: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300',
    HIGH: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    CRITICAL: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
};

interface Props {
    status: string | null | undefined;
    label?: string | null;
    className?: string;
}

export default function StatusBadge({ status, label, className }: Props) {
    if (!status) {
        return <span className="text-sm text-muted-foreground">-</span>;
    }

    const text = label ?? status.replace(/_/g, ' ');

    return (
        <Badge variant="secondary" className={cn('border-transparent font-medium', statusStyles[status] ?? '', className)}>
            {text}
        </Badge>
    );
}
