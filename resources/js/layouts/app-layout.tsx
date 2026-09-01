import { PropsWithChildren, useEffect, useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Boxes,
    PackageOpen,
    Wrench,
    ChartLine,
    CheckCircle2,
    ChevronDown,
    Clock,
    CircleUser,
    Flame,
    IdCard,
    Key,
    LayoutList,
    Building2,
    LogOut,
    Bell,
    Menu,
    Moon,
    QrCode,
    Signal,
    SlidersHorizontal,
    Sun,
    Users,
    UserCheck,
    Shield,
    UserSquare,
    Network,
    FileDown,
    FilePlus2,
    FileQuestion,
    FileCheck2,
    FileWarning,
    ClipboardList,
    ClipboardCheck,
    UserCog,
    PanelLeftClose,
    PanelLeftOpen,
    XCircle,
    ArrowLeftRight,
    Trash2,
    ShieldCheck,
    ChartPie,
} from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { MenuBlock, PageProps } from '@/types';

/** The sheet stores FontAwesome class names; map them onto lucide icons. */
const ICONS: Record<string, typeof Flame> = {
    'fa fa-fire': Flame,
    'fa fa-users': Users,
    'fa fa-user-check': UserCheck,
    'fa fa-user-shield': Shield,
    'fa fa-signal': Signal,
    'fa fa-cubes': Boxes,
    'fa fa-boxes-stacked': PackageOpen,
    'fa fa-screwdriver-wrench': Wrench,
    'fa fa-list': LayoutList,
    'fa fa-building': Building2,
    'fa fa-id-card': IdCard,
    'fa fa-sitemap': Network,
    'fa fa-user-tag': UserSquare,
    'fa fa-key': Key,
    'fa fa-clipboard-list': ClipboardList,
    'fa fa-clipboard-check': ClipboardCheck,
    'fa fa-qrcode': QrCode,
    'fa fa-chart-line': ChartLine,
    'fa fa-sliders': SlidersHorizontal,
    'fa fa-file-arrow-down': FileDown,
    'fa fa-file-circle-plus': FilePlus2,
    'fa fa-file-circle-question': FileQuestion,
    'fa fa-file-circle-check': FileCheck2,
    'fa fa-file-circle-exclamation': FileWarning,
    'fa fa-user-gear': UserCog,
    'fa fa-bell': Bell,
    'fa fa-right-left': ArrowLeftRight,
    'fa fa-trash-can': Trash2,
    'fa fa-shield-halved': ShieldCheck,
    'fa fa-chart-pie': ChartPie,
};

/**
 * Topbar clock. Ticks once a second and is cleared on unmount so navigating
 * between pages never leaves a timer running.
 */
function DigitalClock() {
    const [now, setNow] = useState(() => new Date());

    useEffect(() => {
        const timer = window.setInterval(() => setNow(new Date()), 1000);

        return () => window.clearInterval(timer);
    }, []);

    const time = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    const date = now.toLocaleDateString([], { weekday: 'short', day: '2-digit', month: 'short' });

    return (
        <div
            className="hidden items-center gap-2 rounded-md border bg-muted/40 px-2.5 py-1.5 sm:flex"
            title={now.toLocaleString()}
        >
            <Clock className="size-3.5 shrink-0 text-muted-foreground" />
            <time dateTime={now.toISOString()} className="text-sm font-medium tabular-nums leading-none">
                {time}
            </time>
            <span className="hidden border-l pl-2 text-xs leading-none text-muted-foreground lg:inline">{date}</span>
        </div>
    );
}

/** Header bell with the unread count, linking into the notification centre. */
function NotificationBell() {
    const { notifications } = usePage<PageProps>().props;

    if (!notifications?.can_view) {
        return null;
    }

    const unread = notifications.unread_count ?? 0;

    return (
        <Button variant="ghost" size="icon" className="relative" asChild>
            <a href="/notifications" aria-label={unread ? `${unread} unread notifications` : 'Notifications'}>
                <Bell className="size-4" />
                {unread > 0 && (
                    <span className="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold leading-none text-white">
                        {unread > 99 ? '99+' : unread}
                    </span>
                )}
            </a>
        </Button>
    );
}

function useDarkMode() {
    const [dark, setDark] = useState(() => {
        try {
            return localStorage.getItem('theme') === 'dark';
        } catch {
            return false;
        }
    });

    useEffect(() => {
        document.documentElement.classList.toggle('dark', dark);

        try {
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        } catch {
            /* private mode - the toggle still works for this visit */
        }
    }, [dark]);

    return { dark, toggle: () => setDark((value) => !value) };
}

/** Read a persisted value, tolerating private mode and cleared site data. */
function readStored<T>(key: string, fallback: T): T {
    try {
        const raw = localStorage.getItem(key);

        return raw === null ? fallback : (JSON.parse(raw) as T);
    } catch {
        return fallback;
    }
}

function writeStored(key: string, value: unknown) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
    } catch {
        /* private mode - the choice simply does not survive this visit */
    }
}

