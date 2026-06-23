<script setup lang="ts">
import { CircleAlert, Loader2, Plus } from '@lucide/vue'
import { computed, onMounted, ref, watch } from 'vue'
import { toast } from 'vue-sonner'

import LeaveRequestActionsMenu from '@/components/leave/LeaveRequestActionsMenu.vue'
import LeaveRequestFormDialog from '@/components/leave/LeaveRequestFormDialog.vue'
import LeaveStatusBadge from '@/components/leave/LeaveStatusBadge.vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Skeleton } from '@/components/ui/skeleton'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { useLeaveRequests } from '@/composables/useLeaveRequests'
import { useTeamMembers } from '@/composables/useTeamMembers'
import { getErrorMessage } from '@/services/api'
import type { LeaveRequest, LeaveStatus } from '@/types/leaveRequest'
import { formatDateRange } from '@/utils/date'
import { statusLabel } from '@/utils/leaveStatus'

const {
  leaveRequests,
  loading,
  error,
  actionLoadingId,
  teamMemberId,
  status,
  fetch,
  remove,
  approve,
  reject,
  clearFilters,
  retry,
} = useLeaveRequests()

const { teamMembers, fetch: fetchTeamMembers } = useTeamMembers()

const dialogOpen = ref(false)
const dialogMode = ref<'create' | 'edit'>('create')
const editingRequest = ref<LeaveRequest | undefined>(undefined)
const deleteDialogOpen = ref(false)
const pendingDelete = ref<LeaveRequest | undefined>(undefined)
const deleting = ref(false)

const teamMemberFilter = computed({
  get: () =>
    teamMemberId.value !== undefined ? String(teamMemberId.value) : 'all',
  set: (value: string) => {
    teamMemberId.value = value === 'all' ? undefined : Number(value)
  },
})

const statusFilter = computed({
  get: () => status.value ?? 'all',
  set: (value: string) => {
    status.value = value === 'all' ? undefined : (value as LeaveStatus)
  },
})

const hasActiveFilters = computed(
  () => teamMemberId.value !== undefined || status.value !== undefined,
)

const skeletonRowCount = 5

onMounted(() => {
  void fetch()
  void fetchTeamMembers()
})

watch([teamMemberId, status], () => {
  void fetch()
})

function openCreateDialog(): void {
  dialogMode.value = 'create'
  editingRequest.value = undefined
  dialogOpen.value = true
}

function openEditDialog(request: LeaveRequest): void {
  dialogMode.value = 'edit'
  editingRequest.value = request
  dialogOpen.value = true
}

function onSaved(): void {
  toast.success(
    dialogMode.value === 'create'
      ? 'Leave request created'
      : 'Leave request saved',
  )
}

async function handleApprove(request: LeaveRequest): Promise<void> {
  try {
    await approve(request.id)
    toast.success('Leave request approved')
  } catch (err) {
    toast.error(getErrorMessage(err))
  }
}

async function handleReject(request: LeaveRequest): Promise<void> {
  try {
    await reject(request.id)
    toast.success('Leave request rejected')
  } catch (err) {
    toast.error(getErrorMessage(err))
  }
}

function openDeleteDialog(request: LeaveRequest): void {
  pendingDelete.value = request
  deleteDialogOpen.value = true
}

async function confirmDelete(): Promise<void> {
  if (!pendingDelete.value) {
    return
  }

  deleting.value = true

  try {
    await remove(pendingDelete.value.id)
    toast.success('Leave request deleted')
    deleteDialogOpen.value = false
    pendingDelete.value = undefined
  } catch (err) {
    toast.error(getErrorMessage(err))
  } finally {
    deleting.value = false
  }
}

function isRowLoading(requestId: number): boolean {
  return actionLoadingId.value === requestId
}
</script>

