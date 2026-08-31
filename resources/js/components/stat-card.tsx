import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';

import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

/** Semantic meaning of the figure - drives the accent colour. */
export type Tone = 'neutral' | 'info' | 'success' | 'warning' | 'danger';

/** Optional colour override, for rows of cards that read better as a palette. */
export type Accent = 'blue' | 'emerald' | 'amber' | 'rose' | 'purple' | 'cyan' | 'slate';

const accents: Record<Accent, { bar: string; icon: string }> = {
    blue: { bar: 'bg-blue-600', icon: 'text-blue-600' },
    emerald: { bar: 'bg-emerald-600', icon: 'text-emerald-600' },
    amber: { bar: 'bg-amber-500', icon: 'text-amber-600' },
    rose: { bar: 'bg-rose-600', icon: 'text-rose-600' },
    purple: { bar: 'bg-purple-600', icon: 'text-purple-600' },
    cyan: { bar: 'bg-cyan-600', icon: 'text-cyan-600' },
    slate: { bar: 'bg-slate-400 dark:bg-slate-600', icon: 'text-slate-500' },
};

const tones: Record<Tone, Accent> = {
    neutral: 'slate',
    info: 'blue',
    success: 'emerald',
    warning: 'amber',
    danger: 'rose',
};

interface Props {
    label: string;
    value: number | string;
    /** One line of context under the figure. */
    description?: string;
    /** Short trend or comparison line, e.g. "12 in use". */
    delta?: string | number;
    deltaType?: 'positive' | 'negative' | 'neutral';
    tone?: Tone;
    accent?: Accent;
    icon?: React.ComponentType<{ className?: string }>;
    /** Renders a "View details" link and makes the whole card a link. */
    href?: string | null;
    linkLabel?: string;
    /** Makes the card a button instead - used for click-to-filter rows. */
    onClick?: () => void;
    /** Highlights the card as the active choice in a filter row. */
    active?: boolean;
}

/**
 * The single card used for every headline figure in the app: dashboard
 * metrics, report summaries, and the click-to-filter rows above list pages.
 */
export default function StatCard({
    label,
    value,
    description,
    delta,
    deltaType = 'neutral',
    tone = 'neutral',
    accent,
    icon: Icon,
    href,
    linkLabel = 'View details',
    onClick,
    active = false,
}: Props) {
    const palette = accents[accent ?? tones[tone] ?? 'slate'];

    const deltaColour =
        deltaType === 'positive'
            ? 'text-emerald-600 dark:text-emerald-400'
            : deltaType === 'negative'
              ? 'text-rose-600 dark:text-rose-400'
              : 'text-muted-foreground';

    const card = (
        <Card
            className={cn(
                'group h-full overflow-hidden transition-all',
                (href || onClick) && 'hover:border-primary/50 hover:shadow-lg',
                active && 'border-primary ring-1 ring-primary',
            )}
        >
            <CardContent className="relative flex min-h-[140px] flex-col justify-between p-6 text-left">
                <span className={cn('absolute inset-y-0 left-0 w-1', palette.bar)} aria-hidden="true" />

                <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                        <p className="text-sm font-medium text-muted-foreground">{label}</p>
                        <p className="mt-2 text-3xl font-bold tracking-tight tabular-nums">{value}</p>

                        {delta !== undefined && (
                            <p className={cn('mt-1 text-xs font-medium', deltaColour)}>
                                {deltaType === 'positive' ? '↑' : deltaType === 'negative' ? '↓' : '→'} {delta}
                            </p>
                        )}
                    </div>

                    {Icon && (
                        <Icon
                            className={cn('size-10 shrink-0 opacity-60 transition-transform group-hover:scale-110', palette.icon)}
                        />
                    )}
                </div>

                {description && <p className="mt-3 text-sm leading-snug text-muted-foreground">{description}</p>}

                {href && (
                    <span className="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary transition-all group-hover:gap-2">
                        {linkLabel} <ArrowUpRight className="size-3" />
                    </span>
                )}
            </CardContent>
        </Card>
    );

    if (onClick) {
        return (
            <button
                type="button"
                onClick={onClick}
                aria-pressed={active}
                className="block w-full rounded-xl text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            >
                {card}
            </button>
        );
    }

    if (href) {
        return (
            <Link href={href} className="block rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                {card}
            </Link>
        );
    }

    return card;
}
