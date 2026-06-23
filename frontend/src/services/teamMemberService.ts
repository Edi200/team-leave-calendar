import type { ApiCollectionResponse } from '@/types/api'
import type { TeamMember } from '@/types/teamMember'

import { api } from './api'

export async function fetchTeamMembers(): Promise<TeamMember[]> {
  const { data } = await api.get<ApiCollectionResponse<TeamMember>>(
    '/team-members',
  )

  return data.data
}
