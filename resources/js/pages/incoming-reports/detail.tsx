import ReportDetail from '@/components/report-detail';
interface Props { title:string; issueReport:Record<string,unknown>; feedback?:Record<string,unknown>|null; feedbackReplies?:Record<string,unknown>[]; }
export default function IncomingReportDetail({title,issueReport,feedback,feedbackReplies}: Props) { return <ReportDetail title={title} report={issueReport} feedback={feedback} feedbackReplies={feedbackReplies} mode="incoming" />; }
