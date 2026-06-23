<script setup lang="ts">
import { CircleAlert } from '@lucide/vue'
import { onMounted } from 'vue'

import TeamMemberCard from '@/components/team/TeamMemberCard.vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { useTeamMembers } from '@/composables/useTeamMembers'

const { teamMembers, loading, error, fetch, retry } = useTeamMembers()

const skeletonCount = 4

onMounted(() => {
  void fetch()
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">Team Members</h2>
      <p class="mt-1 text-sm text-muted-foreground">
        People on the team and in the on-call rotation.
      </p>
    </div>

    <div
      v-if="loading"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
      <Card v-for="index in skeletonCount" :key="index">
        <CardContent class="flex items-center gap-4">
          <Skeleton class="size-12 shrink-0 rounded-full" />
          <Skeleton class="h-5 w-32" />
        </CardContent>
      </Card>
    </div>

    <div v-else-if="error" class="space-y-4">
      <Alert variant="destructive">
        <CircleAlert />
        <AlertTitle>Could not load team members</AlertTitle>
        <AlertDescription>{{ error }}</AlertDescription>
      </Alert>
      <Button variant="outline" @click="retry">Retry</Button>
    </div>

    <div
      v-else-if="teamMembers.length === 0"
      class="rounded-xl border border-dashed p-8 text-center text-muted-foreground"
    >
      No team members found.
    </div>

    <div
      v-else
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
      <TeamMemberCard
        v-for="member in teamMembers"
        :key="member.id"
        :member="member"
      />
    </div>
  </div>
</template>
