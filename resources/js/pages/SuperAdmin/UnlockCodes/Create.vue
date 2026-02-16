<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface ButcherShop {
    id: number;
    name: string;
}

interface Props {
    butcherShops: ButcherShop[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Unlock Codes', href: '/super-admin/unlock-codes' },
    { title: 'Generate', href: '/super-admin/unlock-codes/create' },
];

const form = useForm({
    butcher_id: '',
    additional_payments: 50,
    quantity: 1,
    expires_at: '',
});

const submit = () => {
    form.post('/super-admin/unlock-codes');
};
</script>

<template>
    <Head title="Generate Unlock Codes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Generate Unlock Codes</h1>
                <Link href="/super-admin/unlock-codes">
                    <Button variant="outline">Back to List</Button>
                </Link>
            </div>

            <Card class="max-w-2xl">
                <CardHeader>
                    <CardTitle>New Unlock Codes</CardTitle>
                    <CardDescription>
                        Generate unlock codes for butcher owners to extend their payment limits.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="butcher_id">Butcher Shop (Optional)</Label>
                            <select 
                                id="butcher_id"
                                v-model="form.butcher_id"
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">Any Shop (Universal Code)</option>
                                <option v-for="shop in butcherShops" :key="shop.id" :value="shop.id">
                                    {{ shop.name }}
                                </option>
                            </select>
                            <p class="text-sm text-muted-foreground">
                                Leave empty to create a universal code that can be used by any shop.
                            </p>
                            <InputError :message="form.errors.butcher_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="additional_payments">Additional Payments</Label>
                            <Input 
                                id="additional_payments"
                                type="number"
                                v-model="form.additional_payments"
                                min="1"
                                max="10000"
                                required
                            />
                            <p class="text-sm text-muted-foreground">
                                Number of additional transactions this code will add to the license limit.
                            </p>
                            <InputError :message="form.errors.additional_payments" />
                        </div>

                        <div class="space-y-2">
                            <Label for="quantity">Number of Codes to Generate</Label>
                            <Input 
                                id="quantity"
                                type="number"
                                v-model="form.quantity"
                                min="1"
                                max="100"
                                required
                            />
                            <p class="text-sm text-muted-foreground">
                                Generate multiple codes at once (max 100).
                            </p>
                            <InputError :message="form.errors.quantity" />
                        </div>

                        <div class="space-y-2">
                            <Label for="expires_at">Expiration Date (Optional)</Label>
                            <Input 
                                id="expires_at"
                                type="date"
                                v-model="form.expires_at"
                            />
                            <p class="text-sm text-muted-foreground">
                                Leave empty for codes that never expire.
                            </p>
                            <InputError :message="form.errors.expires_at" />
                        </div>

                        <div class="flex gap-4 pt-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Generating...' : 'Generate Codes' }}
                            </Button>
                            <Link href="/super-admin/unlock-codes">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
