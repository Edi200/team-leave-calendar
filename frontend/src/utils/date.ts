const ISO_DATE_RE = /^\d{4}-\d{2}-\d{2}$/

export function parseIsoDate(isoDate: string): Date {
  const [year, month, day] = isoDate.split('-').map(Number)
  return new Date(year!, month! - 1, day)
}

export function toIsoDate(date: Date): string {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export function formatDisplayDate(isoDate: string): string {
  return parseIsoDate(isoDate).toLocaleDateString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

export function formatDateRange(startDate: string, endDate: string): string {
  if (startDate === endDate) {
    return formatDisplayDate(startDate)
  }

  return `${formatDisplayDate(startDate)} – ${formatDisplayDate(endDate)}`
}

export function startOfWeekMonday(date: Date): Date {
  const result = new Date(date.getFullYear(), date.getMonth(), date.getDate())
  const day = result.getDay()
  const diff = day === 0 ? -6 : 1 - day
  result.setDate(result.getDate() + diff)
  return result
}

export function addDays(date: Date, days: number): Date {
  const result = new Date(date.getFullYear(), date.getMonth(), date.getDate())
  result.setDate(result.getDate() + days)
  return result
}

export function addWeeks(date: Date, weeks: number): Date {
  return addDays(date, weeks * 7)
}

export function addMonths(date: Date, months: number): Date {
  return new Date(date.getFullYear(), date.getMonth() + months, date.getDate())
}

export function rangesOverlap(
  startA: string,
  endA: string,
  startB: string,
  endB: string,
): boolean {
  return startA <= endB && endA >= startB
}

export function eachDayInRange(startDate: string, endDate: string): string[] {
  const days: string[] = []
  let current = parseIsoDate(startDate)
  const end = parseIsoDate(endDate)

  while (current <= end) {
    days.push(toIsoDate(current))
    current = addDays(current, 1)
  }

  return days
}

export interface CalendarDay {
  date: Date
  isoDate: string
  inCurrentMonth: boolean
}

export function daysInMonthGrid(year: number, month: number): CalendarDay[] {
  const firstOfMonth = new Date(year, month, 1)
  const gridStart = startOfWeekMonday(firstOfMonth)
  const days: CalendarDay[] = []

  for (let i = 0; i < 42; i++) {
    const date = addDays(gridStart, i)
    days.push({
      date,
      isoDate: toIsoDate(date),
      inCurrentMonth: date.getMonth() === month,
    })
  }

  return days
}

export function isIsoDate(value: string): boolean {
  return ISO_DATE_RE.test(value)
}

export function todayIsoDate(): string {
  return toIsoDate(new Date())
}

export function leaveRequestIntersectsMonth(
  startDate: string,
  endDate: string,
  year: number,
  month: number,
): boolean {
  const monthStart = toIsoDate(new Date(year, month, 1))
  const monthEnd = toIsoDate(new Date(year, month + 1, 0))
  return rangesOverlap(startDate, endDate, monthStart, monthEnd)
}

export function leaveRequestOnDay(
  startDate: string,
  endDate: string,
  isoDate: string,
): boolean {
  return isoDate >= startDate && isoDate <= endDate
}
