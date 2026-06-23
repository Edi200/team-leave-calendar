<script setup lang="ts">
import { ChevronLeft, ChevronRight, CircleAlert } from '@lucide/vue'
import { computed, onMounted, ref, watch } from 'vue'
import { toast } from 'vue-sonner'

import LeaveCalendarMonthView from '@/components/leave/LeaveCalendarMonthView.vue'
import LeaveRequestFormDialog from '@/components/leave/LeaveRequestFormDialog.vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { useLeaveRequests } from '@/composables/useLeaveRequests'
import type { LeaveRequest } from '@/types/leaveRequest'
import { addMonths, leaveRequestIntersectsMonth } from '@/utils/date'

const { leaveRequests, loading, error, fetchAll } = useLeaveRequests()

const now = new Date()
const viewYear = ref(now.getFullYear())
const viewMonth = ref(now.getMonth())

const dialogOpen = ref(false)
const editingRequest = ref<LeaveRequest | undefined>(undefined)

const monthLabel = computed(() =>
  new Date(viewYear.value, viewMonth.value, 1).toLocaleDateString(undefined, {
    month: 'long',
    year: 'numeric',
  }),
)

const monthLeaveRequests = computed(() =>
  leaveRequests.value.filter((request) =>
    leaveRequestIntersectsMonth(
      request.start_date,
      request.end_date,
      viewYear.value,
      viewMonth.value,
    ),
  ),
)

function loadMonthData(): void {
  void fetchAll()
}

function goToPreviousMonth(): void {
  const date = addMonths(new Date(viewYear.value, viewMonth.value, 1), -1)
  viewYear.value = date.getFullYear()
  viewMonth.value = date.getMonth()
}

function goToNextMonth(): void {
  const date = addMonths(new Date(viewYear.value, viewMonth.value, 1), 1)
  viewYear.value = date.getFullYear()
  viewMonth.value = date.getMonth()
}

function goToToday(): void {
  const today = new Date()
  viewYear.value = today.getFullYear()
  viewMonth.value = today.getMonth()
}

function openEditDialog(request: LeaveRequest): void {
  editingRequest.value = request
  dialogOpen.value = true
}

function onSaved(): void {
  toast.success('Leave request saved')
  loadMonthData()
}

onMounted(() => {
  loadMonthData()
})

watch([viewYear, viewMonth], () => {
  loadMonthData()
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">Calendar</h2>
      <p class="mt-1 text-sm text-muted-foreground">
        Monthly view of team leave requests.
      </p>
    </div>

    <div class="flex items-center justify-between gap-4">
      <div class="flex items-center gap-1">
        <Button variant="ghost" size="icon" @click="goToPreviousMonth">
          <ChevronLeft />
          <span class="sr-only">Previous month</span>
        </Button>
        <Button variant="ghost" size="icon" @click="goToNextMonth">
          <ChevronRight />
          <span class="sr-only">Next month</span>
        </Button>
      </div>

      <h3 class="text-lg font-semibold tracking-tight">
        {{ monthLabel }}
      </h3>

      <Button variant="outline" @click="goToToday">Today</Button>
    </div>

    <div v-if="loading" class="space-y-4">
      <Skeleton class="hidden h-112 w-full rounded-xl md:block" />
      <div class="space-y-4 md:hidden">
        <Skeleton class="h-24 w-full rounded-xl" />
        <Skeleton class="h-24 w-full rounded-xl" />
        <Skeleton class="h-24 w-full rounded-xl" />
      </div>
    </div>

    <div v-else-if="error" class="space-y-4">
      <Alert variant="destructive">
        <CircleAlert />
        <AlertTitle>Could not load calendar</AlertTitle>
        <AlertDescription>{{ error }}</AlertDescription>
      </Alert>
      <Button variant="outline" @click="loadMonthData">Retry</Button>
    </div>

    <LeaveCalendarMonthView
      v-else
      :year="viewYear"
      :month="viewMonth"
      :leave-requests="monthLeaveRequests"
      @select-request="openEditDialog"
    />

    <LeaveRequestFormDialog
      v-model:open="dialogOpen"
      mode="edit"
      :leave-request="editingRequest"
      @saved="onSaved"
    />
  </div>
</template>
