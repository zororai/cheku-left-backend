<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/owner/staff' },
    { title: 'Add Staff', href: '/owner/staff/create' },
];

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    role: 'cashier',
});

const submit = () => {
    form.post('/owner/staff');
};
</script>

<template>
    <Head title="Add Staff Member" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Add Staff Member</h1>
                <Link href="/owner/staff">
                    <Button variant="outline">Cancel</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Staff Details</CardTitle>
                        <CardDescription>
                            These credentials will be used by the staff member to log in on the mobile app.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Full Name *</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
                                placeholder="e.g., John Doe"
                                required
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email Address *</Label>
                            <Input 
                                id="email" 
                                type="email"
                                v-model="form.email" 
                                placeholder="e.g., john@example.com"
                                required
                            />
                            <p class="text-xs text-muted-foreground">Used to log in on the mobile app</p>
                            <p v-if="form.errors.email" class="text-sm text-red-500">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="username">Username (Optional)</Label>
                            <Input 
                                id="username" 
                                v-model="form.username" 
                                placeholder="e.g., johnd"
                            />
                            <p class="text-xs text-muted-foreground">Alternative login identifier</p>
                            <p v-if="form.errors.username" class="text-sm text-red-500">{{ form.errors.username }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">Password *</Label>
                            <Input 
                                id="password" 
                                type="password"
                                v-model="form.password" 
                                placeholder="Minimum 6 characters"
                                required
                            />
                            <p v-if="form.errors.password" class="text-sm text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="role">Role *</Label>
                            <select 
                                id="role"
                                v-model="form.role"
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                                required
                            >
                                <option value="cashier">Salesman / Cashier</option>
                                <option value="manager">Manager</option>
                            </select>
                            <p class="text-xs text-muted-foreground">
                                <strong>Salesman:</strong> Can make sales only. 
                                <strong>Manager:</strong> Can manage products and view reports.
                            </p>
                            <p v-if="form.errors.role" class="text-sm text-red-500">{{ form.errors.role }}</p>
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            <Link href="/owner/staff">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Adding...' : 'Add Staff Member' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
