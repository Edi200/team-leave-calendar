import type { BadgeVariants } from '@/components/ui/badge'
import type { LeaveStatus } from '@/types/leaveRequest'

const STATUS_LABELS: Record<LeaveStatus, string> = {
  pending: 'Pending',
  approved: 'Approved',
  rejected: 'Rejected',
}

export function statusLabel(status: LeaveStatus): string {
  return STATUS_LABELS[status]
}

export function statusBadgeVariant(
  status: LeaveStatus,
): NonNullable<BadgeVariants['variant']> {
  switch (status) {
    case 'approved':
      return 'default'
    case 'rejected':
      return 'destructive'
    case 'pending':
      return 'outline'
  }
}

export function statusBadgeClass(status: LeaveStatus): string {
  switch (status) {
    case 'pending':
      return 'border-amber-500/50 text-amber-700 dark:text-amber-400'
    case 'approved':
      return 'border-transparent bg-emerald-600 text-white hover:bg-emerald-600/90'
    case 'rejected':
      return ''
  }
}

export function statusBarClass(status: LeaveStatus): string {
  switch (status) {
    case 'pending':
      return 'bg-amber-500/80'
    case 'approved':
      return 'bg-emerald-600'
    case 'rejected':
      return 'bg-destructive'
  }
}
