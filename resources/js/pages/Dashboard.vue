<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface Props {
    dashboardType: 'super_admin' | 'owner' | 'cashier';
    stats?: {
        totalButchers?: number;
        activeSubscriptions?: number;
        expiredSubscriptions?: number;
        suspendedAccounts?: number;
        totalPlatformRevenue?: string;
        monthlyRevenue?: string;
        todaySales?: string;
        totalSales?: number;
        totalRevenue?: string;
        mySalesCount?: number;
    };
    recentButchers?: Array<{
        id: number;
        name: string;
        subscription_status: string;
        owner?: { name: string; email: string };
        subscription_plan?: { name: string };
    }>;
    expiringIn7Days?: Array<{
        id: number;
        name: string;
        subscription_end_date: string;
        owner?: { name: string };
    }>;
    plans?: Array<{
        id: number;
        name: string;
        price: string;
        duration_days: number;
        butcher_shops_count: number;
    }>;
    butcherShop?: {
        name: string;
        subscription_status: string;
        subscription_end_date: string;
        subscription_plan?: { name: string };
    };
    recentSales?: Array<{
        id: number;
        sale_number: string;
        total_amount: string;
        sale_date: string;
        user?: { name: string };
    }>;
    error?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const statusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-500';
        case 'expired': return 'bg-yellow-500';
        case 'suspended': return 'bg-red-500';
        default: return 'bg-gray-500';
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Super Admin Dashboard -->
            <template v-if="dashboardType === 'super_admin'">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold">Super Admin Dashboard</h1>
                    <Badge variant="outline" class="text-sm">Platform Overview</Badge>
                </div>

                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Total Butcher Shops</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ stats?.totalButchers || 0 }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Active Subscriptions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-green-600">{{ stats?.activeSubscriptions || 0 }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Expired</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-yellow-600">{{ stats?.expiredSubscriptions || 0 }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Suspended</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold text-red-600">{{ stats?.suspendedAccounts || 0 }}</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Revenue Cards -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Total Platform Revenue</CardTitle>
                            <CardDescription>All time earnings</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">${{ stats?.totalPlatformRevenue || '0.00' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Monthly Revenue</CardTitle>
                            <CardDescription>This month's earnings</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">${{ stats?.monthlyRevenue || '0.00' }}</div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Recent Butcher Shops & Expiring Soon -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Butcher Shops</CardTitle>
                            <CardDescription>Latest registered shops</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="recentButchers && recentButchers.length > 0" class="space-y-3">
                                <div v-for="shop in recentButchers" :key="shop.id" class="flex items-center justify-between border-b pb-2 last:border-0">
                                    <div>
                                        <p class="font-medium">{{ shop.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ shop.owner?.email }}</p>
                                    </div>
                                    <Badge :class="statusColor(shop.subscription_status)">
                                        {{ shop.subscription_status }}
                                    </Badge>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No butcher shops yet</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Expiring Soon</CardTitle>
                            <CardDescription>Subscriptions expiring in 7 days</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div v-if="expiringIn7Days && expiringIn7Days.length > 0" class="space-y-3">
                                <div v-for="shop in expiringIn7Days" :key="shop.id" class="flex items-center justify-between border-b pb-2 last:border-0">
                                    <div>
                                        <p class="font-medium">{{ shop.name }}</p>
                                        <p class="text-sm text-muted-foreground">{{ shop.owner?.name }}</p>
                                    </div>
                                    <span class="text-sm text-yellow-600">{{ shop.subscription_end_date }}</span>
                                </div>
                            </div>
                            <p v-else class="text-muted-foreground">No expiring subscriptions</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Plans Overview -->
                <Card>
                    <CardHeader>
                        <CardTitle>Subscription Plans</CardTitle>
                        <CardDescription>Available plans and subscriber count</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="plans && plans.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="plan in plans" :key="plan.id" class="rounded-lg border p-4">
                                <h3 class="font-semibold">{{ plan.name }}</h3>
                                <p class="text-2xl font-bold">${{ plan.price }}</p>
                                <p class="text-sm text-muted-foreground">{{ plan.duration_days }} days</p>
                                <p class="mt-2 text-sm">{{ plan.butcher_shops_count }} subscribers</p>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">No plans configured</p>
                    </CardContent>
                </Card>
            </template>

            <!-- Owner Dashboard -->
            <template v-else-if="dashboardType === 'owner'">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold">{{ butcherShop?.name || 'My Shop' }}</h1>
                    <Badge v-if="butcherShop" :class="statusColor(butcherShop.subscription_status)">
                        {{ butcherShop.subscription_status }}
                    </Badge>
                </div>

                <div v-if="error" class="rounded-lg bg-red-100 p-4 text-red-700">
                    {{ error }}
                </div>

                <div v-else class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Today's Sales</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">${{ stats?.todaySales || '0.00' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Total Transactions</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ stats?.totalSales || 0 }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Total Revenue</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">${{ stats?.totalRevenue || '0.00' }}</div>
                        </CardContent>
                    </Card>
                </div>
            </template>

            <!-- Cashier Dashboard -->
            <template v-else-if="dashboardType === 'cashier'">
                <h1 class="text-2xl font-bold">Cashier Dashboard</h1>

                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">My Sales Today</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">${{ stats?.todaySales || '0.00' }}</div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium">Transactions Today</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-3xl font-bold">{{ stats?.mySalesCount || 0 }}</div>
                        </CardContent>
                    </Card>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
