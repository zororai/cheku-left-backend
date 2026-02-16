<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';

interface Staff {
    id: number;
    name: string;
    email: string;
    username: string | null;
    role: string;
    is_active: boolean;
}

interface Props {
    staff: Staff;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/owner/staff' },
    { title: 'Edit', href: `/owner/staff/${props.staff.id}/edit` },
];

const form = useForm({
    name: props.staff.name,
    email: props.staff.email,
    username: props.staff.username || '',
    password: '',
    role: props.staff.role,
});

const submit = () => {
    form.put(`/owner/staff/${props.staff.id}`);
};
</script>

<template>
    <Head :title="`Edit - ${staff.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold">Edit Staff Member</h1>
                    <Badge :class="staff.is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                        {{ staff.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </div>
                <Link href="/owner/staff">
                    <Button variant="outline">Back to Staff</Button>
                </Link>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Staff Details</CardTitle>
                        <CardDescription>
                            Update staff member information. Leave password blank to keep current password.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Full Name *</Label>
                            <Input 
                                id="name" 
                                v-model="form.name" 
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
                            />
                            <p v-if="form.errors.username" class="text-sm text-red-500">{{ form.errors.username }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="password">New Password</Label>
                            <Input 
                                id="password" 
                                type="password"
                                v-model="form.password" 
                                placeholder="Leave blank to keep current password"
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
                            <p v-if="form.errors.role" class="text-sm text-red-500">{{ form.errors.role }}</p>
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            <Link href="/owner/staff">
                                <Button type="button" variant="outline">Cancel</Button>
                            </Link>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
