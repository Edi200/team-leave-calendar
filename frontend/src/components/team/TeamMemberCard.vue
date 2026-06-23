<script setup lang="ts">
import { computed } from 'vue'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Card, CardContent } from '@/components/ui/card'
import type { TeamMember } from '@/types/teamMember'
import { getInitials } from '@/utils/initials'

const props = defineProps<{
  member: TeamMember
}>()

const initials = computed(() => getInitials(props.member.name))
</script>

<template>
  <Card>
    <CardContent class="flex items-center gap-4">
      <Avatar class="size-12">
        <AvatarFallback class="text-sm font-medium">
          {{ initials }}
        </AvatarFallback>
      </Avatar>
      <p class="font-medium">{{ member.name }}</p>
    </CardContent>
  </Card>
</template>
