import { FormEvent, useEffect, useState } from 'react';
import { Html5QrcodeScanner } from 'html5-qrcode';
import { Link, useForm } from '@inertiajs/react';
import { AlertCircle, CheckCircle2, FileWarning, ImagePlus, LoaderCircle, ScanLine } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import { TextareaField, TextField } from '@/components/field';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface AssetPreview {
	id: number;
	asset_name: string;
	category: string | null;
	brand: string | null;
	location: string | null;
}

interface Props {
	selectedAsset: (AssetPreview & { asset_code: string }) | null;
}

type LookupState = 'idle' | 'checking' | 'found' | 'missing' | 'error';

export default function ReportIssue({ selectedAsset }: Props) {
	const { data, setData, post, processing, errors } = useForm({
		title: '',
		description: '',
		image: null as File | null,
		asset_id: selectedAsset ? String(selectedAsset.id) : '',
		qr_code: selectedAsset?.asset_code ?? '',
	});
	const [asset, setAsset] = useState<(AssetPreview & { asset_code?: string }) | null>(selectedAsset);
	const [suggestions, setSuggestions] = useState<(AssetPreview & { asset_code: string })[]>([]);
	const [lookupState, setLookupState] = useState<LookupState>(selectedAsset ? 'found' : 'idle');

	useEffect(() => {
		const scanner = new Html5QrcodeScanner(
			'report-issue-reader',
			{ fps: 8, qrbox: { width: 220, height: 220 } },
			false,
		);

		scanner.render(
			(decodedText) => setData('qr_code', decodedText),
			() => undefined,
		);

		return () => {
			scanner.clear().catch(() => undefined);
		};
	}, []);

	useEffect(() => {
		const code = data.qr_code.trim();
		const controller = new AbortController();
		if (!code) {
			setAsset(null);
			setSuggestions([]);
			setLookupState('idle');
			return () => controller.abort();
		}

		setLookupState('checking');
		const timer = window.setTimeout(() => {
			fetch(`/search-assets?q=${encodeURIComponent(code)}`, { credentials: 'same-origin', signal: controller.signal })
				.then((response) => {
					if (!response.ok) throw new Error('Lookup failed');
					return response.json() as Promise<(AssetPreview & { asset_code: string })[]>;
				})
				.then((results) => {
					setSuggestions(results);
					if (results.length === 0) {
						setAsset(null);
						setData('asset_id', '');
						setLookupState('missing');
						return;
					}

					if (results.length === 1 && results[0].asset_code.toLowerCase() === code.toLowerCase()) {
						selectAsset(results[0]);
					} else {
						setLookupState('idle');
					}
				})
				.catch(() => {
					if (controller.signal.aborted) return;
					setSuggestions([]);
					setAsset(null);
					setLookupState('error');
				});
		}, 350);

		return () => {
			window.clearTimeout(timer);
			controller.abort();
		};
	}, [data.qr_code]);

	const selectAsset = (selected: AssetPreview & { asset_code: string }) => {
		setAsset(selected);
		setSuggestions([]);
		setData('qr_code', selected.asset_code);
		setData('asset_id', String(selected.id));
		setLookupState('found');
	};

	const submit = (event: FormEvent) => {
		event.preventDefault();
		post('/report-issue', { forceFormData: true });
	};

	return (
		<AppLayout title="Report an Issue" description="Send a clear issue report linked to an inventory asset." actions={<Button variant="outline" asChild><Link href="/my-reports">My reports</Link></Button>}>
			<div className="grid gap-6 xl:grid-cols-[340px_minmax(0,1fr)]">
				<Card className="h-fit">
					<CardHeader>
						<CardTitle className="flex items-center gap-2"><ScanLine className="size-5 text-primary" /> Find an asset</CardTitle>
						<CardDescription>Scan the QR code or enter its asset code manually.</CardDescription>
					</CardHeader>
					<CardContent className="space-y-4">
						<div id="report-issue-reader" className="overflow-hidden rounded-md border bg-muted/30 p-2" />
						<div className="relative">
							<TextField name="qr_code" label="Asset QR / code" value={data.qr_code} onChange={(value) => { setData('qr_code', value); setAsset(null); setSuggestions([]); setData('asset_id', ''); }} placeholder="Search PC-0033 or Victus" />
							{suggestions.length > 0 && <div className="absolute inset-x-0 top-[4.5rem] z-20 overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-lg" role="listbox">
								{suggestions.map((suggestion) => <button key={suggestion.id} type="button" role="option" className="flex w-full items-start justify-between gap-3 rounded-sm px-3 py-2 text-left text-sm hover:bg-accent" onClick={() => selectAsset(suggestion)}><span><span className="block font-medium">{suggestion.asset_code} · {suggestion.asset_name}</span><span className="block text-xs text-muted-foreground">{suggestion.category ?? 'Uncategorized'} · {suggestion.location ?? 'No location'}</span></span><ScanLine className="mt-1 size-4 shrink-0 text-muted-foreground" /></button>)}
							</div>}
						</div>
						{lookupState === 'checking' && <p className="flex items-center gap-2 text-xs text-muted-foreground"><LoaderCircle className="size-3 animate-spin" /> Checking asset...</p>}
						{lookupState === 'missing' && <p className="flex items-center gap-2 text-xs text-destructive"><AlertCircle className="size-3" /> Asset not found. Check the code and try again.</p>}
						{lookupState === 'error' && <p className="flex items-center gap-2 text-xs text-destructive"><AlertCircle className="size-3" /> Could not check this asset right now.</p>}
						{asset && <div className="rounded-md border bg-accent/40 p-4"><div className="mb-3 flex items-center justify-between gap-2"><p className="font-medium">{asset.asset_name}</p><Badge variant="outline">Selected</Badge></div><dl className="space-y-2 text-sm"><div className="flex justify-between gap-3"><dt className="text-muted-foreground">Category</dt><dd>{asset.category ?? '-'}</dd></div><div className="flex justify-between gap-3"><dt className="text-muted-foreground">Brand</dt><dd>{asset.brand ?? '-'}</dd></div><div className="flex justify-between gap-3"><dt className="text-muted-foreground">Location</dt><dd>{asset.location ?? '-'}</dd></div></dl></div>}
					</CardContent>
				</Card>

				<Card>
					<CardHeader>
						<CardTitle className="flex items-center gap-2"><FileWarning className="size-5 text-primary" /> Issue details</CardTitle>
						<CardDescription>Describe what needs attention so the IT team can act quickly.</CardDescription>
					</CardHeader>
					<CardContent>
						<form onSubmit={submit} className="space-y-5">
							<TextField name="title" label="Report title" required value={data.title} error={errors.title} onChange={(value) => setData('title', value)} placeholder="Laptop screen is flickering" />
							<TextareaField name="description" label="Description" required rows={7} value={data.description} error={errors.description} onChange={(value) => setData('description', value)} placeholder="Tell us what happened, when it started, and any useful details..." />
							<label className="block space-y-2 text-sm font-medium"><span className="flex items-center gap-2"><ImagePlus className="size-4" /> Problem image</span><input type="file" accept="image/*" onChange={(event) => setData('image', event.target.files?.[0] ?? null)} className="block w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-primary file:px-3 file:py-1 file:text-primary-foreground" /><span className="text-xs text-muted-foreground">Optional, maximum 10MB.</span><span className="text-xs text-destructive">{errors.image}</span></label>
							<input type="hidden" value={data.asset_id} readOnly />
							{errors.asset_id && <p className="flex items-center gap-2 text-sm text-destructive"><AlertCircle className="size-4" /> {errors.asset_id}</p>}
							{lookupState === 'found' && <p className="flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-400"><CheckCircle2 className="size-4" /> This report will be linked to {asset?.asset_name}.</p>}
							<div className="flex justify-end border-t pt-5"><Button type="submit" disabled={processing || !data.asset_id || lookupState !== 'found'}><FileWarning /> {processing ? 'Submitting...' : 'Submit report'}</Button></div>
						</form>
					</CardContent>
				</Card>
			</div>
		</AppLayout>
	);
}
