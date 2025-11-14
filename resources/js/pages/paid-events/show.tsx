import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import MainLayout from '@/layouts/main-layout';
import { Head, Link } from '@inertiajs/react';
import { isAfter, isWithinInterval } from 'date-fns';
import {
    ArrowLeft,
    CalendarDays,
    Clock,
    DollarSign,
    Info,
    MapPin,
    Users,
    FileText,
    AlertCircle,
    CheckCircle2,
} from 'lucide-react';

type PaidEvent = {
    id: number;
    title: string;
    slug: string;
    semester: string;
    description: string | null;
    registration_deadline: string;
    registration_start_time: string;
    registration_limit: number | null;
    registration_fee: number;
    student_id_rules: string | null;
    student_id_rules_guide: string | null;
    pickup_points: string[] | null;
    departments: string[] | null;
    sections: string[] | null;
    lab_teacher_names: string[] | null;
    banner_image_url?: string;
};

type RegistrationInfo = {
    is_open: boolean;
    is_full: boolean;
    total_registrations: number;
    confirmed_registrations: number;
};

type PaidEventDetailsPageProps = {
    paidEvent: PaidEvent;
    registrationInfo: RegistrationInfo;
};

export default function PaidEventDetailsPage({ paidEvent, registrationInfo }: PaidEventDetailsPageProps) {
    const registrationStart = new Date(paidEvent.registration_start_time);
    const registrationDeadline = new Date(paidEvent.registration_deadline);
    const now = new Date();

    const isUpcoming = isAfter(registrationStart, now);
    const isOpen = isWithinInterval(now, { start: registrationStart, end: registrationDeadline });
    const isClosed = isAfter(now, registrationDeadline);

    const formatDateTime = (date: Date) => {
        return new Intl.DateTimeFormat('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
        }).format(date);
    };

    const RegistrationStatusBadge = () => {
        if (registrationInfo.is_full) {
            return (
                <Badge
                    variant="outline"
                    className="border-red-300 bg-red-50 text-red-700 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400"
                >
                    <AlertCircle className="mr-1.5 h-3 w-3" />
                    Registration Full
                </Badge>
            );
        }

        if (isOpen && registrationInfo.is_open) {
            return (
                <Badge
                    variant="outline"
                    className="border-green-300 bg-green-50 text-green-700 dark:border-green-800/30 dark:bg-green-900/20 dark:text-green-400"
                >
                    <CheckCircle2 className="mr-1.5 h-3 w-3" />
                    Registration Open
                </Badge>
            );
        }

        if (isUpcoming) {
            return (
                <Badge
                    variant="outline"
                    className="border-blue-300 bg-blue-50 text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/20 dark:text-blue-400"
                >
                    <Clock className="mr-1.5 h-3 w-3" />
                    Opens Soon
                </Badge>
            );
        }

        return (
            <Badge
                variant="outline"
                className="border-slate-300 bg-slate-50 text-slate-700 dark:border-slate-800/30 dark:bg-slate-900/20 dark:text-slate-400"
            >
                <AlertCircle className="mr-1.5 h-3 w-3" />
                Registration Closed
            </Badge>
        );
    };

    const canRegister = registrationInfo.is_open && !registrationInfo.is_full && !isClosed;

    const getButtonText = () => {
        if (registrationInfo.is_full) return '🚫 Registration Full';
        if (isClosed) return '⏰ Registration Closed';
        if (isUpcoming) return '🕐 Registration Not Started';
        return '🎫 Register Now';
    };

    return (
        <MainLayout>
            <Head title={paidEvent.title} />

            <section className="container mx-auto px-4 py-8 pb-24 lg:pb-8">
                {/* Back Button */}
                <Link href="/paid-events">
                    <Button variant="ghost" size="sm" className="mb-4 gap-2">
                        <ArrowLeft className="h-4 w-4" />
                        Back to Paid Events
                    </Button>
                </Link>

                {/* Main Content */}
                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Left Column - Main Info */}
                    <div className="lg:col-span-2">
                        {/* Banner Image */}
                        {paidEvent.banner_image_url && (
                            <div className="mb-6 overflow-hidden rounded-xl border border-slate-200 shadow-sm dark:border-slate-700">
                                <img
                                    src={paidEvent.banner_image_url}
                                    alt={paidEvent.title}
                                    className="w-full object-contain"
                                />
                            </div>
                        )}

                        <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <div className="mb-4">
                                <div className="mb-3 flex items-start justify-between gap-3">
                                    <h1 className="flex-1 text-2xl font-bold text-slate-900 sm:text-3xl dark:text-white">
                                        {paidEvent.title}
                                    </h1>
                                    <div className="hidden shrink-0 sm:block">
                                        <RegistrationStatusBadge />
                                    </div>
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <Badge variant="outline" className="border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                                        📚 {paidEvent.semester}
                                    </Badge>
                                    <div className="sm:hidden">
                                        <RegistrationStatusBadge />
                                    </div>
                                </div>
                            </div>

                            {/* Description */}
                            {paidEvent.description && (
                                <div className="prose prose-slate mb-6 max-w-none dark:prose-invert">
                                    <div dangerouslySetInnerHTML={{ __html: paidEvent.description }} />
                                </div>
                            )}

                            {/* Student ID Rules */}
                            {paidEvent.student_id_rules && (
                                <div className="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/30 dark:bg-blue-900/20">
                                    <div className="mb-2 flex items-center gap-2 font-semibold text-blue-900 dark:text-blue-300">
                                        <FileText className="h-4 w-4" />
                                        Student ID Requirements
                                    </div>
                                    <p className="text-sm text-blue-800 dark:text-blue-300">{paidEvent.student_id_rules}</p>
                                    {paidEvent.student_id_rules_guide && (
                                        <p className="mt-2 text-xs text-blue-700 dark:text-blue-400">
                                            Guide: {paidEvent.student_id_rules_guide}
                                        </p>
                                    )}
                                </div>
                            )}

                            {/* Additional Info */}
                            <div className="space-y-4">
                                {paidEvent.departments && paidEvent.departments.length > 0 && (
                                    <div>
                                        <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            <Info className="h-4 w-4" />
                                            Eligible Departments
                                        </h3>
                                        <div className="flex flex-wrap gap-2">
                                            {paidEvent.departments.map((dept) => (
                                                <Badge key={dept} variant="secondary">
                                                    {dept}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {paidEvent.sections && paidEvent.sections.length > 0 && (
                                    <div>
                                        <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            <Info className="h-4 w-4" />
                                            Sections
                                        </h3>
                                        <div className="flex flex-wrap gap-2">
                                            {paidEvent.sections.map((section) => (
                                                <Badge key={section} variant="secondary">
                                                    {section}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {paidEvent.pickup_points && paidEvent.pickup_points.length > 0 && (
                                    <div>
                                        <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            <MapPin className="h-4 w-4" />
                                            Pickup Points
                                        </h3>
                                        <div className="flex flex-wrap gap-2">
                                            {paidEvent.pickup_points.map((point) => (
                                                <Badge key={point} variant="outline">
                                                    📍 {point}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {paidEvent.lab_teacher_names && paidEvent.lab_teacher_names.length > 0 && (
                                    <div>
                                        <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            <Users className="h-4 w-4" />
                                            Lab Teachers
                                        </h3>
                                        <div className="flex flex-wrap gap-2">
                                            {paidEvent.lab_teacher_names.map((teacher) => (
                                                <Badge key={teacher} variant="outline">
                                                    👨‍🏫 {teacher}
                                                </Badge>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Right Column - Registration Info */}
                    <div className="lg:col-span-1">
                        <div className="sticky top-4 space-y-4">
                            {/* Registration Fee */}
                            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <div className="mb-4 flex items-center justify-between">
                                    <span className="text-sm font-medium text-slate-600 dark:text-slate-400">Registration Fee</span>
                                    <div className="flex items-center gap-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        <DollarSign className="h-5 w-5" />৳{paidEvent.registration_fee}
                                    </div>
                                </div>

                                {/* Registration Stats */}
                                <div className="space-y-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-slate-600 dark:text-slate-400">Total Registrations</span>
                                        <span className="font-semibold text-slate-900 dark:text-slate-100">
                                            {registrationInfo.total_registrations}
                                        </span>
                                    </div>
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="text-slate-600 dark:text-slate-400">Confirmed</span>
                                        <span className="font-semibold text-slate-900 dark:text-slate-100">
                                            {registrationInfo.confirmed_registrations}
                                        </span>
                                    </div>
                                    {paidEvent.registration_limit && (
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="text-slate-600 dark:text-slate-400">Limit</span>
                                            <span className="font-semibold text-slate-900 dark:text-slate-100">
                                                {paidEvent.registration_limit}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                {/* Progress Bar */}
                                {paidEvent.registration_limit && (
                                    <div className="mt-4">
                                        <div className="mb-1 flex items-center justify-between text-xs text-slate-500">
                                            <span>Capacity</span>
                                            <span>
                                                {Math.round((registrationInfo.confirmed_registrations / paidEvent.registration_limit) * 100)}%
                                            </span>
                                        </div>
                                        <div className="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div
                                                className="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 dark:from-blue-400 dark:to-cyan-400"
                                                style={{
                                                    width: `${Math.min(100, (registrationInfo.confirmed_registrations / paidEvent.registration_limit) * 100)}%`,
                                                }}
                                            ></div>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Timeline */}
                            <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                <h3 className="mb-4 font-semibold text-slate-900 dark:text-white">Registration Timeline</h3>
                                <div className="space-y-4">
                                    <div>
                                        <div className="mb-1 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <CalendarDays className="h-4 w-4 text-blue-500" />
                                            Opens
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">{formatDateTime(registrationStart)}</p>
                                    </div>
                                    <div>
                                        <div className="mb-1 flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                            <Clock className="h-4 w-4 text-blue-500" />
                                            Deadline
                                        </div>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">
                                            {formatDateTime(registrationDeadline)}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Register Button - Desktop */}
                            <Button
                                className="hidden w-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-600 font-medium text-white shadow-md transition-all hover:from-blue-700 hover:to-cyan-700 hover:shadow-xl disabled:from-slate-400 disabled:to-slate-500 disabled:opacity-50 lg:block dark:from-blue-500 dark:to-cyan-500 dark:hover:from-blue-600 dark:hover:to-cyan-600"
                                size="lg"
                                disabled={!canRegister}
                            >
                                {getButtonText()}
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            {/* Fixed Register Button - Mobile */}
            <div className="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white/95 p-4 backdrop-blur-sm lg:hidden dark:border-slate-700 dark:bg-slate-900/95">
                <Button
                    className="w-full rounded-full bg-gradient-to-r from-blue-600 to-cyan-600 font-medium text-white shadow-lg transition-all hover:from-blue-700 hover:to-cyan-700 hover:shadow-xl disabled:from-slate-400 disabled:to-slate-500 disabled:opacity-50 dark:from-blue-500 dark:to-cyan-500 dark:hover:from-blue-600 dark:hover:to-cyan-600"
                    size="lg"
                    disabled={!canRegister}
                >
                    {getButtonText()}
                </Button>
            </div>
        </MainLayout>
    );
}
