import { usePage } from '@inertiajs/react';

import type { PageProps } from '@/types';

/**
 * Permission check for the current user.
 *
 * A super admin holds the single "*" entry rather than every permission name,
 * so a plain `permissions.includes('assets.manage')` reports false and hides
 * the whole UI from the one account that can do everything. Always go through
 * this helper instead of matching permission strings by hand.
 *
 *   const can = useCan();
 *   can('assets.create', 'assets.manage')   // true if the user holds either
 */
export function useCan() {
    const { auth } = usePage<PageProps>().props;
    const permissions = auth?.user?.permissions ?? [];
    const isSuperAdmin = permissions.includes('*');

    return (...names: string[]) => isSuperAdmin || names.some((name) => permissions.includes(name));
}
