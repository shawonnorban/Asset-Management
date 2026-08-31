import ResourceDetail from '@/components/resource-detail';
export default function CompletedReportShow({title,issueReport}: {title:string; issueReport:Record<string,unknown>}) { return <ResourceDetail title={title} data={issueReport} back="/completed-reports" />; }
