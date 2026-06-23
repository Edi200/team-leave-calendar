import axios, { isAxiosError } from 'axios'

import type { ValidationErrorResponse } from '@/types/api'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

export function getErrorMessage(error: unknown): string {
  if (isAxiosError<{ message?: string }>(error)) {
    return error.response?.data?.message ?? error.message
  }

  if (error instanceof Error) {
    return error.message
  }

  return 'An unexpected error occurred'
}

export function getValidationErrors(
  error: unknown,
): Record<string, string[]> | null {
  if (
    isAxiosError<ValidationErrorResponse>(error) &&
    error.response?.status === 422
  ) {
    return error.response.data.errors ?? null
  }

  return null
}
