import { Link } from '@inertiajs/react';

interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PageLink[];
    from?: number | null;
    to?: number | null;
    total?: number;
}

export default function Pagination({ links, from, to, total }: Props) {
    if (!links?.length || !total) return null;

    return (
        <div className="mt-4 flex flex-col gap-3 border-t pt-4 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
            <span>Showing {from ?? 0}-{to ?? 0} of {total}</span>
            <div className="flex flex-wrap gap-1">
                {links.map((link, index) => link.url ? (
                    <Link key={`${link.label}-${index}`} href={link.url} preserveState preserveScroll className={`rounded border px-3 py-1 ${link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                ) : (
                    <span key={`${link.label}-${index}`} className="rounded border px-3 py-1 opacity-50" dangerouslySetInnerHTML={{ __html: link.label }} />
                ))}
            </div>
        </div>
    );
}
