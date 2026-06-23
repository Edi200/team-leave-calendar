import type { OnCallResponse } from '@/types/onCall'

import { api } from './api'

export async function fetchOnCall(week?: string): Promise<OnCallResponse> {
  const { data } = await api.get<OnCallResponse>('/on-call', {
    params: week ? { week } : undefined,
  })

  return data
}
