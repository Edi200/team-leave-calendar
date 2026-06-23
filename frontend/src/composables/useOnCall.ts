import { ref } from 'vue'

import { getErrorMessage } from '@/services/api'
import { fetchLeaveRequests } from '@/services/leaveRequestService'
import { fetchOnCall } from '@/services/onCallService'
import type { LeaveRequest } from '@/types/leaveRequest'
import type { OnCallResponse } from '@/types/onCall'
import { rangesOverlap, todayIsoDate } from '@/utils/date'

const onCall = ref<OnCallResponse | null>(null)
const conflictingLeaves = ref<LeaveRequest[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const week = ref(todayIsoDate())

async function loadConflictingLeaves(response: OnCallResponse): Promise<void> {
  if (!response.conflict) {
    conflictingLeaves.value = []
    return
  }

  const approvedLeaves = await fetchLeaveRequests({
    team_member_id: response.on_call_member.id,
    status: 'approved',
  })

  conflictingLeaves.value = approvedLeaves.filter((leave) =>
    rangesOverlap(
      leave.start_date,
      leave.end_date,
      response.week_start,
      response.week_end,
    ),
  )
}

export function useOnCall() {
  async function fetch(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const response = await fetchOnCall(week.value)
      onCall.value = response
      await loadConflictingLeaves(response)
    } catch (err) {
      error.value = getErrorMessage(err)
      onCall.value = null
      conflictingLeaves.value = []
    } finally {
      loading.value = false
    }
  }

  function retry(): Promise<void> {
    return fetch()
  }

  return {
    onCall,
    conflictingLeaves,
    loading,
    error,
    week,
    fetch,
    retry,
  }
}
