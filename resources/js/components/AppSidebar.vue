<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ClipboardList, CreditCard, Key, LayoutGrid, Receipt, Store, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { dashboard } from '@/routes';

const page = usePage();
const user = computed(() => page.props.auth?.user as { role?: string } | undefined);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (user.value?.role === 'super_admin') {
        items.push({
            title: 'Butcher Shops',
            href: '/super-admin/butcher-shops',
            icon: Store,
        });
        items.push({
            title: 'Unlock Codes',
            href: '/super-admin/unlock-codes',
            icon: Key,
        });
    }

    if (user.value?.role === 'owner' || user.value?.role === 'manager') {
        items.push({
            title: 'Staff',
            href: '/owner/staff',
            icon: Users,
        });
        items.push({
            title: 'Sales',
            href: '/owner/sales',
            icon: Receipt,
        });
        items.push({
            title: 'Stock Sessions',
            href: '/owner/stock-sessions',
            icon: ClipboardList,
        });
        items.push({
            title: 'License',
            href: '/owner/license',
            icon: CreditCard,
        });
    }

    return items;
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
