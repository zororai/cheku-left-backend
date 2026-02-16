<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface StockMovement {
    id: number;
    product_id: number;
    product_name: string;
    opening_grams: number;
    sold_grams: number;
    closing_grams: number;
    expected_closing_grams: number;
    variance_grams: number;
    product?: { id: number; name: string };
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
    session: StockSession;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Stock Sessions', href: '/owner/stock-sessions' },
    { title: `Session #${props.session.id}`, href: `/owner/stock-sessions/${props.session.id}` },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const gramsToKg = (grams: number) => {
    return (grams / 1000).toFixed(2);
};

const getTotalVariance = () => {
    return props.session.stock_movements.reduce((sum, m) => sum + (m.variance_grams || 0), 0);
};
</script>

<template>
    <Head :title="`Stock Session #${session.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Stock Session #{{ session.id }}</h1>
                    <p class="text-muted-foreground">{{ formatDate(session.opened_at) }}</p>
                </div>
                <Link href="/owner/stock-sessions">
                    <Button variant="outline">Back to Sessions</Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Session Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Session Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :variant="session.status === 'open' ? 'default' : 'secondary'">
                                {{ session.status }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Opened</span>
                            <span>{{ formatDate(session.opened_at) }}</span>
                        </div>
                        <div v-if="session.closed_at" class="flex justify-between">
                            <span class="text-muted-foreground">Closed</span>
                            <span>{{ formatDate(session.closed_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Salesman</span>
                            <span>{{ session.user?.name || 'Unknown' }}</span>
                        </div>
                        <div v-if="session.notes" class="pt-2 border-t">
                            <p class="text-sm text-muted-foreground mb-1">Notes</p>
                            <p>{{ session.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Summary -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="text-center p-4 bg-muted rounded-lg">
                                <p class="text-sm text-muted-foreground">Opening Stock</p>
                                <p class="text-2xl font-bold">
                                    {{ gramsToKg(session.stock_movements.reduce((sum, m) => sum + m.opening_grams, 0)) }} kg
                                </p>
                            </div>
                            <div class="text-center p-4 bg-muted rounded-lg">
                                <p class="text-sm text-muted-foreground">Total Sold</p>
                                <p class="text-2xl font-bold">
                                    {{ gramsToKg(session.stock_movements.reduce((sum, m) => sum + m.sold_grams, 0)) }} kg
                                </p>
                            </div>
                            <div class="text-center p-4 rounded-lg" :class="getTotalVariance() < 0 ? 'bg-red-100' : 'bg-green-100'">
                                <p class="text-sm text-muted-foreground">Total Variance</p>
                                <p class="text-2xl font-bold" :class="getTotalVariance() < 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ gramsToKg(getTotalVariance()) }} kg
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Stock Movements -->
            <Card>
                <CardHeader>
                    <CardTitle>Stock Movements</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="session.stock_movements.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 font-medium">Product</th>
                                    <th class="pb-3 font-medium text-right">Opening</th>
                                    <th class="pb-3 font-medium text-right">Sold</th>
                                    <th class="pb-3 font-medium text-right">Expected</th>
                                    <th class="pb-3 font-medium text-right">Actual</th>
                                    <th class="pb-3 font-medium text-right">Variance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="movement in session.stock_movements" :key="movement.id" class="border-b last:border-0">
                                    <td class="py-3 font-medium">{{ movement.product_name }}</td>
                                    <td class="py-3 text-right">{{ gramsToKg(movement.opening_grams) }} kg</td>
                                    <td class="py-3 text-right">{{ gramsToKg(movement.sold_grams) }} kg</td>
                                    <td class="py-3 text-right">{{ gramsToKg(movement.expected_closing_grams || 0) }} kg</td>
                                    <td class="py-3 text-right">{{ gramsToKg(movement.closing_grams || 0) }} kg</td>
                                    <td class="py-3 text-right font-semibold" :class="movement.variance_grams < 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ gramsToKg(movement.variance_grams || 0) }} kg
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 font-semibold">
                                    <td class="py-3">Total</td>
                                    <td class="py-3 text-right">
                                        {{ gramsToKg(session.stock_movements.reduce((sum, m) => sum + m.opening_grams, 0)) }} kg
                                    </td>
                                    <td class="py-3 text-right">
                                        {{ gramsToKg(session.stock_movements.reduce((sum, m) => sum + m.sold_grams, 0)) }} kg
                                    </td>
                                    <td class="py-3 text-right">
                                        {{ gramsToKg(session.stock_movements.reduce((sum, m) => sum + (m.expected_closing_grams || 0), 0)) }} kg
                                    </td>
                                    <td class="py-3 text-right">
                                        {{ gramsToKg(session.stock_movements.reduce((sum, m) => sum + (m.closing_grams || 0), 0)) }} kg
                                    </td>
                                    <td class="py-3 text-right" :class="getTotalVariance() < 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ gramsToKg(getTotalVariance()) }} kg
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p v-else class="text-muted-foreground text-center py-4">No stock movements recorded.</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
