<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface ButcherShop {
    id: number;
    name: string;
    address: string;
    phone: string;
    subscription_status: string;
    subscription_end_date: string;
    owner?: { id: number; name: string; email: string; is_active: boolean };
    subscription_plan?: { id: number; name: string };
}

interface Props {
    butcherShops: {
        data: ButcherShop[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Butcher Shops', href: '/super-admin/butcher-shops' },
];

const statusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-500 text-white';
        case 'expired': return 'bg-yellow-500 text-white';
        case 'suspended': return 'bg-red-500 text-white';
        default: return 'bg-gray-500 text-white';
    }
};

const toggleStatus = (shop: ButcherShop) => {
    if (confirm(`Are you sure you want to ${shop.subscription_status === 'suspended' ? 'activate' : 'suspend'} "${shop.name}"?`)) {
        router.post(`/super-admin/butcher-shops/${shop.id}/toggle-status`);
    }
};
</script>

<template>
    <Head title="Butcher Shops" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Butcher Shops</h1>
                <Link href="/super-admin/butcher-shops/create">
                    <Button>Add New Butcher Shop</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>All Butcher Shops ({{ butcherShops.total }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="butcherShops.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 font-medium">Shop Name</th>
                                    <th class="pb-3 font-medium">Owner</th>
                                    <th class="pb-3 font-medium">Plan</th>
                                    <th class="pb-3 font-medium">Status</th>
                                    <th class="pb-3 font-medium">Expires</th>
                                    <th class="pb-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="shop in butcherShops.data" :key="shop.id" class="border-b last:border-0">
                                    <td class="py-3">
                                        <div>
                                            <p class="font-medium">{{ shop.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ shop.phone }}</p>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div>
                                            <p>{{ shop.owner?.name }}</p>
                                            <p class="text-sm text-muted-foreground">{{ shop.owner?.email }}</p>
                                        </div>
                                    </td>
                                    <td class="py-3">{{ shop.subscription_plan?.name || 'N/A' }}</td>
                                    <td class="py-3">
                                        <Badge :class="statusColor(shop.subscription_status)">
                                            {{ shop.subscription_status }}
                                        </Badge>
                                    </td>
                                    <td class="py-3">{{ shop.subscription_end_date || 'N/A' }}</td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <Link :href="`/super-admin/butcher-shops/${shop.id}/edit`">
                                                <Button variant="outline" size="sm">Edit</Button>
                                            </Link>
                                            <Button 
                                                :variant="shop.subscription_status === 'suspended' ? 'default' : 'destructive'" 
                                                size="sm"
                                                @click="toggleStatus(shop)"
                                            >
                                                {{ shop.subscription_status === 'suspended' ? 'Activate' : 'Suspend' }}
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-muted-foreground">No butcher shops yet. Click "Add New Butcher Shop" to create one.</p>

                    <!-- Pagination -->
                    <div v-if="butcherShops.last_page > 1" class="mt-4 flex justify-center gap-2">
                        <Link 
                            v-for="page in butcherShops.last_page" 
                            :key="page"
                            :href="`/super-admin/butcher-shops?page=${page}`"
                            :class="[
                                'px-3 py-1 rounded border',
                                page === butcherShops.current_page ? 'bg-primary text-white' : 'hover:bg-muted'
                            ]"
                        >
                            {{ page }}
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
