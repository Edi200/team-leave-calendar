import type { TeamMember } from './teamMember'

export type LeaveStatus = 'pending' | 'approved' | 'rejected'

export interface LeaveRequest {
  id: number
  team_member_id: number
  team_member: TeamMember
  start_date: string
  end_date: string
  reason: string
  status: LeaveStatus
  created_at: string
  updated_at: string
}

export interface LeaveRequestFormData {
  team_member_id: number
  start_date: string
  end_date: string
  reason: string
}

export interface LeaveRequestFilters {
  team_member_id?: number
  status?: LeaveStatus
}

export interface LeaveRequestUpdateData {
  team_member_id?: number
  start_date?: string
  end_date?: string
  reason?: string
  status?: LeaveStatus
}
