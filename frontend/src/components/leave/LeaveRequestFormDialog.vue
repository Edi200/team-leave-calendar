<script setup lang="ts">
import { Calendar as CalendarIcon, Loader2 } from '@lucide/vue'
import { parseDate, type DateValue } from '@internationalized/date'
import { computed, reactive, ref, watch } from 'vue'

import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { useLeaveRequests } from '@/composables/useLeaveRequests'
import { useTeamMembers } from '@/composables/useTeamMembers'
import { getErrorMessage, getValidationErrors } from '@/services/api'
import type { LeaveRequest, LeaveRequestFormData } from '@/types/leaveRequest'
import { cn } from '@/lib/utils'
import { formatDisplayDate } from '@/utils/date'
import { toast } from 'vue-sonner'

const props = defineProps<{
  open: boolean
  mode: 'create' | 'edit'
  leaveRequest?: LeaveRequest
}>()

const emit = defineEmits<{
  saved: []
  'update:open': [value: boolean]
}>()

const { create, update } = useLeaveRequests()
const { teamMembers, fetch: fetchTeamMembers } = useTeamMembers()

const saving = ref(false)
const clientErrors = reactive<Record<string, string>>({})
const fieldErrors = ref<Record<string, string[]>>({})

const form = reactive<LeaveRequestFormData>({
  team_member_id: 0,
  start_date: '',
  end_date: '',
  reason: '',
})

const startDateOpen = ref(false)
const endDateOpen = ref(false)
const startDateValue = ref<DateValue>()
const endDateValue = ref<DateValue>()

const teamMemberSelectValue = computed({
  get: () =>
    form.team_member_id > 0 ? String(form.team_member_id) : undefined,
  set: (value: string | undefined) => {
    form.team_member_id = value ? Number(value) : 0
  },
})

const dialogTitle = computed(() =>
  props.mode === 'create' ? 'New Leave Request' : 'Edit Leave Request',
)

function resetForm(): void {
  form.team_member_id = 0
  form.start_date = ''
  form.end_date = ''
  form.reason = ''
  startDateValue.value = undefined
  endDateValue.value = undefined
  Object.keys(clientErrors).forEach((key) => {
    delete clientErrors[key]
  })
  fieldErrors.value = {}
}

function populateForm(request: LeaveRequest): void {
  form.team_member_id = request.team_member_id
  form.start_date = request.start_date
  form.end_date = request.end_date
  form.reason = request.reason
  startDateValue.value = parseDate(request.start_date)
  endDateValue.value = parseDate(request.end_date)
}

function fieldError(field: string): string | undefined {
  return (
    clientErrors[field] ??
    fieldErrors.value[field]?.[0]
  )
}

function validateClient(): boolean {
  Object.keys(clientErrors).forEach((key) => {
    delete clientErrors[key]
  })

  if (!form.team_member_id) {
    clientErrors.team_member_id = 'Team member is required.'
  }

  if (!form.start_date) {
    clientErrors.start_date = 'Start date is required.'
  }

  if (!form.end_date) {
    clientErrors.end_date = 'End date is required.'
  }

  if (
    form.start_date &&
    form.end_date &&
    form.end_date < form.start_date
  ) {
    clientErrors.end_date = 'End date must be on or after the start date.'
  }

  if (!form.reason.trim()) {
    clientErrors.reason = 'Reason is required.'
  }

  return Object.keys(clientErrors).length === 0
}

function closeDialog(): void {
  emit('update:open', false)
}

function onStartDateSelect(value: DateValue | undefined): void {
  if (!value) {
    return
  }

  form.start_date = value.toString()
  startDateValue.value = value
  startDateOpen.value = false

  if (form.end_date && form.end_date < form.start_date) {
    form.end_date = form.start_date
    endDateValue.value = value
  }
}

function onEndDateSelect(value: DateValue | undefined): void {
  if (!value) {
    return
  }

  form.end_date = value.toString()
  endDateValue.value = value
  endDateOpen.value = false
}