/** Sidebar narrowed to an icon rail. */
function useSidebarRail() {
    const [collapsed, setCollapsed] = useState(() => readStored('sidebar-collapsed', false));

    useEffect(() => writeStored('sidebar-collapsed', collapsed), [collapsed]);

    return { collapsed, toggle: () => setCollapsed((value) => !value) };
}

/**
 * Which nav groups are folded away. Only the closed ones are stored, so a group
 * added to the menu later starts open rather than silently hidden.
 */
function useCollapsedGroups() {
    const [closed, setClosed] = useState<string[]>(() => readStored('sidebar-closed-groups', []));

    useEffect(() => writeStored('sidebar-closed-groups', closed), [closed]);

    return {
        isClosed: (header: string) => closed.includes(header),
        toggle: (header: string) =>
            setClosed((current) =>
                current.includes(header) ? current.filter((item) => item !== header) : [...current, header],
            ),
    };
}

interface NavGroupProps {
    block: MenuBlock;
    railed?: boolean;
    compact?: boolean;
    isClosed: (header: string) => boolean;
    toggle: (header: string) => void;
}

function NavGroup({ block, railed = false, compact = false, isClosed, toggle }: NavGroupProps) {
    const header = block.header ?? '';
    const hasActive = block.items.some((item) => item.active);
    const collapsible = block.collapsible !== false;

    // A folded group that holds the current page would hide where the user is,
    // so it always renders open.
    const open = railed || !header || !collapsible || hasActive || !isClosed(header);

    const links = (
        <ul className="space-y-1">
            {block.items.map((item) => {
                const Icon = ICONS[item.icon] ?? LayoutList;

                return (
                    <li key={item.label}>
                        {/* a plain anchor: most pages are still Blade,
                            so a full page load is the correct behaviour */}
                        <a
                            href={item.url ?? '#'}
                            title={railed ? item.label : undefined}
                            className={cn(
                                'flex items-center gap-3 rounded-md text-sm transition-colors',
                                railed ? 'justify-center px-2' : 'px-3',
                                compact && !railed && 'py-2',
                                item.active
                                    ? 'bg-primary font-medium text-primary-foreground shadow-sm'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                            )}
                            style={{ paddingTop: '0.4rem', paddingBottom: '0.4rem' }}
                        >
                            <Icon className="size-4 shrink-0" />
                            {!railed && <span className="truncate">{item.label}</span>}
                        </a>
                    </li>
                );
            })}
        </ul>
    );

    if (railed) {
        return <div className="mb-3 border-b pb-3 last:border-b-0">{links}</div>;
    }

    if (!header) {
        return <div className="mb-5">{links}</div>;
    }

    // Pinned open: a plain caption, with no control implying it can be folded.
    if (!collapsible) {
        return (
            <div className="mb-4">
                <p
                    className="mx-1 mb-2 rounded-md border border-indigo-200/70 bg-indigo-50/80 px-3 text-[11px] font-bold uppercase tracking-[0.2em] text-indigo-600 shadow-[inset_0_0_0_1px_rgba(79,70,229,0.06)]"
                    style={{ paddingTop: '0.4rem', paddingBottom: '0.4rem' }}
                >
                    {header}
                </p>
                <div className="mt-1">{links}</div>
            </div>
        );
    }

    return (
        <div className="mb-4">
            <button
                type="button"
                onClick={() => toggle(header)}
                aria-expanded={open}
                className="flex w-full items-center justify-between rounded-lg border border-indigo-200/60 bg-indigo-50/70 px-3 text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-600 shadow-[inset_0_0_0_1px_rgba(79,70,229,0.05)] transition-colors hover:bg-indigo-100/80 hover:text-indigo-700"
                style={{ paddingTop: '0.4rem', paddingBottom: '0.4rem' }}
            >
                <span className="truncate">{header}</span>
                <span className="flex items-center gap-1.5">
                    {!open && hasActive && <span className="size-1.5 rounded-full bg-primary" aria-hidden="true" />}
                    <ChevronDown className={cn('size-3.5 shrink-0 transition-transform', !open && '-rotate-90')} />
                </span>
            </button>

            {open && <div className="mt-1">{links}</div>}
        </div>
    );
}

function FlashMessages() {
    const { flash } = usePage<PageProps>().props;

    if (!flash?.success && !flash?.error) {
        return null;
    }

    return (
        <div className="mb-6 space-y-3">
            {flash.success && (
                <div className="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <CheckCircle2 className="mt-0.5 size-4 shrink-0" />
                    <span>{flash.success}</span>
                </div>
            )}
            {flash.error && (
                <div className="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                    <XCircle className="mt-0.5 size-4 shrink-0" />
                    <span>{flash.error}</span>
                </div>
            )}
        </div>
    );
}

interface Props {
    title: string;
    description?: string;
    actions?: React.ReactNode;
}

