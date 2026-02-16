<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref } from 'vue';

interface SaleItem {
    id: number;
    quantity: number;
    unit_price: string;
    total_price: string;
    product?: { name: string; unit: string };
}

interface Sale {
    id: number;
    sale_number: string;
    total_amount: string;
    payment_method: string;
    sale_date: string;
    synced_at: string;
    user?: { id: number; name: string };
    items?: SaleItem[];
}

interface Props {
    sales: {
        data: Sale[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        todaySales: string;
        weekSales: string;
        monthSales: string;
        totalTransactions: number;
    };
    cashiers: Array<{ id: number; name: string }>;
    filters: {
        date_from?: string;
        date_to?: string;
        cashier_id?: string;
    };
    error?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sales', href: '/owner/sales' },
];

const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const cashierId = ref(props.filters.cashier_id || '');

const applyFilters = () => {
    router.get('/owner/sales', {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        cashier_id: cashierId.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    cashierId.value = '';
    router.get('/owner/sales');
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
</script>

<template>
    <Head title="Sales Records" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Sales Records</h1>
            </div>

            <div v-if="error" class="rounded-lg bg-red-100 p-4 text-red-700">
                {{ error }}
            </div>

            <template v-else>
                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Today</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">${{ stats.todaySales }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">This Week</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">${{ stats.weekSales }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">${{ stats.monthSales }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Total Transactions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ stats.totalTransactions }}</div>
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
                                <Label for="cashier">Salesman</Label>
                                <select 
                                    id="cashier"
                                    v-model="cashierId"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2"
                                >
                                    <option value="">All</option>
                                    <option v-for="cashier in cashiers" :key="cashier.id" :value="cashier.id">
                                        {{ cashier.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <Button @click="applyFilters">Apply</Button>
                                <Button variant="outline" @click="clearFilters">Clear</Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sales Table -->
                <Card>
                    <CardHeader>
                        <CardTitle>Sales ({{ sales.total }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="sales.data.length > 0" class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-3 font-medium">Sale #</th>
                                        <th class="pb-3 font-medium">Date</th>
                                        <th class="pb-3 font-medium">Salesman</th>
                                        <th class="pb-3 font-medium">Payment</th>
                                        <th class="pb-3 font-medium text-right">Amount</th>
                                        <th class="pb-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="sale in sales.data" :key="sale.id" class="border-b last:border-0">
                                        <td class="py-3 font-mono text-sm">{{ sale.sale_number }}</td>
                                        <td class="py-3 text-sm">{{ formatDate(sale.sale_date) }}</td>
                                        <td class="py-3">{{ sale.user?.name || 'Unknown' }}</td>
                                        <td class="py-3 capitalize">{{ sale.payment_method }}</td>
                                        <td class="py-3 text-right font-semibold">${{ sale.total_amount }}</td>
                                        <td class="py-3">
                                            <Link :href="`/owner/sales/${sale.id}`">
                                                <Button variant="outline" size="sm">View</Button>
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="text-center py-8 text-muted-foreground">
                            No sales found. Sales synced from the mobile app will appear here.
                        </p>

                        <!-- Pagination -->
                        <div v-if="sales.last_page > 1" class="mt-4 flex justify-center gap-2">
                            <Link 
                                v-for="page in sales.last_page" 
                                :key="page"
                                :href="`/owner/sales?page=${page}&date_from=${dateFrom}&date_to=${dateTo}&cashier_id=${cashierId}`"
                                :class="[
                                    'px-3 py-1 rounded border',
                                    page === sales.current_page ? 'bg-primary text-white' : 'hover:bg-muted'
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
