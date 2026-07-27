import { Head, Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

interface KpiRow {
    id: number;
    description: string;
    title: string;
    target: string | null;
    unit_of_measure: string | null;
    status: 'Pending' | 'Approved' | 'Rejected' | 'Completed';
    progress_count: number;
    responsible_units: { id: number; name: string; acronym: string | null }[];
}

interface StrategicPlan {
    id: number;
    school_year: string;
    status: string;
}

interface Props {
    kpis: KpiRow[];
    strategicPlans: StrategicPlan[];
    selectedStrategicPlanId: number | null;
}

const STATUS_VARIANT: Record<
    KpiRow['status'],
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    Pending: 'secondary',
    Approved: 'default',
    Rejected: 'destructive',
    Completed: 'outline',
};

export default function KeyResultAreaKpiIndex({
    kpis,
    strategicPlans,
    selectedStrategicPlanId,
}: Props) {
    const handleAcademicYearChange = (value: string) => {
        router.get(
            '/key-result-area/kpi',
            { strategic_plan_id: value },
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="My KPIs" />

            <div className="space-y-6 p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            My KPIs
                        </h1>
                        <p className="text-muted-foreground">
                            KPIs proposed by or assigned to your unit.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <Select
                            value={selectedStrategicPlanId?.toString() ?? ''}
                            onValueChange={handleAcademicYearChange}
                        >
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Academic year" />
                            </SelectTrigger>
                            <SelectContent>
                                {strategicPlans.map((plan) => (
                                    <SelectItem
                                        key={plan.id}
                                        value={plan.id.toString()}
                                    >
                                        {plan.school_year}
                                        {plan.status === 'Active' && ' (Active)'}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Button asChild>
                            <Link href="/key-result-area/kpi/create">
                                <Plus className="mr-2 h-4 w-4" />
                                Propose a KPI
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Target</TableHead>
                                <TableHead>Responsible units</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">
                                    Progress entries
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {kpis.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={5}
                                        className="py-8 text-center text-muted-foreground"
                                    >
                                        No KPIs yet for this academic year.
                                        Propose your first one.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                kpis.map((kpi) => (
                                    <TableRow key={kpi.id}>
                                        <TableCell className="font-medium">
                                            <Link
                                                href={`/key-result-area/kpi/${kpi.id}`}
                                                className="hover:underline"
                                            >
                                                {kpi.description} {''}
                                                {kpi.title}
                                            </Link>
                                        </TableCell>
                                        <TableCell>
                                            {kpi.target ?? '—'}{' '}
                                            {kpi.unit_of_measure && (
                                                <span className="text-sm text-muted-foreground">
                                                    ({kpi.unit_of_measure})
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-sm text-muted-foreground">
                                            {kpi.responsible_units
                                                .map((u) => u.acronym ?? u.name)
                                                .join(', ') || '—'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    STATUS_VARIANT[kpi.status]
                                                }
                                            >
                                                {kpi.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {kpi.progress_count}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
        </>
    );
}