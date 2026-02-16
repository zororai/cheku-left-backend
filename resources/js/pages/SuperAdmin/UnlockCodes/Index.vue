<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ref } from 'vue';

interface ButcherShop {
    id: number;
    name: string;
}

interface UnlockCode {
    id: number;
    butcher_id: number | null;
    code: string;
    additional_payments: number;
    is_used: boolean;
    used_at: string | null;
    expires_at: string | null;
    created_at: string;
    butcher_shop?: ButcherShop;
}

interface Props {
    unlockCodes: {
        data: UnlockCode[];
        current_page: number;
        last_page: number;
        total: number;
    };
    butcherShops: ButcherShop[];
    stats: {
        totalCodes: number;
        usedCodes: number;
        unusedCodes: number;
    };
    filters: {
        butcher_id?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Unlock Codes', href: '/super-admin/unlock-codes' },
];

const butcherId = ref(props.filters.butcher_id || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/super-admin/unlock-codes', {
        butcher_id: butcherId.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    butcherId.value = '';
    status.value = '';
    router.get('/super-admin/unlock-codes');
};

const deleteCode = (id: number) => {
    if (confirm('Are you sure you want to delete this unlock code?')) {
        router.delete(`/super-admin/unlock-codes/${id}`);
    }
};

const copyCode = (code: string) => {
    navigator.clipboard.writeText(code);
    alert('Code copied to clipboard!');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Unlock Codes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Unlock Codes</h1>
                <Link href="/super-admin/unlock-codes/create">
                    <Button>Generate Codes</Button>
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Codes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.totalCodes }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Used Codes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ stats.usedCodes }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Unused Codes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ stats.unusedCodes }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Butcher Shop</label>
                            <select 
                                v-model="butcherId"
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">All Shops</option>
                                <option v-for="shop in butcherShops" :key="shop.id" :value="shop.id">
                                    {{ shop.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Status</label>
                            <select 
                                v-model="status"
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">All</option>
                                <option value="used">Used</option>
                                <option value="unused">Unused</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <Button @click="applyFilters">Apply</Button>
                            <Button variant="outline" @click="clearFilters">Clear</Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Codes Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Codes ({{ unlockCodes.total }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="unlockCodes.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 font-medium">Code</th>
                                    <th class="pb-3 font-medium">Butcher Shop</th>
                                    <th class="pb-3 font-medium text-right">Payments</th>
                                    <th class="pb-3 font-medium">Status</th>
                                    <th class="pb-3 font-medium">Expires</th>
                                    <th class="pb-3 font-medium">Created</th>
                                    <th class="pb-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="code in unlockCodes.data" :key="code.id" class="border-b last:border-0">
                                    <td class="py-3">
                                        <code class="rounded bg-muted px-2 py-1 font-mono text-sm">{{ code.code }}</code>
                                    </td>
                                    <td class="py-3">
                                        {{ code.butcher_shop?.name || 'Any Shop' }}
                                    </td>
                                    <td class="py-3 text-right font-semibold">
                                        +{{ code.additional_payments }}
                                    </td>
                                    <td class="py-3">
                                        <Badge :variant="code.is_used ? 'secondary' : 'default'">
                                            {{ code.is_used ? 'Used' : 'Unused' }}
                                        </Badge>
                                    </td>
                                    <td class="py-3 text-sm">
                                        {{ code.expires_at ? formatDate(code.expires_at) : 'Never' }}
                                    </td>
                                    <td class="py-3 text-sm">
                                        {{ formatDate(code.created_at) }}
                                    </td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <Button variant="outline" size="sm" @click="copyCode(code.code)">
                                                Copy
                                            </Button>
                                            <Button 
                                                v-if="!code.is_used" 
                                                variant="destructive" 
                                                size="sm" 
                                                @click="deleteCode(code.id)"
                                            >
                                                Delete
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-center py-8 text-muted-foreground">
                        No unlock codes found. Click "Generate Codes" to create new codes.
                    </p>

                    <!-- Pagination -->
                    <div v-if="unlockCodes.last_page > 1" class="mt-4 flex justify-center gap-2">
                        <Link 
                            v-for="page in unlockCodes.last_page" 
                            :key="page"
                            :href="`/super-admin/unlock-codes?page=${page}&butcher_id=${butcherId}&status=${status}`"
                            :class="[
                                'px-3 py-1 rounded border',
                                page === unlockCodes.current_page ? 'bg-primary text-white' : 'hover:bg-muted'
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
