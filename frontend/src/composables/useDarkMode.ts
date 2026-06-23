import { ref } from 'vue'

const THEME_STORAGE_KEY = 'theme'

const isDark = ref(false)

function readStoredTheme(): boolean | null {
  const stored = localStorage.getItem(THEME_STORAGE_KEY)

  if (stored === 'dark') {
    return true
  }

  if (stored === 'light') {
    return false
  }

  return null
}

function getSystemDark(): boolean {
  return window.matchMedia('(prefers-color-scheme: dark)').matches
}

function resolveIsDark(): boolean {
  const stored = readStoredTheme()

  if (stored !== null) {
    return stored
  }

  return getSystemDark()
}

function applyDarkMode(dark: boolean): void {
  document.documentElement.classList.toggle('dark', dark)
}

export function initDarkMode(): void {
  isDark.value = resolveIsDark()
  applyDarkMode(isDark.value)
}

export function useDarkMode() {
  function toggle(): void {
    isDark.value = !isDark.value
    applyDarkMode(isDark.value)
    localStorage.setItem(THEME_STORAGE_KEY, isDark.value ? 'dark' : 'light')
  }

  return {
    isDark,
    toggle,
  }
}