<template>
  <div class="space-y-6">
    <div
      class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
    >
      <div>
        <h2 class="text-2xl font-semibold tracking-tight">Leave Requests</h2>
        <p class="mt-1 text-sm text-muted-foreground">
          Review, approve, and manage team leave.
        </p>
      </div>
      <Button class="shrink-0" @click="openCreateDialog">
        <Plus />
        New Leave Request
      </Button>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
      <Select v-model="teamMemberFilter">
        <SelectTrigger class="w-full sm:w-48">
          <SelectValue placeholder="Team member" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All team members</SelectItem>
          <SelectItem
            v-for="member in teamMembers"
            :key="member.id"
            :value="String(member.id)"
          >
            {{ member.name }}
          </SelectItem>
        </SelectContent>
      </Select>

      <Select v-model="statusFilter">
        <SelectTrigger class="w-full sm:w-40">
          <SelectValue placeholder="Status" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All statuses</SelectItem>
          <SelectItem value="pending">{{ statusLabel('pending') }}</SelectItem>
          <SelectItem value="approved">
            {{ statusLabel('approved') }}
          </SelectItem>
          <SelectItem value="rejected">
            {{ statusLabel('rejected') }}
          </SelectItem>
        </SelectContent>
      </Select>
    </div>

    <div v-if="loading" class="space-y-4">
      <div class="hidden md:block">
        <div class="rounded-xl border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Team Member</TableHead>
                <TableHead>Dates</TableHead>
                <TableHead>Reason</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="w-16" />
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="index in skeletonRowCount" :key="index">
                <TableCell><Skeleton class="h-4 w-24" /></TableCell>
                <TableCell><Skeleton class="h-4 w-32" /></TableCell>
                <TableCell><Skeleton class="h-4 w-40" /></TableCell>
                <TableCell><Skeleton class="h-5 w-16 rounded-full" /></TableCell>
                <TableCell><Skeleton class="size-8 rounded-md" /></TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
      <div class="grid gap-4 md:hidden">
        <Card v-for="index in skeletonRowCount" :key="index">
          <CardHeader class="space-y-2">
            <Skeleton class="h-5 w-32" />
            <Skeleton class="h-4 w-48" />
          </CardHeader>
          <CardContent class="space-y-3">
            <Skeleton class="h-4 w-full" />
            <Skeleton class="h-5 w-16 rounded-full" />
          </CardContent>
        </Card>
      </div>
    </div>

    <div v-else-if="error" class="space-y-4">
      <Alert variant="destructive">
        <CircleAlert />
        <AlertTitle>Could not load leave requests</AlertTitle>
        <AlertDescription>{{ error }}</AlertDescription>
      </Alert>
      <Button variant="outline" @click="retry">Retry</Button>
    </div>

    <div
      v-else-if="leaveRequests.length === 0"
      class="rounded-xl border border-dashed p-8 text-center"
    >
      <p class="text-muted-foreground">
        {{
          hasActiveFilters
            ? 'No leave requests match your filters.'
            : 'No leave requests yet. Create the first one.'
        }}
      </p>
      <div class="mt-4 flex justify-center gap-2">
        <Button v-if="hasActiveFilters" variant="outline" @click="clearFilters">
          Clear filters
        </Button>
        <Button v-else @click="openCreateDialog">
          <Plus />
          New Leave Request
        </Button>
      </div>
    </div>

    <template v-else>
      <div class="hidden md:block">
        <div class="rounded-xl border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Team Member</TableHead>
                <TableHead>Dates</TableHead>
                <TableHead>Reason</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="w-16 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="request in leaveRequests"
                :key="request.id"
                :class="isRowLoading(request.id) && 'opacity-60'"
              >
                <TableCell class="font-medium">
                  <span class="inline-flex items-center gap-2">
                    {{ request.team_member.name }}
                    <Loader2
                      v-if="isRowLoading(request.id)"
                      class="size-4 animate-spin text-muted-foreground"
                    />
                  </span>
                </TableCell>
                <TableCell>
                  {{ formatDateRange(request.start_date, request.end_date) }}
                </TableCell>
                <TableCell class="max-w-xs truncate">
                  {{ request.reason }}
                </TableCell>
                <TableCell>
                  <LeaveStatusBadge :status="request.status" />
                </TableCell>
                <TableCell class="text-right">
                  <LeaveRequestActionsMenu
                    :request="request"
                    :disabled="isRowLoading(request.id)"
                    @edit="openEditDialog(request)"
                    @approve="handleApprove(request)"
                    @reject="handleReject(request)"
                    @delete="openDeleteDialog(request)"
                  />
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <div class="grid gap-4 md:hidden">
        <Card
          v-for="request in leaveRequests"
          :key="request.id"
          :class="isRowLoading(request.id) && 'opacity-60'"
        >
          <CardHeader
            class="flex flex-row items-start justify-between gap-4 space-y-0"
          >
            <div class="space-y-1">
              <CardTitle class="text-base">
                <span class="inline-flex items-center gap-2">
                  {{ request.team_member.name }}
                  <Loader2
                    v-if="isRowLoading(request.id)"
                    class="size-4 animate-spin text-muted-foreground"
                  />
                </span>
              </CardTitle>
              <p class="text-sm text-muted-foreground">
                {{ formatDateRange(request.start_date, request.end_date) }}
              </p>
            </div>
            <LeaveRequestActionsMenu
              :request="request"
              :disabled="isRowLoading(request.id)"
              @edit="openEditDialog(request)"
              @approve="handleApprove(request)"
              @reject="handleReject(request)"
              @delete="openDeleteDialog(request)"
            />
          </CardHeader>
          <CardContent class="space-y-3">
            <p class="text-sm">{{ request.reason }}</p>
            <LeaveStatusBadge :status="request.status" />
          </CardContent>
        </Card>
      </div>
    </template>

    <LeaveRequestFormDialog
      v-model:open="dialogOpen"
      :mode="dialogMode"
      :leave-request="editingRequest"
      @saved="onSaved"
    />

    <AlertDialog v-model:open="deleteDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete leave request?</AlertDialogTitle>
          <AlertDialogDescription>
            This will permanently delete
            {{
              pendingDelete
                ? `${pendingDelete.team_member.name}'s leave request (${formatDateRange(pendingDelete.start_date, pendingDelete.end_date)})`
                : 'this leave request'
            }}.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deleting">Cancel</AlertDialogCancel>
          <AlertDialogAction
            class="bg-destructive text-white hover:bg-destructive/90"
            :disabled="deleting"
            @click.prevent="confirmDelete"
          >
            <Loader2 v-if="deleting" class="animate-spin" />
            Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>
