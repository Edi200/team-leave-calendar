<script setup lang="ts">
import AppShell from '@/components/layout/AppShell.vue'
import { Toaster } from '@/components/ui/sonner'
import { initDarkMode } from '@/composables/useDarkMode'

initDarkMode()
</script>

<template>
  <AppShell />
  <Toaster rich-colors close-button />
</template>
