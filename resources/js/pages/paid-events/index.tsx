import { PaidEventCard, PaidEventListItem } from '@/components/paid-events/paid-event-card';
import { CustomPagination } from '@/components/ui/custom-pagination';
import { Input } from '@/components/ui/input';
import MainLayout from '@/layouts/main-layout';
import { Head, router } from '@inertiajs/react';
import { Search as SearchIcon, X } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

type PaginatedPaidEvents = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    data: PaidEventListItem[];
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
};

type PaidEventsPageProps = {
    paidEvents: PaginatedPaidEvents;
    filters: {
        search?: string;
    };
};

export default function PaidEventsPage({ paidEvents, filters }: PaidEventsPageProps) {
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilters = useCallback(() => {
        const params: Record<string, string> = {};
        if (search) params.search = search;

        router.get('/paid-events', params, {
            preserveState: true,
            replace: true,
        });
    }, [search]);

    const clearFilters = () => {
        setSearch('');
        router.get('/paid-events', {}, { preserveState: true, replace: true });
    };

    const hasActiveFilters = !!filters.search;

    // Debounced search
    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== filters.search) {
                applyFilters();
            }
        }, 500);

        return () => clearTimeout(timer);
    }, [search, filters.search, applyFilters]);

    return (
        <MainLayout>
            <Head title="Paid Events" />

            <section className="container mx-auto px-4 py-16">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold tracking-tight">Paid Events</h1>
                    <p className="mt-1 text-slate-600 dark:text-slate-300">
                        Register for bus trips, tours, and special events.
                    </p>
                </div>

                <div className="mb-6">
                    <div className="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                        <div className="flex flex-col gap-4 md:flex-row md:items-center">
                            <div className="relative flex-1">
                                <SearchIcon className="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <Input
                                    type="text"
                                    placeholder="Search by title, semester, or slug..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-10"
                                />
                            </div>

                            {hasActiveFilters && (
                                <button
                                    onClick={clearFilters}
                                    className="flex items-center gap-2 whitespace-nowrap rounded-md bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                >
                                    <X className="h-4 w-4" />
                                    Clear Filters
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                {paidEvents.data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-12">
                        <div className="mb-4 text-6xl">🎫</div>
                        <p className="mb-2 text-lg text-slate-500">No paid events found</p>
                        <p className="text-sm text-slate-400">
                            {hasActiveFilters
                                ? 'Try adjusting your search to see more events.'
                                : 'There are no paid events available at the moment.'}
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {paidEvents.data.map((paidEvent) => (
                            <PaidEventCard key={paidEvent.id} paidEvent={paidEvent} />
                        ))}
                    </div>
                )}

                {paidEvents.data.length > 0 && paidEvents.last_page > 1 && (
                    <div className="mt-8 flex justify-center">
                        <CustomPagination currentPage={paidEvents.current_page} totalPages={paidEvents.last_page} />
                    </div>
                )}
            </section>
        </MainLayout>
    );
}
