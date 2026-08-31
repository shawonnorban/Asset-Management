import { Download, FileSpreadsheet, FileText, Table2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
    /** Export endpoint without the format query string. */
    baseUrl: string;
    label?: string;
}

/**
 * PDF / Excel / CSV off one endpoint. Plain anchors, because a download must not
 * be intercepted by the Inertia router.
 */
export default function ExportMenu({ baseUrl, label = 'Export' }: Props) {
    const href = (format: string) => `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}format=${format}`;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="outline">
                    <Download className="size-4" /> {label}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-48">
                <DropdownMenuLabel>Download as</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <a href={href('pdf')}>
                        <FileText className="mr-2 size-4" /> PDF
                    </a>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href={href('xlsx')}>
                        <FileSpreadsheet className="mr-2 size-4" /> Excel (.xlsx)
                    </a>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href={href('csv')}>
                        <Table2 className="mr-2 size-4" /> CSV
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
