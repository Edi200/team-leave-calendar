<script setup lang="ts">
import { Calendar, LogOut, Menu, PhoneCall, Users } from '@lucide/vue'
import type { Component } from 'vue'
import { computed, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'

import DarkModeToggle from '@/components/layout/DarkModeToggle.vue'
import { Button } from '@/components/ui/button'
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import { cn } from '@/lib/utils'

const route = useRoute()
const sheetOpen = ref(false)

const navItems: Array<{
  name: string
  label: string
  to: string
  icon: Component
}> = [
  { name: 'team-members', label: 'Team Members', to: '/', icon: Users },
  {
    name: 'leave-requests',
    label: 'Leave Requests',
    to: '/leave-requests',
    icon: LogOut,
  },
  { name: 'calendar', label: 'Calendar', to: '/calendar', icon: Calendar },
  { name: 'on-call', label: 'On-Call', to: '/on-call', icon: PhoneCall },
]

watch(
  () => route.path,
  () => {
    sheetOpen.value = false
  },
)

function navLinkClass(isActive: boolean): string {
  return cn(
    'inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors',
    isActive
      ? 'bg-accent text-accent-foreground'
      : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
  )
}

const pageTitle = computed(() => {
  return (
    navItems.find((item) => item.name === route.name)?.label ??
    'Team Leave Calendar'
  )
})
</script>

<template>
  <div class="min-h-screen bg-background">
    <header class="border-b">
      <div class="mx-auto flex h-14 max-w-7xl items-center gap-4 px-4">
        <Sheet v-model:open="sheetOpen">
          <SheetTrigger as-child>
            <Button variant="ghost" size="icon" class="md:hidden">
              <Menu />
              <span class="sr-only">Open navigation</span>
            </Button>
          </SheetTrigger>
          <SheetContent side="left" class="w-72">
            <SheetHeader>
              <SheetTitle>Navigation</SheetTitle>
            </SheetHeader>
            <nav class="flex flex-col gap-1 px-2">
              <RouterLink
                v-for="item in navItems"
                :key="item.name"
                v-slot="{ isActive, href, navigate }"
                :to="item.to"
                custom
              >
                <a
                  :href="href"
                  :class="navLinkClass(isActive)"
                  @click="navigate"
                >
                  <component :is="item.icon" class="size-4 shrink-0" />
                  {{ item.label }}
                </a>
              </RouterLink>
              <DarkModeToggle labeled />
            </nav>
          </SheetContent>
        </Sheet>

        <h1 class="text-lg font-semibold tracking-tight">
          Team Leave Calendar
        </h1>

        <nav class="ml-auto hidden items-center gap-1 md:flex">
          <RouterLink
            v-for="item in navItems"
            :key="item.name"
            v-slot="{ isActive, href, navigate }"
            :to="item.to"
            custom
          >
            <a
              :href="href"
              :class="navLinkClass(isActive)"
              @click="navigate"
            >
              <component :is="item.icon" class="size-4 shrink-0" />
              {{ item.label }}
            </a>
          </RouterLink>
        </nav>

        <div class="hidden md:block">
          <DarkModeToggle />
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
      <h2 class="sr-only">{{ pageTitle }}</h2>
      <RouterView />
    </main>
  </div>
</template>
