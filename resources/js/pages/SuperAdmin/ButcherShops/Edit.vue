<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';

interface Plan {
    id: number;
    name: string;
    price: string;
    duration_days: number;
}

interface ButcherShop {
    id: number;
    name: string;
    address: string;
    phone: string;
    subscription_status: string;
    subscription_end_date: string;
    plan_id: number;
    owner?: { id: number; name: string; email: string; is_active: boolean };
}

interface Props {
    butcherShop: ButcherShop;
    plans: Plan[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Butcher Shops', href: '/super-admin/butcher-shops' },
    { title: 'Edit', href: `/super-admin/butcher-shops/${props.butcherShop.id}/edit` },
];

const form = useForm({
    shop_name: props.butcherShop.name,
    shop_address: props.butcherShop.address || '',
    shop_phone: props.butcherShop.phone || '',
    owner_name: props.butcherShop.owner?.name || '',
    owner_email: props.butcherShop.owner?.email || '',
    plan_id: props.butcherShop.plan_id,
});

const extendForm = useForm({
    months: 1,
});

const submit = () => {
    form.put(`/super-admin/butcher-shops/${props.butcherShop.id}`);
};

const toggleStatus = () => {
    const action = props.butcherShop.subscription_status === 'suspended' ? 'activate' : 'suspend';
    if (confirm(`Are you sure you want to ${action} this butcher shop?`)) {
        router.post(`/super-admin/butcher-shops/${props.butcherShop.id}/toggle-status`);
    }
};

const extendSubscription = () => {
    extendForm.post(`/super-admin/butcher-shops/${props.butcherShop.id}/extend-subscription`);
};

const statusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-500 text-white';
        case 'expired': return 'bg-yellow-500 text-white';
        case 'suspended': return 'bg-red-500 text-white';
        default: return 'bg-gray-500 text-white';
    }
};
</script>

<template>
    <Head :title="`Edit - ${butcherShop.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold">{{ butcherShop.name }}</h1>
                    <Badge :class="statusColor(butcherShop.subscription_status)">
                        {{ butcherShop.subscription_status }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button 
                        :variant="butcherShop.subscription_status === 'suspended' ? 'default' : 'destructive'"
                        @click="toggleStatus"
                    >
                        {{ butcherShop.subscription_status === 'suspended' ? 'Activate' : 'Suspend' }}
                    </Button>
                    <Link href="/super-admin/butcher-shops">
                        <Button variant="outline">Back to List</Button>
                    </Link>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6 md:grid-cols-2">
                <!-- Shop Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Shop Details</CardTitle>
                        <CardDescription>Basic information about the butcher shop</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="shop_name">Shop Name *</Label>
                            <Input 
                                id="shop_name" 
                                v-model="form.shop_name" 
                                required
                            />
                            <p v-if="form.errors.shop_name" class="text-sm text-red-500">{{ form.errors.shop_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="shop_address">Address</Label>
                            <Input 
                                id="shop_address" 
                                v-model="form.shop_address" 
                            />
                            <p v-if="form.errors.shop_address" class="text-sm text-red-500">{{ form.errors.shop_address }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="shop_phone">Phone</Label>
                            <Input 
                                id="shop_phone" 
                                v-model="form.shop_phone" 
                            />
                            <p v-if="form.errors.shop_phone" class="text-sm text-red-500">{{ form.errors.shop_phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="plan_id">Subscription Plan</Label>
                            <select 
                                id="plan_id"
                                v-model="form.plan_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ plan.name }} - ${{ plan.price }}
                                </option>
                            </select>
                            <p v-if="form.errors.plan_id" class="text-sm text-red-500">{{ form.errors.plan_id }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Owner Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Owner Account</CardTitle>
                        <CardDescription>Owner information (password not editable here)</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="owner_name">Owner Name *</Label>
                            <Input 
                                id="owner_name" 
                                v-model="form.owner_name" 
                                required
                            />
                            <p v-if="form.errors.owner_name" class="text-sm text-red-500">{{ form.errors.owner_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="owner_email">Owner Email *</Label>
                            <Input 
                                id="owner_email" 
                                type="email"
                                v-model="form.owner_email" 
                                required
                            />
                            <p v-if="form.errors.owner_email" class="text-sm text-red-500">{{ form.errors.owner_email }}</p>
                        </div>

                        <div class="rounded-lg border p-4 bg-muted/50">
                            <p class="text-sm"><strong>Subscription Expires:</strong> {{ butcherShop.subscription_end_date || 'Not set' }}</p>
                            <p class="text-sm mt-1"><strong>Owner Status:</strong> {{ butcherShop.owner?.is_active ? 'Active' : 'Inactive' }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="md:col-span-2 flex justify-end gap-4">
                    <Link href="/super-admin/butcher-shops">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </Button>
                </div>
            </form>

            <!-- Extend Subscription -->
            <Card>
                <CardHeader>
                    <CardTitle>Extend Subscription</CardTitle>
                    <CardDescription>Add more time to this shop's subscription</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="extendSubscription" class="flex items-end gap-4">
                        <div class="space-y-2">
                            <Label for="extend_months">Months to Add</Label>
                            <Input 
                                id="extend_months" 
                                type="number"
                                v-model="extendForm.months" 
                                min="1"
                                max="24"
                                class="w-32"
                            />
                        </div>
                        <Button type="submit" :disabled="extendForm.processing">
                            {{ extendForm.processing ? 'Extending...' : 'Extend Subscription' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
