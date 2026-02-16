<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

interface SaleItem {
    id: number;
    quantity: number;
    unit_price: string;
    total_price: string;
    product?: { id: number; name: string; unit: string; price: string };
}

interface Sale {
    id: number;
    sale_number: string;
    total_amount: string;
    payment_method: string;
    sale_date: string;
    synced_at: string;
    user?: { id: number; name: string };
    items: SaleItem[];
}

interface Props {
    sale: Sale;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Sales', href: '/owner/sales' },
    { title: props.sale.sale_number, href: `/owner/sales/${props.sale.id}` },
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
</script>

<template>
    <Head :title="`Sale ${sale.sale_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Sale {{ sale.sale_number }}</h1>
                    <p class="text-muted-foreground">{{ formatDate(sale.sale_date) }}</p>
                </div>
                <Link href="/owner/sales">
                    <Button variant="outline">Back to Sales</Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Sale Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Sale Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Sale Number</span>
                            <span class="font-mono">{{ sale.sale_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Date</span>
                            <span>{{ formatDate(sale.sale_date) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Salesman</span>
                            <span>{{ sale.user?.name || 'Unknown' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Payment Method</span>
                            <Badge variant="outline" class="capitalize">{{ sale.payment_method }}</Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Synced At</span>
                            <span class="text-sm">{{ formatDate(sale.synced_at) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Total -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Total</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-4xl font-bold">${{ sale.total_amount }}</div>
                        <p class="text-muted-foreground mt-1">{{ sale.items?.length || 0 }} item(s)</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Items -->
            <Card>
                <CardHeader>
                    <CardTitle>Items</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="sale.items && sale.items.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 font-medium">Product</th>
                                    <th class="pb-3 font-medium text-right">Unit Price</th>
                                    <th class="pb-3 font-medium text-right">Quantity</th>
                                    <th class="pb-3 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in sale.items" :key="item.id" class="border-b last:border-0">
                                    <td class="py-3">
                                        <div>
                                            <p class="font-medium">{{ item.product?.name || 'Unknown Product' }}</p>
                                            <p class="text-sm text-muted-foreground">{{ item.product?.unit }}</p>
                                        </div>
                                    </td>
                                    <td class="py-3 text-right">${{ item.unit_price }}</td>
                                    <td class="py-3 text-right">{{ item.quantity }}</td>
                                    <td class="py-3 text-right font-semibold">${{ item.total_price }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2">
                                    <td colspan="3" class="py-3 text-right font-semibold">Total</td>
                                    <td class="py-3 text-right text-xl font-bold">${{ sale.total_amount }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p v-else class="text-muted-foreground">No items in this sale.</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
