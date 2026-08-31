import { Link } from '@inertiajs/react';
import { ArrowLeft, Mail, Pencil, ShieldCheck, UserRound } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface ProfileRecord {
    name: string;
    email: string;
    role: string;
    image_url: string | null;
}

interface Props {
    title: string;
    record: ProfileRecord;
}

export default function ProfileShow({ title, record }: Props) {
    return (
        <AppLayout
            title={title}
            actions={
                <Button variant="outline" asChild>
                    <Link href="/home">
                        <ArrowLeft className="size-4" />
                        Back
                    </Link>
                </Button>
            }
        >
            <div className="mx-auto max-w-2xl">
                <Card>
                    <CardHeader className="flex-row items-center justify-between space-y-0 border-b">
                        <CardTitle className="flex items-center gap-3">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <UserRound className="size-5" />
                            </span>
                            My profile
                        </CardTitle>
                        <Button asChild>
                            <Link href="/profile/edit">
                                <Pencil className="size-4" />
                                Edit profile
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent className="space-y-8 p-6">
                        <div className="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                            {record.image_url ? (
                                <img src={record.image_url} alt="Profile" className="size-40 rounded-full object-cover ring-4 ring-primary/10" />
                            ) : (
                                <span className="flex size-40 items-center justify-center rounded-full bg-muted text-muted-foreground ring-4 ring-primary/10">
                                    <UserRound className="size-10" />
                                </span>
                            )}
                            <div className="text-center sm:pt-2 sm:text-left">
                                <h2 className="text-xl font-semibold">{record.name}</h2>
                                <p className="mt-1 text-sm text-muted-foreground">Account details</p>
                            </div>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-lg border p-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <Mail className="size-4 text-primary" /> Email
                                </div>
                                <p className="break-all text-sm text-muted-foreground">{record.email}</p>
                            </div>
                            <div className="rounded-lg border p-4">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <ShieldCheck className="size-4 text-primary" /> Role
                                </div>
                                <p className="text-sm text-muted-foreground">{record.role}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