export default function AppLayout({
    title,
    description,
    actions,
    children,
}: PropsWithChildren<Props>) {
    const { auth, menu } = usePage<PageProps>().props;
    const { dark, toggle } = useDarkMode();
    const rail = useSidebarRail();
    const groups = useCollapsedGroups();

    return (
        <div className="min-h-screen bg-muted/30">
            <Head title={title} />

            {/* ---------- sidebar ---------- */}
            <aside
                className={cn(
                    'fixed inset-y-0 left-0 z-40 hidden flex-col border-r bg-background transition-[width] duration-200 lg:flex',
                    rail.collapsed ? 'w-16' : 'w-64',
                )}
            >
                <div className={cn('flex h-16 items-center border-b', rail.collapsed ? 'justify-center px-2' : 'px-5')}>
                    <a
                        href="/home"
                        title={rail.collapsed ? 'Asset Management' : undefined}
                        className="flex items-center gap-3 font-semibold tracking-tight"
                    >
                        <span className="flex size-8 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <Boxes className="size-4" />
                        </span>
                        {!rail.collapsed && <span>Asset Management</span>}
                    </a>
                </div>

                <nav className={cn('flex-1 overflow-y-auto py-4', rail.collapsed ? 'px-2' : 'px-3')}>
                    {menu?.map((block, blockIndex) => (
                        <NavGroup
                            key={block.header ?? blockIndex}
                            block={block}
                            railed={rail.collapsed}
                            isClosed={groups.isClosed}
                            toggle={groups.toggle}
                        />
                    ))}
                </nav>

                <div className={cn('border-t p-2', rail.collapsed ? 'flex justify-center' : '')}>
                    <Button
                        variant="ghost"
                        size={rail.collapsed ? 'icon' : 'sm'}
                        onClick={rail.toggle}
                        title={rail.collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                        aria-label={rail.collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                        className={cn(!rail.collapsed && 'w-full justify-start gap-3 text-muted-foreground')}
                    >
                        {rail.collapsed ? (
                            <PanelLeftOpen className="size-4" />
                        ) : (
                            <>
                                <PanelLeftClose className="size-4" /> Collapse
                            </>
                        )}
                    </Button>
                </div>
            </aside>

            {/* ---------- main ---------- */}
            <div className={cn('transition-[padding] duration-200', rail.collapsed ? 'lg:pl-16' : 'lg:pl-64')}>
                <header className="sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-background/90 px-4 backdrop-blur sm:px-8">
                    <details className="relative lg:hidden">
                        <summary className="flex size-10 cursor-pointer list-none items-center justify-center rounded-md hover:bg-accent" aria-label="Open navigation">
                            <Menu className="size-5" />
                        </summary>
                        <div className="absolute left-0 top-12 z-50 max-h-[calc(100vh-5rem)] w-72 overflow-y-auto rounded-md border bg-background p-3 shadow-lg">
                            {menu?.map((block, blockIndex) => (
                                <NavGroup
                                    key={block.header ?? blockIndex}
                                    block={block}
                                    compact
                                    isClosed={groups.isClosed}
                                    toggle={groups.toggle}
                                />
                            ))}
                        </div>
                    </details>
                    <div className="flex-1" />

                    <DigitalClock />

                    <NotificationBell />

                    <Button variant="ghost" size="icon" onClick={toggle} aria-label="Toggle theme">
                        {dark ? <Moon className="size-4" /> : <Sun className="size-4" />}
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="gap-2">
                                {auth?.user?.image_url ? (
                                    <img src={auth.user.image_url} alt="" className="size-6 rounded-full object-cover" />
                                ) : (
                                    <CircleUser className="size-4" />
                                )}
                                <span className="hidden sm:inline">{auth?.user?.name ?? 'Guest'}</span>
                                <ChevronDown className="size-3 opacity-60" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-56">
                            <DropdownMenuLabel>
                                <div className="flex items-center gap-3">
                                    {auth?.user?.image_url ? (
                                        <img src={auth.user.image_url} alt="" className="size-9 rounded-full object-cover" />
                                    ) : (
                                        <CircleUser className="size-5 text-muted-foreground" />
                                    )}
                                    <div>
                                        <div className="font-medium">{auth?.user?.name}</div>
                                        <div className="text-xs font-normal text-muted-foreground">
                                            {auth?.user?.email}
                                        </div>
                                    </div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link href="/profile">
                                    {auth?.user?.image_url ? (
                                        <img src={auth.user.image_url} alt="" className="mr-2 size-4 rounded-full object-cover" />
                                    ) : (
                                        <CircleUser className="mr-2 size-4" />
                                    )}
                                    My profile
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                onSelect={() =>
                                    (document.getElementById('logout-form') as HTMLFormElement | null)?.submit()
                                }
                            >
                                <LogOut className="mr-2 size-4" />
                                Logout
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <form id="logout-form" method="POST" action="/logout" className="hidden">
                        <input
                            type="hidden"
                            name="_token"
                            value={
                                document
                                    .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                                    ?.content ?? ''
                            }
                        />
                    </form>
                </header>

                <main className="mx-auto max-w-[1600px] p-4 sm:p-8">
                    <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
                            {description && (
                                <p className="mt-1 text-sm text-muted-foreground">{description}</p>
                            )}
                        </div>
                        {actions && <div className="flex flex-wrap gap-2">{actions}</div>}
                    </div>

                    <FlashMessages />

                    {children}
                </main>
            </div>
        </div>
    );
}
