<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { cn } from '@/lib/utils'
import type { LeaveStatus } from '@/types/leaveRequest'
import {
  statusBadgeClass,
  statusBadgeVariant,
  statusLabel,
} from '@/utils/leaveStatus'

defineProps<{
  status: LeaveStatus
}>()
</script>

<template>
  <Badge
    :variant="statusBadgeVariant(status)"
    :class="cn(statusBadgeClass(status))"
  >
    {{ statusLabel(status) }}
  </Badge>
</template>
