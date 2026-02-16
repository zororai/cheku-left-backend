<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Plan {
    id: number;
    name: string;
    price: string;
    duration_days: number;
}

interface Props {
    plans: Plan[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Butcher Shops', href: '/super-admin/butcher-shops' },
    { title: 'Create', href: '/super-admin/butcher-shops/create' },
];

const form = useForm({
    shop_name: '',
    shop_address: '',
    shop_phone: '',
    owner_name: '',
    owner_email: '',
    owner_password: '',
    plan_id: props.plans[0]?.id || '',
    subscription_months: 1,
});

const submit = () => {
    form.post('/super-admin/butcher-shops');
};
</script>

<template>
    <Head title="Create Butcher Shop" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Create Butcher Shop</h1>
                <Link href="/super-admin/butcher-shops">
                    <Button variant="outline">Cancel</Button>
                </Link>
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
                                placeholder="e.g., Premium Meats"
                                required
                            />
                            <p v-if="form.errors.shop_name" class="text-sm text-red-500">{{ form.errors.shop_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="shop_address">Address</Label>
                            <Input 
                                id="shop_address" 
                                v-model="form.shop_address" 
                                placeholder="e.g., 123 Main St, City"
                            />
                            <p v-if="form.errors.shop_address" class="text-sm text-red-500">{{ form.errors.shop_address }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="shop_phone">Phone</Label>
                            <Input 
                                id="shop_phone" 
                                v-model="form.shop_phone" 
                                placeholder="e.g., +1234567890"
                            />
                            <p v-if="form.errors.shop_phone" class="text-sm text-red-500">{{ form.errors.shop_phone }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Owner Details -->
                <Card>
                    <CardHeader>
                        <CardTitle>Owner Account</CardTitle>
                        <CardDescription>Login credentials for the shop owner</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="owner_name">Owner Name *</Label>
                            <Input 
                                id="owner_name" 
                                v-model="form.owner_name" 
                                placeholder="e.g., John Doe"
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
                                placeholder="e.g., owner@example.com"
                                required
                            />
                            <p v-if="form.errors.owner_email" class="text-sm text-red-500">{{ form.errors.owner_email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="owner_password">Password *</Label>
                            <Input 
                                id="owner_password" 
                                type="password"
                                v-model="form.owner_password" 
                                placeholder="Minimum 8 characters"
                                required
                            />
                            <p v-if="form.errors.owner_password" class="text-sm text-red-500">{{ form.errors.owner_password }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Subscription -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Subscription</CardTitle>
                        <CardDescription>Choose a plan and subscription duration</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="plan_id">Subscription Plan *</Label>
                                <select 
                                    id="plan_id"
                                    v-model="form.plan_id"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2"
                                    required
                                >
                                    <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                        {{ plan.name }} - ${{ plan.price }} ({{ plan.duration_days }} days)
                                    </option>
                                </select>
                                <p v-if="form.errors.plan_id" class="text-sm text-red-500">{{ form.errors.plan_id }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="subscription_months">Subscription Duration (Months) *</Label>
                                <Input 
                                    id="subscription_months" 
                                    type="number"
                                    v-model="form.subscription_months" 
                                    min="1"
                                    max="24"
                                    required
                                />
                                <p v-if="form.errors.subscription_months" class="text-sm text-red-500">{{ form.errors.subscription_months }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="md:col-span-2 flex justify-end gap-4">
                    <Link href="/super-admin/butcher-shops">
                        <Button type="button" variant="outline">Cancel</Button>
                    </Link>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Butcher Shop' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