async function save(): Promise<void> {
  fieldErrors.value = {}

  if (!validateClient()) {
    return
  }

  saving.value = true

  try {
    const payload: LeaveRequestFormData = {
      team_member_id: form.team_member_id,
      start_date: form.start_date,
      end_date: form.end_date,
      reason: form.reason.trim(),
    }

    if (props.mode === 'create') {
      await create(payload)
    } else if (props.leaveRequest) {
      await update(props.leaveRequest.id, payload)
    }

    emit('saved')
    closeDialog()
  } catch (error) {
    const validationErrors = getValidationErrors(error)

    if (validationErrors) {
      fieldErrors.value = validationErrors
      return
    }

    toast.error(getErrorMessage(error))
  } finally {
    saving.value = false
  }
}

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      return
    }

    resetForm()

    if (props.mode === 'edit' && props.leaveRequest) {
      populateForm(props.leaveRequest)
    }

    if (teamMembers.value.length === 0) {
      void fetchTeamMembers()
    }
  },
)
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
        <DialogDescription>
          {{
            mode === 'create'
              ? 'Submit a new leave request for approval.'
              : 'Update this leave request.'
          }}
        </DialogDescription>
      </DialogHeader>

      <form class="space-y-4" @submit.prevent="save">
        <div class="space-y-2">
          <Label for="team-member">Team member</Label>
          <Select v-model="teamMemberSelectValue">
            <SelectTrigger id="team-member" class="w-full">
              <SelectValue placeholder="Select a team member" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="member in teamMembers"
                :key="member.id"
                :value="String(member.id)"
              >
                {{ member.name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <p
            v-if="fieldError('team_member_id')"
            class="text-sm text-destructive"
          >
            {{ fieldError('team_member_id') }}
          </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-2">
            <Label>Start date</Label>
            <Popover v-model:open="startDateOpen">
              <PopoverTrigger as-child>
                <Button
                  variant="outline"
                  :class="
                    cn(
                      'w-full justify-start text-left font-normal',
                      !form.start_date && 'text-muted-foreground',
                    )
                  "
                >
                  <CalendarIcon />
                  {{
                    form.start_date
                      ? formatDisplayDate(form.start_date)
                      : 'Pick a date'
                  }}
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0" align="start">
                <Calendar
                  :model-value="startDateValue"
                  @update:model-value="onStartDateSelect"
                />
              </PopoverContent>
            </Popover>
            <p
              v-if="fieldError('start_date')"
              class="text-sm text-destructive"
            >
              {{ fieldError('start_date') }}
            </p>
          </div>

          <div class="space-y-2">
            <Label>End date</Label>
            <Popover v-model:open="endDateOpen">
              <PopoverTrigger as-child>
                <Button
                  variant="outline"
                  :class="
                    cn(
                      'w-full justify-start text-left font-normal',
                      !form.end_date && 'text-muted-foreground',
                    )
                  "
                >
                  <CalendarIcon />
                  {{
                    form.end_date
                      ? formatDisplayDate(form.end_date)
                      : 'Pick a date'
                  }}
                </Button>
              </PopoverTrigger>
              <PopoverContent class="w-auto p-0" align="start">
                <Calendar
                  :model-value="endDateValue"
                  :min-value="startDateValue"
                  @update:model-value="onEndDateSelect"
                />
              </PopoverContent>
            </Popover>
            <p v-if="fieldError('end_date')" class="text-sm text-destructive">
              {{ fieldError('end_date') }}
            </p>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="reason">Reason</Label>
          <Textarea
            id="reason"
            v-model="form.reason"
            rows="3"
            placeholder="Why are you taking leave?"
          />
          <p v-if="fieldError('reason')" class="text-sm text-destructive">
            {{ fieldError('reason') }}
          </p>
        </div>

        <DialogFooter class="gap-2 sm:gap-0">
          <Button
            type="button"
            variant="outline"
            :disabled="saving"
            @click="closeDialog"
          >
            Cancel
          </Button>
          <Button type="submit" :disabled="saving">
            <Loader2 v-if="saving" class="animate-spin" />
            Save
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
