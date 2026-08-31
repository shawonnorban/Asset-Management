import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { TextField } from '@/components/field';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

interface Props {
    suggested_period: string;
}

export default function AssignmentAuditsCreate({ suggested_period }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        audit_name: '',
        audit_period: suggested_period,
    });

    return (
        <AppLayout
            title="New Assignment Audit"
            description="Create an audit cycle to verify each active asset assignment."
            actions={
                <Button variant="outline" asChild>
                    <Link href="/assignment-audits">
                        <ArrowLeft /> Back
                    </Link>
                </Button>
            }
        >
            <Card>
                <CardContent className="space-y-4 pt-6">
                    <form onSubmit={(event) => {
                        event.preventDefault();
                        post('/assignment-audits');
                    }} className="space-y-4 max-w-xl">
                        <TextField
                            name="audit_name"
                            label="Audit name"
                            required
                            value={data.audit_name}
                            error={errors.audit_name}
                            onChange={(value) => setData('audit_name', value)}
                            placeholder="Q3 asset assignment verification"
                        />

                        <TextField
                            name="audit_period"
                            label="Audit month"
                            type="date"
                            required
                            value={data.audit_period}
                            error={errors.audit_period}
                            onChange={(value) => setData('audit_period', value)}
                        />

                        <Button type="submit" disabled={processing}>
                            <Save /> {processing ? 'Creating...' : 'Create audit'}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </AppLayout>
    );
}
