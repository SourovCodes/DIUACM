import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/react';
import { isAfter, isWithinInterval } from 'date-fns';
import { ArrowUpRight, CalendarDays, Clock, DollarSign, Users } from 'lucide-react';

export type PaidEventListItem = {
    id: number;
    title: string;
    slug: string;
    semester: string;
    registration_deadline: string;
    registration_start_time: string;
    registration_limit: number | null;
    registration_fee: number;
    registrations_count?: number;
    banner_image_url?: string;
};

type Props = {
    paidEvent: PaidEventListItem;
};

export function PaidEventCard({ paidEvent }: Props) {
    // Compute time-based status
    const now = new Date();
    const registrationStart = new Date(paidEvent.registration_start_time);
    const registrationDeadline = new Date(paidEvent.registration_deadline);
    const isUpcoming = isAfter(registrationStart, now);
    const isOpen = isWithinInterval(now, { start: registrationStart, end: registrationDeadline });
    const isClosed = isAfter(now, registrationDeadline);

    // Calculate registration progress
    const progress = paidEvent.registration_limit
        ? Math.min(100, ((paidEvent.registrations_count ?? 0) / paidEvent.registration_limit) * 100)
        : 0;

    const formatDeadlineStatus = (deadlineDate: Date, reference: Date): string => {
        const diffInMinutes = Math.floor((deadlineDate.getTime() - reference.getTime()) / (1000 * 60));
        const diffInHours = Math.floor(diffInMinutes / 60);
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays > 0) return `${diffInDays} day${diffInDays > 1 ? 's' : ''} left`;
        if (diffInHours > 0) return `${diffInHours} hour${diffInHours > 1 ? 's' : ''} left`;
        if (diffInMinutes > 0) return `${diffInMinutes} minute${diffInMinutes > 1 ? 's' : ''} left`;
        return 'Closing soon';
    };

    const StatusBadge = () => {
        if (isOpen) {
            return (
                <Badge
                    variant="outline"
                    className="border-green-300/70 bg-gradient-to-r from-green-500/20 to-emerald-500/20 text-green-700 shadow-sm dark:border-green-700/70 dark:text-green-300"
                >
                    <span className="flex items-center gap-1.5">
                        <span className="h-2 w-2 rounded-full bg-green-600 dark:bg-green-400"></span>
                        Open Now
                    </span>
                </Badge>
            );
        }
        if (isUpcoming) {
            return (
                <Badge
                    variant="outline"
                    className="border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/20 dark:text-blue-400"
                >
                    {formatDeadlineStatus(registrationStart, now)}
                </Badge>
            );
        }
        return (
            <Badge
                variant="secondary"
                className="border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
            >
                Registration Closed
            </Badge>
        );
    };

    return (
        <Link href={`/paid-events/${paidEvent.slug}`} className="block">
            <div className="group relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-md transition-all hover:shadow-lg dark:border-slate-700 dark:bg-slate-900">
                {/* Banner Image */}
                {paidEvent.banner_image_url && (
                    <div className="relative aspect-video w-full overflow-hidden">
                        <img
                            src={paidEvent.banner_image_url}
                            alt={paidEvent.title}
                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <div className="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                        <div className="absolute top-4 right-4">
                            <StatusBadge />
                        </div>
                    </div>
                )}

                <div className="absolute -inset-1 -z-10 rounded-xl bg-gradient-to-r from-blue-500/10 via-cyan-500/10 to-indigo-500/10 opacity-0 transition-opacity duration-300 group-hover:opacity-70"></div>
                <div className="absolute inset-0 -z-10 bg-gradient-to-br from-blue-50 to-slate-50 opacity-50 dark:from-slate-800 dark:to-slate-900"></div>
                <div className="absolute -right-10 -bottom-10 -z-10 h-24 w-24 rounded-full bg-blue-100/40 dark:bg-blue-900/20"></div>

                <div className="relative z-10 p-5">
                    {!paidEvent.banner_image_url && (
                        <div className="mb-4 flex justify-end">
                            <StatusBadge />
                        </div>
                    )}

                    <div className="mb-4">
                        <h3 className="mb-2 line-clamp-2 text-base font-semibold text-slate-900 transition-colors group-hover:text-blue-600 sm:text-lg dark:text-white dark:group-hover:text-blue-400">
                            {paidEvent.title}
                        </h3>

                        <div className="flex flex-wrap items-center gap-3 text-sm text-slate-500 dark:text-slate-400">
                            <div className="flex items-center gap-1.5">
                                <CalendarDays className="h-4 w-4 text-blue-500" />
                                <span>
                                    Deadline:{' '}
                                    {new Intl.DateTimeFormat('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric',
                                    }).format(registrationDeadline)}
                                </span>
                            </div>

                            <div className="flex items-center gap-1.5">
                                <Clock className="h-4 w-4 text-blue-500" />
                                <span>
                                    {new Intl.DateTimeFormat('en-US', {
                                        hour: 'numeric',
                                        minute: '2-digit',
                                        hour12: true,
                                    }).format(registrationDeadline)}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-wrap gap-2">
                        <Badge
                            variant="default"
                            className="bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm dark:from-blue-500 dark:to-cyan-500"
                        >
                            <DollarSign className="mr-1 h-3 w-3" />৳{paidEvent.registration_fee}
                        </Badge>

                        <Badge variant="outline" className="border-slate-200 bg-white/30 dark:border-slate-700 dark:bg-slate-800/30">
                            📚 {paidEvent.semester}
                        </Badge>
                    </div>

                    {typeof paidEvent.registrations_count === 'number' && (
                        <div className="mt-4">
                            <div className="flex items-center justify-between text-sm text-slate-600 dark:text-slate-400">
                                <div className="flex items-center">
                                    <Users className="mr-1.5 h-4 w-4 text-blue-500" />
                                    <span className="flex items-center gap-1">
                                        <span className="font-medium text-slate-800 dark:text-slate-200">
                                            {paidEvent.registrations_count}
                                        </span>
                                        {paidEvent.registration_limit ? ` / ${paidEvent.registration_limit}` : ''} registered
                                    </span>
                                </div>
                                {paidEvent.registration_limit && (
                                    <span className="text-xs font-medium">{Math.round(progress)}% full</span>
                                )}
                            </div>
                            {paidEvent.registration_limit && (
                                <div className="mt-2 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        className="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 dark:from-blue-400 dark:to-cyan-400"
                                        style={{ width: `${progress}%` }}
                                    ></div>
                                </div>
                            )}
                        </div>
                    )}

                    {isOpen && (
                        <div className="mt-4">
                            <Badge
                                variant="outline"
                                className="border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/20 dark:text-amber-400"
                            >
                                ⏰ {formatDeadlineStatus(registrationDeadline, now)}
                            </Badge>
                        </div>
                    )}

                    <div className="absolute right-4 bottom-4 flex h-8 w-8 transform items-center justify-center rounded-full bg-blue-100 opacity-0 transition-all duration-300 group-hover:translate-x-1 group-hover:opacity-100 dark:bg-blue-900/50">
                        <ArrowUpRight className="h-4 w-4 text-blue-700 dark:text-blue-400" />
                    </div>
                </div>
            </div>
        </Link>
    );
}
