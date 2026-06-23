<script setup lang="ts">
import { Check, MoreHorizontal, Pencil, Trash2, X } from '@lucide/vue'

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type { LeaveRequest } from '@/types/leaveRequest'

defineProps<{
  request: LeaveRequest
  disabled?: boolean
}>()

const emit = defineEmits<{
  edit: []
  approve: []
  reject: []
  delete: []
}>()
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" size="icon" :disabled="disabled">
        <MoreHorizontal />
        <span class="sr-only">Actions</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuItem @click="emit('edit')">
        <Pencil />
        Edit
      </DropdownMenuItem>
      <DropdownMenuItem @click="emit('approve')">
        <Check />
        Approve
      </DropdownMenuItem>
      <DropdownMenuItem variant="destructive" @click="emit('reject')">
        <X />
        Reject
      </DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem variant="destructive" @click="emit('delete')">
        <Trash2 />
        Delete
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
