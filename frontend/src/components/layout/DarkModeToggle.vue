<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue'

import { Button } from '@/components/ui/button'
import { useDarkMode } from '@/composables/useDarkMode'

const { isDark, toggle } = useDarkMode()
</script>

<template>
  <Button variant="ghost" size="icon" @click="toggle">
    <Sun v-if="isDark" />
    <Moon v-else />
    <span class="sr-only">Toggle dark mode</span>
  </Button>
</template>
