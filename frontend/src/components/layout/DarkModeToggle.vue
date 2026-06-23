<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue'
import { computed } from 'vue'

import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { useDarkMode } from '@/composables/useDarkMode'

withDefaults(
  defineProps<{
    labeled?: boolean
  }>(),
  {
    labeled: false,
  },
)

const { isDark, toggle } = useDarkMode()

const themeLabel = computed(() => (isDark.value ? 'Light mode' : 'Dark mode'))
</script>

<template>
  <Button
    variant="ghost"
    :size="labeled ? 'default' : 'icon'"
    :class="
      cn(
        labeled &&
          'h-auto w-full justify-start gap-2 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground',
      )
    "
    @click="toggle"
  >
    <Sun v-if="isDark" class="size-4 shrink-0" />
    <Moon v-else class="size-4 shrink-0" />
    <span v-if="labeled">{{ themeLabel }}</span>
    <span v-else class="sr-only">Toggle dark mode</span>
  </Button>
</template>
