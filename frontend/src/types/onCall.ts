import type { TeamMember } from './teamMember'

export interface OnCallResponse {
  week_start: string
  week_end: string
  on_call_member: TeamMember
  conflict: boolean
}
