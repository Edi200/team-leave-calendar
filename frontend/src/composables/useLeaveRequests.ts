import { ref } from 'vue'

import { getErrorMessage } from '@/services/api'
import {
  createLeaveRequest,
  deleteLeaveRequest,
  fetchLeaveRequests,
  updateLeaveRequest,
} from '@/services/leaveRequestService'
import type {
  LeaveRequest,
  LeaveRequestFilters,
  LeaveRequestFormData,
  LeaveRequestUpdateData,
  LeaveStatus,
} from '@/types/leaveRequest'

const leaveRequests = ref<LeaveRequest[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const actionLoadingId = ref<number | null>(null)
const teamMemberId = ref<number | undefined>(undefined)
const status = ref<LeaveStatus | undefined>(undefined)

function buildFilterParams(): LeaveRequestFilters {
  const params: LeaveRequestFilters = {}

  if (teamMemberId.value !== undefined) {
    params.team_member_id = teamMemberId.value
  }

  if (status.value !== undefined) {
    params.status = status.value
  }

  return params
}

function upsertLeaveRequest(updated: LeaveRequest): void {
  const index = leaveRequests.value.findIndex((item) => item.id === updated.id)

  if (index === -1) {
    leaveRequests.value = [...leaveRequests.value, updated].sort((a, b) =>
      a.start_date.localeCompare(b.start_date),
    )
    return
  }

  const next = [...leaveRequests.value]
  next[index] = updated
  leaveRequests.value = next.sort((a, b) =>
    a.start_date.localeCompare(b.start_date),
  )
}

function removeLeaveRequest(id: number): void {
  leaveRequests.value = leaveRequests.value.filter((item) => item.id !== id)
}

export function useLeaveRequests() {
  async function fetch(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      leaveRequests.value = await fetchLeaveRequests(buildFilterParams())
    } catch (err) {
      error.value = getErrorMessage(err)
    } finally {
      loading.value = false
    }
  }

  async function fetchAll(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      leaveRequests.value = await fetchLeaveRequests()
    } catch (err) {
      error.value = getErrorMessage(err)
    } finally {
      loading.value = false
    }
  }

  async function create(
    payload: LeaveRequestFormData,
  ): Promise<LeaveRequest> {
    const created = await createLeaveRequest(payload)
    upsertLeaveRequest(created)
    return created
  }

  async function update(
    id: number,
    payload: LeaveRequestUpdateData,
  ): Promise<LeaveRequest> {
    const updated = await updateLeaveRequest(id, payload)
    upsertLeaveRequest(updated)
    return updated
  }

  async function remove(id: number): Promise<void> {
    actionLoadingId.value = id

    try {
      await deleteLeaveRequest(id)
      removeLeaveRequest(id)
    } finally {
      actionLoadingId.value = null
    }
  }

  async function approve(id: number): Promise<LeaveRequest> {
    actionLoadingId.value = id

    try {
      return await update(id, { status: 'approved' })
    } finally {
      actionLoadingId.value = null
    }
  }

  async function reject(id: number): Promise<LeaveRequest> {
    actionLoadingId.value = id

    try {
      return await update(id, { status: 'rejected' })
    } finally {
      actionLoadingId.value = null
    }
  }

  function clearFilters(): void {
    teamMemberId.value = undefined
    status.value = undefined
  }

  function retry(): Promise<void> {
    return fetch()
  }

  return {
    leaveRequests,
    loading,
    error,
    actionLoadingId,
    teamMemberId,
    status,
    fetch,
    fetchAll,
    create,
    update,
    remove,
    approve,
    reject,
    clearFilters,
    retry,
  }
}
