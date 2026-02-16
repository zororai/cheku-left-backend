<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';

interface Props {
    license: {
        plan: string;
        status: string;
        is_locked: boolean;
        payment_count: number;
        payment_limit: number;
        remaining_payments: number;
        expires_at: string | null;
    } | null;
    shop: {
        name: string;
        subscription_status: string;
        subscription_end_date: string | null;
    };
    stats: {
        todaySales: number;
        monthSales: number;
    };
    error?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'License & Subscription', href: '/owner/license' },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-100 text-green-800';
        case 'locked': return 'bg-red-100 text-red-800';
        case 'expired': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getUsagePercentage = () => {
    if (!props.license) return 0;
    return Math.min(100, (props.license.payment_count / props.license.payment_limit) * 100);
};
</script>

<template>
    <Head title="License & Subscription" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">License & Subscription</h1>
            </div>

            <div v-if="error" class="rounded-lg bg-red-100 p-4 text-red-700">
                {{ error }}
            </div>

            <template v-else-if="license">
                <!-- Shop Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>{{ shop.name }}</CardTitle>
                        <CardDescription>Your butcher shop subscription details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="flex justify-between items-center p-4 bg-muted rounded-lg">
                                <span class="text-muted-foreground">Subscription Status</span>
                                <Badge :class="getStatusColor(shop.subscription_status)">
                                    {{ shop.subscription_status }}
                                </Badge>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-muted rounded-lg">
                                <span class="text-muted-foreground">Subscription Expires</span>
                                <span class="font-semibold">{{ shop.subscription_end_date || 'N/A' }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- License Info -->
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>License Status</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Plan</span>
                                <Badge variant="outline" class="capitalize">{{ license.plan }}</Badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Status</span>
                                <Badge :class="getStatusColor(license.status)">
                                    {{ license.status }}
                                </Badge>
                            </div>
                            <div v-if="license.expires_at" class="flex justify-between items-center">
                                <span class="text-muted-foreground">Expires</span>
                                <span class="font-semibold">{{ license.expires_at }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Payment Usage</CardTitle>
                            <CardDescription>Track your transaction limit</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>{{ license.payment_count }} / {{ license.payment_limit }} transactions</span>
                                    <span>{{ license.remaining_payments }} remaining</span>
                                </div>
                                <Progress :model-value="getUsagePercentage()" class="h-3" />
                            </div>

                            <div v-if="license.is_locked" class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-800 font-semibold">License Locked</p>
                                <p class="text-sm text-red-600">
                                    You have reached your transaction limit. Please contact support to unlock your license.
                                </p>
                            </div>

                            <div v-else-if="license.remaining_payments <= 10" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-yellow-800 font-semibold">Running Low</p>
                                <p class="text-sm text-yellow-600">
                                    You have {{ license.remaining_payments }} transactions remaining. Consider upgrading soon.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Usage Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle>Transaction Stats</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="text-center p-6 bg-muted rounded-lg">
                                <p class="text-sm text-muted-foreground">Today's Transactions</p>
                                <p class="text-4xl font-bold mt-2">{{ stats.todaySales }}</p>
                            </div>
                            <div class="text-center p-6 bg-muted rounded-lg">
                                <p class="text-sm text-muted-foreground">This Month</p>
                                <p class="text-4xl font-bold mt-2">{{ stats.monthSales }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Contact Support -->
                <Card>
                    <CardHeader>
                        <CardTitle>Need Help?</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground mb-4">
                            Contact our support team to unlock your license or upgrade your plan.
                        </p>
                        <div class="flex gap-4">
                            <a href="tel:+263775219766" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90">
                                📞 +263 77 521 9766
                            </a>
                            <a href="mailto:support@chekuleft.co.zw" class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg hover:bg-muted">
                                ✉️ support@chekuleft.co.zw
                            </a>
                        </div>
                    </CardContent>
                </Card>
            </template>
        </div>
    </AppLayout>
</template>
