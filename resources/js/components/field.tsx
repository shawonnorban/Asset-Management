import { ReactNode } from 'react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export const NONE = '__none__';

interface BaseProps {
    label: string;
    error?: string;
    required?: boolean;
    hint?: string;
    children?: ReactNode;
    htmlFor?: string;
}

/** Label + control + error, the shape every form on the site uses. */
export function Field({ label, error, required, hint, children, htmlFor }: BaseProps) {
    return (
        <div>
            <Label htmlFor={htmlFor} className="mb-2 block">
                {label} {required && <span className="text-destructive">*</span>}
            </Label>
            {children}
            {error && <p className="mt-1 text-xs text-destructive">{error}</p>}
            {hint && !error && <p className="mt-1 text-xs text-muted-foreground">{hint}</p>}
        </div>
    );
}

interface TextProps extends Omit<BaseProps, 'children'> {
    name: string;
    value: string;
    onChange: (value: string) => void;
    type?: 'text' | 'number' | 'date' | 'email' | 'password';
    placeholder?: string;
    step?: string;
}

export function TextField({ name, value, onChange, type = 'text', placeholder, step, ...rest }: TextProps) {
    return (
        <Field {...rest} htmlFor={name}>
            <Input
                id={name}
                type={type}
                step={step}
                value={value ?? ''}
                placeholder={placeholder}
                onChange={(e) => onChange(e.target.value)}
                aria-invalid={Boolean(rest.error)}
            />
        </Field>
    );
}

interface TextareaProps extends Omit<BaseProps, 'children'> {
    name: string;
    value: string;
    onChange: (value: string) => void;
    rows?: number;
}

export function TextareaField({ name, value, onChange, rows = 3, ...rest }: TextareaProps) {
    return (
        <Field {...rest} htmlFor={name}>
            <textarea
                id={name}
                rows={rows}
                className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
            />
        </Field>
    );
}

export interface Choice {
    value: string;
    label: string;
}

interface SelectProps extends Omit<BaseProps, 'children'> {
    name: string;
    value: string;
    onChange: (value: string) => void;
    choices: Choice[];
    placeholder?: string;
    /** label for the empty choice; omit to make the select mandatory */
    emptyLabel?: string;
}

export function SelectField({
    name,
    value,
    onChange,
    choices,
    placeholder,
    emptyLabel,
    ...rest
}: SelectProps) {
    return (
        <Field {...rest} htmlFor={name}>
            <Select value={value || NONE} onValueChange={(v) => onChange(v === NONE ? '' : v)}>
                <SelectTrigger id={name} aria-invalid={Boolean(rest.error)}>
                    <SelectValue placeholder={placeholder ?? 'Select'} />
                </SelectTrigger>
                <SelectContent>
                    {emptyLabel !== undefined && <SelectItem value={NONE}>{emptyLabel}</SelectItem>}
                    {choices.map((choice) => (
                        <SelectItem key={choice.value} value={choice.value}>
                            {choice.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </Field>
    );
}

/** Turns { KEY: 'Label' } into the choice list the selects want. */
export const choicesFrom = (map: Record<string, string>): Choice[] =>
    Object.entries(map).map(([value, label]) => ({ value, label }));

export const choicesFromList = (rows: { id: number; label: string }[]): Choice[] =>
    rows.map((row) => ({ value: String(row.id), label: row.label }));
