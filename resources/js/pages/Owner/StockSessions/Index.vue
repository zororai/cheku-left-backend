<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { ref } from 'vue';

interface StockMovement {
    id: number;
    product_name: string;
    opening_grams: number;
    sold_grams: number;
    closing_grams: number;
    variance_grams: number;
}

interface StockSession {
    id: number;
    status: 'open' | 'closed';
    opened_at: string;
    closed_at: string | null;
    notes: string | null;
    user?: { id: number; name: string };
    stock_movements: StockMovement[];
}

interface Props {
    sessions: {
        data: StockSession[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        totalSessions: number;
        openSessions: number;
        totalVariance: string;
    };
    filters: {
        date_from?: string;
        date_to?: string;
        status?: string;
    };
    error?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Stock Sessions', href: '/owner/stock-sessions' },
];

const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/owner/stock-sessions', {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    status.value = '';
    router.get('/owner/stock-sessions');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getTotalVariance = (movements: StockMovement[]) => {
    const total = movements.reduce((sum, m) => sum + (m.variance_grams || 0), 0);
    return (total / 1000).toFixed(2);
};
</script>

<template>
    <Head title="Stock Sessions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Stock Sessions</h1>
            </div>

            <div v-if="error" class="rounded-lg bg-red-100 p-4 text-red-700">
                {{ error }}
            </div>

            <template v-else>
                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Total Sessions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.totalSessions }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Open Sessions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.openSessions }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Total Variance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" :class="stats.totalVariance.startsWith('-') ? 'text-red-600' : ''">
                                {{ stats.totalVariance }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Filters -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Filters</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-4">
                            <div class="space-y-2">
                                <Label for="date_from">From Date</Label>
                                <Input id="date_from" type="date" v-model="dateFrom" />
                            </div>
                            <div class="space-y-2">
                                <Label for="date_to">To Date</Label>
                                <Input id="date_to" type="date" v-model="dateTo" />
                            </div>
                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <select 
                                    id="status"
                                    v-model="status"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option value="">All</option>
                                    <option value="open">Open</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <Button @click="applyFilters">Apply</Button>
                                <Button variant="outline" @click="clearFilters">Clear</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sessions Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>Sessions ({{ sessions.total }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="sessions.data.length > 0" class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-3 font-medium">Date</th>
                                        <th class="pb-3 font-medium">Salesman</th>
                                        <th class="pb-3 font-medium">Status</th>
                                        <th class="pb-3 font-medium text-right">Variance</th>
                                        <th class="pb-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="session in sessions.data" :key="session.id" class="border-b last:border-0">
                                        <td class="py-3">
                                            <div>
                                                <p class="font-medium">{{ formatDate(session.opened_at) }}</p>
                                                <p v-if="session.closed_at" class="text-sm text-muted-foreground">
                                                    Closed: {{ formatDate(session.closed_at) }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="py-3">{{ session.user?.name || 'Unknown' }}</td>
                                        <td class="py-3">
                                            <Badge :variant="session.status === 'open' ? 'default' : 'secondary'">
                                                {{ session.status }}
                                            </Badge>
                                        </td>
                                        <td class="py-3 text-right font-semibold" :class="parseFloat(getTotalVariance(session.stock_movements)) < 0 ? 'text-red-600' : 'text-green-600'">
                                            {{ getTotalVariance(session.stock_movements) }} kg
                                        </td>
                                        <td class="py-3">
                                            <Link :href="`/owner/stock-sessions/${session.id}`">
                                                <Button variant="outline" size="sm">View</Button>
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="text-center py-8 text-muted-foreground">
                            No stock sessions found. Sessions from the mobile app will appear here.
                        </p>

                        <!-- Pagination -->
                        <div v-if="sessions.last_page > 1" class="mt-4 flex justify-center gap-2">
                            <Link 
                                v-for="page in sessions.last_page" 
                                :key="page"
                                :href="`/owner/stock-sessions?page=${page}&date_from=${dateFrom}&date_to=${dateTo}&status=${status}`"
                                :class="[
                                    'px-3 py-1 rounded border',
                                    page === sessions.current_page ? 'bg-primary text-white' : 'hover:bg-muted'
                                ]"
                            >
                                {{ page }}
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </template>
        </div>
    </AppLayout>
</template>
