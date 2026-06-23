<script setup lang="ts">
import {
  ChevronLeft,
  ChevronRight,
  CircleAlert,
  TriangleAlert,
} from '@lucide/vue'
import { computed, onMounted, watch } from 'vue'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { useOnCall } from '@/composables/useOnCall'
import { cn } from '@/lib/utils'
import {
  addDays,
  addWeeks,
  formatDateRange,
  parseIsoDate,
  startOfWeekMonday,
  todayIsoDate,
  toIsoDate,
} from '@/utils/date'
import { getInitials } from '@/utils/initials'

const { onCall, conflictingLeaves, loading, error, week, fetch, retry } =
  useOnCall()

const weekLabel = computed(() => {
  if (onCall.value) {
    return formatDateRange(onCall.value.week_start, onCall.value.week_end)
  }

  const monday = startOfWeekMonday(parseIsoDate(week.value))
  const sunday = addDays(monday, 6)

  return formatDateRange(toIsoDate(monday), toIsoDate(sunday))
})

const onCallInitials = computed(() =>
  onCall.value ? getInitials(onCall.value.on_call_member.name) : '',
)

function goToPreviousWeek(): void {
  week.value = toIsoDate(addWeeks(parseIsoDate(week.value), -1))
}

function goToNextWeek(): void {
  week.value = toIsoDate(addWeeks(parseIsoDate(week.value), 1))
}

function goToThisWeek(): void {
  week.value = todayIsoDate()
}

onMounted(() => {
  void fetch()
})

watch(week, () => {
  void fetch()
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">On-Call</h2>
      <p class="mt-1 text-sm text-muted-foreground">
        Who is on call this week and whether leave creates a conflict.
      </p>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-1">
        <Button variant="ghost" size="icon" @click="goToPreviousWeek">
          <ChevronLeft />
          <span class="sr-only">Previous week</span>
        </Button>
        <Button variant="ghost" size="icon" @click="goToNextWeek">
          <ChevronRight />
          <span class="sr-only">Next week</span>
        </Button>
        <p class="px-2 text-sm font-medium text-muted-foreground">
          {{ weekLabel }}
        </p>
      </div>

      <Button variant="outline" class="shrink-0" @click="goToThisWeek">
        This week
      </Button>
    </div>

    <div v-if="loading" class="space-y-4">
      <Card>
        <CardContent class="flex flex-col items-center gap-4 py-10">
          <Skeleton class="size-24 rounded-full" />
          <Skeleton class="h-8 w-48" />
          <Skeleton class="h-4 w-56" />
        </CardContent>
      </Card>
    </div>

    <div v-else-if="error" class="space-y-4">
      <Alert variant="destructive">
        <CircleAlert />
        <AlertTitle>Could not load on-call schedule</AlertTitle>
        <AlertDescription>{{ error }}</AlertDescription>
      </Alert>
      <Button variant="outline" @click="retry">Retry</Button>
    </div>

    <template v-else-if="onCall">
      <Card
        :class="
          cn(
            onCall.conflict && 'border-destructive bg-destructive/5',
          )
        "
      >
        <CardContent class="flex flex-col items-center gap-4 py-10 text-center">
          <Avatar class="size-24 text-2xl">
            <AvatarFallback class="text-2xl font-semibold">
              {{ onCallInitials }}
            </AvatarFallback>
          </Avatar>
          <div class="space-y-1">
            <p class="text-2xl font-semibold tracking-tight">
              {{ onCall.on_call_member.name }}
            </p>
            <p class="text-sm text-muted-foreground">
              On call {{ formatDateRange(onCall.week_start, onCall.week_end) }}
            </p>
          </div>
        </CardContent>
      </Card>

      <Alert v-if="onCall.conflict" variant="destructive">
        <TriangleAlert />
        <AlertTitle>On-call conflict</AlertTitle>
        <AlertDescription>
          <p class="mb-2">
            {{ onCall.on_call_member.name }} is scheduled on call but has
            approved leave overlapping this week:
          </p>
          <ul class="list-disc space-y-1 pl-4">
            <li
              v-for="leave in conflictingLeaves"
              :key="leave.id"
            >
              Approved leave
              {{ formatDateRange(leave.start_date, leave.end_date) }}
              <span v-if="leave.reason" class="text-destructive/90">
                — {{ leave.reason }}
              </span>
            </li>
          </ul>
        </AlertDescription>
      </Alert>
    </template>
  </div>
</template>
