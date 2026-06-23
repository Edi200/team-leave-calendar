import { ref } from 'vue'

import { getErrorMessage } from '@/services/api'
import { fetchTeamMembers } from '@/services/teamMemberService'
import type { TeamMember } from '@/types/teamMember'

const teamMembers = ref<TeamMember[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

export function useTeamMembers() {
  async function fetch(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      teamMembers.value = await fetchTeamMembers()
    } catch (err) {
      error.value = getErrorMessage(err)
    } finally {
      loading.value = false
    }
  }

  function retry(): Promise<void> {
    return fetch()
  }

  return {
    teamMembers,
    loading,
    error,
    fetch,
    retry,
  }
}
