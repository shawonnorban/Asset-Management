import { PropsWithChildren, useEffect, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import {
    Boxes,
    ChartLine,
    CheckCircle2,
    ChevronDown,
    CircleUser,
    Flame,
    IdCard,
    Key,
    LayoutList,
    Building2,
    LogOut,
    Moon,
    QrCode,
    Signal,
    SlidersHorizontal,
    Sun,
    Users,
    UserCheck,
    UserSquare,
    Network,
    FileDown,
    FilePlus2,
    FileQuestion,
    FileCheck2,
    XCircle,
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
import type { PageProps } from '@/types';

/** The sheet stores FontAwesome class names; map them onto lucide icons. */
const ICONS: Record<string, typeof Flame> = {
    'fa fa-fire': Flame,
    'fa fa-users': Users,
    'fa fa-user-check': UserCheck,
    'fa fa-signal': Signal,
    'fa fa-cubes': Boxes,
    'fa fa-list': LayoutList,
    'fa fa-building': Building2,
    'fa fa-id-card': IdCard,
    'fa fa-sitemap': Network,
    'fa fa-user-tag': UserSquare,
    'fa fa-key': Key,
    'fa fa-clipboard-list': LayoutList,
    'fa fa-qrcode': QrCode,
    'fa fa-chart-line': ChartLine,
    'fa fa-sliders': SlidersHorizontal,
    'fa fa-file-arrow-down': FileDown,
    'fa fa-file-circle-plus': FilePlus2,
    'fa fa-file-circle-question': FileQuestion,
    'fa fa-file-circle-check': FileCheck2,
};

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

    return (
        <div className="min-h-screen bg-muted/30">
            <Head title={title} />

            {/* ---------- sidebar ---------- */}
            <aside className="fixed inset-y-0 left-0 hidden w-64 flex-col border-r bg-background lg:flex">
                <div className="flex h-16 items-center border-b px-6">
                    <a href="/home" className="flex items-center gap-2 font-semibold">
                        <Boxes className="size-5" />
                        <span>Assets</span>
                    </a>
                </div>

                <nav className="flex-1 overflow-y-auto px-3 py-4">
                    {menu?.map((block, blockIndex) => (
                        <div key={blockIndex} className="mb-5">
                            {block.header && (
                                <p className="px-3 pb-2 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                    {block.header}
                                </p>
                            )}

                            <ul className="space-y-1">
                                {block.items.map((item) => {
                                    const Icon = ICONS[item.icon] ?? LayoutList;

                                    return (
                                        <li key={item.label}>
                                            {/* a plain anchor: most pages are still Blade,
                                                so a full page load is the correct behaviour */}
                                            <a
                                                href={item.url ?? '#'}
                                                className={cn(
                                                    'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
                                                    item.active
                                                        ? 'bg-primary text-primary-foreground'
                                                        : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                                )}
                                            >
                                                <Icon className="size-4 shrink-0" />
                                                <span className="truncate">{item.label}</span>
                                            </a>
                                        </li>
                                    );
                                })}
                            </ul>
                        </div>
                    ))}
                </nav>
            </aside>

            {/* ---------- main ---------- */}
            <div className="lg:pl-64">
                <header className="sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-background/95 px-4 backdrop-blur sm:px-6">
                    <div className="flex-1" />

                    <Button variant="ghost" size="icon" onClick={toggle} aria-label="Toggle theme">
                        {dark ? <Moon className="size-4" /> : <Sun className="size-4" />}
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" className="gap-2">
                                <CircleUser className="size-4" />
                                <span className="hidden sm:inline">{auth?.user?.name ?? 'Guest'}</span>
                                <ChevronDown className="size-3 opacity-60" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-56">
                            <DropdownMenuLabel>
                                <div className="font-medium">{auth?.user?.name}</div>
                                <div className="text-xs font-normal text-muted-foreground">
                                    {auth?.user?.email}
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
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

                <main className="p-4 sm:p-6">
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
