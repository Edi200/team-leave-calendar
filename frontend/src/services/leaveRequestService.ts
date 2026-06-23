import type { ApiCollectionResponse, ApiResourceResponse } from '@/types/api'
import type {
  LeaveRequest,
  LeaveRequestFilters,
  LeaveRequestFormData,
  LeaveRequestUpdateData,
} from '@/types/leaveRequest'

import { api } from './api'

export async function fetchLeaveRequests(
  params?: LeaveRequestFilters,
): Promise<LeaveRequest[]> {
  const { data } = await api.get<ApiCollectionResponse<LeaveRequest>>(
    '/leave-requests',
    { params },
  )

  return data.data
}

export async function createLeaveRequest(
  payload: LeaveRequestFormData,
): Promise<LeaveRequest> {
  const { data } = await api.post<ApiResourceResponse<LeaveRequest>>(
    '/leave-requests',
    payload,
  )

  return data.data
}

export async function updateLeaveRequest(
  id: number,
  payload: LeaveRequestUpdateData,
): Promise<LeaveRequest> {
  const { data } = await api.patch<ApiResourceResponse<LeaveRequest>>(
    `/leave-requests/${id}`,
    payload,
  )

  return data.data
}

export async function deleteLeaveRequest(id: number): Promise<void> {
  await api.delete(`/leave-requests/${id}`)
}
