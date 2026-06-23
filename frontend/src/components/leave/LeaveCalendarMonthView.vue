<script setup lang="ts">
import { computed } from 'vue'

import LeaveStatusBadge from '@/components/leave/LeaveStatusBadge.vue'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import type { LeaveRequest } from '@/types/leaveRequest'
import {
  daysInMonthGrid,
  formatDisplayDate,
  leaveRequestOnDay,
  todayIsoDate,
  toIsoDate,
} from '@/utils/date'
import { statusBarClass } from '@/utils/leaveStatus'

const props = defineProps<{
  year: number
  month: number
  leaveRequests: LeaveRequest[]
}>()

const emit = defineEmits<{
  'select-request': [request: LeaveRequest]
}>()

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as const

const today = todayIsoDate()

const gridDays = computed(() => daysInMonthGrid(props.year, props.month))

const agendaDays = computed(() => {
  const lastDay = new Date(props.year, props.month + 1, 0).getDate()
  const days: Array<{ isoDate: string; requests: LeaveRequest[] }> = []

  for (let day = 1; day <= lastDay; day++) {
    const isoDate = toIsoDate(new Date(props.year, props.month, day))
    const requests = requestsOnDay(isoDate)

    if (requests.length > 0) {
      days.push({ isoDate, requests })
    }
  }

  return days
})

function requestsOnDay(isoDate: string): LeaveRequest[] {
  return props.leaveRequests.filter((request) =>
    leaveRequestOnDay(request.start_date, request.end_date, isoDate),
  )
}

function barRoundClass(request: LeaveRequest, isoDate: string): string {
  const isStart = isoDate === request.start_date
  const isEnd = isoDate === request.end_date

  if (isStart && isEnd) {
    return 'rounded-full'
  }

  if (isStart) {
    return 'rounded-l-full'
  }

  if (isEnd) {
    return 'rounded-r-full'
  }

  return 'rounded-none'
}

function barHoverClass(status: LeaveRequest['status']): string {
  switch (status) {
    case 'pending':
      return 'hover:bg-amber-500/70'
    case 'approved':
      return 'hover:bg-emerald-600/90'
    case 'rejected':
      return 'hover:bg-destructive/90'
  }
}

function selectRequest(request: LeaveRequest): void {
  emit('select-request', request)
}
</script>

<template>
  <div>
    <div class="hidden overflow-hidden rounded-xl border md:grid md:grid-cols-7">
      <div
        v-for="weekday in weekdays"
        :key="weekday"
        class="border-b bg-muted/50 px-2 py-2 text-center text-xs font-medium text-muted-foreground"
      >
        {{ weekday }}
      </div>

      <div
        v-for="(day, dayIndex) in gridDays"
        :key="day.isoDate"
        :class="
          cn(
            'min-h-28 border-b border-r p-2',
            (dayIndex + 1) % 7 === 0 && 'border-r-0',
            !day.inCurrentMonth && 'bg-muted/20',
          )
        "
      >
        <span
          :class="
            cn(
              'inline-flex size-7 items-center justify-center rounded-full text-sm',
              !day.inCurrentMonth && 'text-muted-foreground',
              day.isoDate === today &&
                day.inCurrentMonth &&
                'bg-primary font-medium text-primary-foreground',
            )
          "
        >
          {{ day.date.getDate() }}
        </span>

        <div class="mt-1 space-y-1">
          <Button
            v-for="request in requestsOnDay(day.isoDate)"
            :key="`${day.isoDate}-${request.id}`"
            variant="ghost"
            :class="
              cn(
                'h-5 w-full justify-start px-1.5 py-0 text-[10px] font-normal',
                statusBarClass(request.status),
                barRoundClass(request, day.isoDate),
                barHoverClass(request.status),
              )
            "
            :title="`${request.team_member.name}: ${request.reason}`"
            @click="selectRequest(request)"
          >
            <span
              v-if="day.isoDate === request.start_date"
              class="truncate text-white"
            >
              {{ request.team_member.name }}
            </span>
          </Button>
        </div>
      </div>
    </div>

    <div class="space-y-4 md:hidden">
      <div
        v-if="agendaDays.length === 0"
        class="rounded-xl border border-dashed p-6 text-center text-sm text-muted-foreground"
      >
        No leave scheduled this month.
      </div>

      <div
        v-for="day in agendaDays"
        :key="day.isoDate"
        class="rounded-xl border"
      >
        <div
          class="border-b bg-muted/50 px-4 py-2 text-sm font-medium"
          :class="day.isoDate === today && 'text-primary'"
        >
          {{ formatDisplayDate(day.isoDate) }}
        </div>
        <div class="divide-y">
          <Button
            v-for="request in day.requests"
            :key="request.id"
            variant="ghost"
            class="h-auto w-full justify-start gap-3 rounded-none px-4 py-3 text-left"
            @click="selectRequest(request)"
          >
            <span
              :class="
                cn('size-2 shrink-0 rounded-full', statusBarClass(request.status))
              "
            />
            <span class="min-w-0 flex-1">
              <span class="block truncate font-medium">
                {{ request.team_member.name }}
              </span>
              <span class="block truncate text-sm text-muted-foreground">
                {{ request.reason }}
              </span>
            </span>
            <LeaveStatusBadge :status="request.status" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>
