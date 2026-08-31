import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Camera, Save, UserRound } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';

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

export default function ProfileEdit({ title, record }: Props) {
    const { data, setData, post, processing, errors } = useForm<any>({
        name: record.name,
        email: record.email,
        image: null,
        current_password: '',
        password: '',
        password_confirmation: '',
        _method: 'PATCH',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post('/profile', { forceFormData: true });
    };

    return (
        <AppLayout
            title={title}
            actions={
                <Button variant="outline" asChild>
                    <Link href="/profile">
                        <ArrowLeft className="size-4" />
                        Back
                    </Link>
                </Button>
            }
        >
            <div className="mx-auto max-w-3xl">
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-3">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <UserRound className="size-5" />
                            </span>
                            My profile
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="flex items-center gap-4">
                                {record.image_url ? (
                                    <img src={record.image_url} alt="Profile" className="size-40 rounded-full object-cover" />
                                ) : (
                                    <span className="flex size-40 items-center justify-center rounded-full bg-muted text-muted-foreground">
                                        <UserRound className="size-7" />
                                    </span>
                                )}
                                <label className="space-y-2 text-sm font-medium">
                                    <span className="flex items-center gap-2"><Camera className="size-4" /> Profile image</span>
                                    <Input type="file" accept="image/png,image/jpeg" onChange={(event) => setData('image', event.target.files?.[0] ?? null)} />
                                    <span className="text-xs text-destructive">{errors.image}</span>
                                </label>
                            </div>

                            <div className="grid gap-5 sm:grid-cols-2">
                                <label className="space-y-2 text-sm font-medium">
                                    <span>Name</span>
                                    <Input value={data.name} onChange={(event) => setData('name', event.target.value)} required />
                                    <span className="text-xs text-destructive">{errors.name}</span>
                                </label>
                                <label className="space-y-2 text-sm font-medium">
                                    <span>Email</span>
                                    <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} required />
                                    <span className="text-xs text-destructive">{errors.email}</span>
                                </label>
                            </div>

                            <div className="rounded-lg border p-4">
                                <p className="mb-4 text-sm font-medium">Change password</p>
                                <div className="grid gap-5 sm:grid-cols-2">
                                    <label className="space-y-2 text-sm font-medium sm:col-span-2">
                                        <span>Current password</span>
                                        <Input type="password" value={data.current_password} onChange={(event) => setData('current_password', event.target.value)} />
                                        <span className="text-xs text-destructive">{errors.current_password}</span>
                                    </label>
                                    <label className="space-y-2 text-sm font-medium">
                                        <span>New password</span>
                                        <Input type="password" value={data.password} onChange={(event) => setData('password', event.target.value)} />
                                        <span className="text-xs text-destructive">{errors.password}</span>
                                    </label>
                                    <label className="space-y-2 text-sm font-medium">
                                        <span>Confirm new password</span>
                                        <Input type="password" value={data.password_confirmation} onChange={(event) => setData('password_confirmation', event.target.value)} />
                                    </label>
                                </div>
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    <Save className="size-4" />
                                    {processing ? 'Saving...' : 'Save profile'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
