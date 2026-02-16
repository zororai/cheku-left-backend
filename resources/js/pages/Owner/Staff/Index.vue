<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

interface Staff {
    id: number;
    name: string;
    email: string;
    username: string | null;
    role: string;
    is_active: boolean;
    created_at: string;
}

interface Props {
    staff: Staff[];
    butcherShop?: { name: string };
    error?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff', href: '/owner/staff' },
];

const toggleStatus = (member: Staff) => {
    const action = member.is_active ? 'deactivate' : 'activate';
    if (confirm(`Are you sure you want to ${action} "${member.name}"?`)) {
        router.post(`/owner/staff/${member.id}/toggle-status`);
    }
};

const deleteStaff = (member: Staff) => {
    if (confirm(`Are you sure you want to remove "${member.name}"? This cannot be undone.`)) {
        router.delete(`/owner/staff/${member.id}`);
    }
};

const roleLabel = (role: string) => {
    switch (role) {
        case 'manager': return 'Manager';
        case 'cashier': return 'Salesman';
        default: return role;
    }
};
</script>

<template>
    <Head title="Staff Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Staff Management</h1>
                    <p v-if="butcherShop" class="text-muted-foreground">{{ butcherShop.name }}</p>
                </div>
                <Link href="/owner/staff/create">
                    <Button>Add Staff Member</Button>
                </Link>
            </div>

            <div v-if="error" class="rounded-lg bg-red-100 p-4 text-red-700">
                {{ error }}
            </div>

            <Card v-else>
                <CardHeader>
                    <CardTitle>All Staff ({{ staff.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="staff.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b">
                                    <th class="pb-3 font-medium">Name</th>
                                    <th class="pb-3 font-medium">Email / Username</th>
                                    <th class="pb-3 font-medium">Role</th>
                                    <th class="pb-3 font-medium">Status</th>
                                    <th class="pb-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in staff" :key="member.id" class="border-b last:border-0">
                                    <td class="py-3 font-medium">{{ member.name }}</td>
                                    <td class="py-3">
                                        <div>
                                            <p>{{ member.email }}</p>
                                            <p v-if="member.username" class="text-sm text-muted-foreground">@{{ member.username }}</p>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <Badge variant="outline">{{ roleLabel(member.role) }}</Badge>
                                    </td>
                                    <td class="py-3">
                                        <Badge :class="member.is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                                            {{ member.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </td>
                                    <td class="py-3">
                                        <div class="flex gap-2">
                                            <Link :href="`/owner/staff/${member.id}/edit`">
                                                <Button variant="outline" size="sm">Edit</Button>
                                            </Link>
                                            <Button 
                                                :variant="member.is_active ? 'secondary' : 'default'" 
                                                size="sm"
                                                @click="toggleStatus(member)"
                                            >
                                                {{ member.is_active ? 'Deactivate' : 'Activate' }}
                                            </Button>
                                            <Button 
                                                variant="destructive" 
                                                size="sm"
                                                @click="deleteStaff(member)"
                                            >
                                                Remove
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8">
                        <p class="text-muted-foreground mb-4">No staff members yet.</p>
                        <Link href="/owner/staff/create">
                            <Button>Add Your First Staff Member</Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>How Staff Login Works</CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground space-y-2">
                    <p><strong>For Mobile App (Flutter):</strong> Staff members use their <strong>email</strong> and <strong>password</strong> to log in via the API.</p>
                    <p><strong>Username:</strong> Optional, can be used as an alternative login identifier.</p>
                    <p><strong>Deactivated staff</strong> cannot log in until you activate them again.</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
