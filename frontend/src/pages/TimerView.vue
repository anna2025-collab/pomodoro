<script setup>
import {computed, onMounted, ref} from 'vue'

const emit = defineEmits(['logout', 'show-login'])
const totalSeconds = ref(25 * 60)
const isRunning = ref(false)
const intervalId = ref(null)
const endTime = ref(null)
const currentMode = ref('focus')
const completedFocusSessions = ref(Number(localStorage.getItem('completedFocusSessions') ||
    0))
const totalFocusSeconds = ref(0)
const statusMessage = ref('')
let completionAudioContext = null

const getCompletionAudioContext = () => {
  const AudioContextClass = window.AudioContext || window.webkitAudioContext

  if (!AudioContextClass) {
    return null
  }

  if (!completionAudioContext) {
    completionAudioContext = new AudioContextClass()
  }

  return completionAudioContext
}

const unlockCompletionSound = async () => {
  const audioContext = getCompletionAudioContext()

  if (!audioContext) {
    return
  }

  if (audioContext.state === 'suspended') {
    await audioContext.resume()
  }
}

const playCompletionSound = async () => {
  const audioContext = getCompletionAudioContext()

  if (!audioContext) {
    return
  }

  if (audioContext.state === 'suspended') {
    await audioContext.resume()
  }

  const now = audioContext.currentTime
  const tones = [
    { start: 0, frequency: 880 },
    { start: 0.18, frequency: 988 },
    { start: 0.36, frequency: 1175 },
  ]

  tones.forEach(({ start, frequency }) => {
    const oscillator = audioContext.createOscillator()
    const gain = audioContext.createGain()

    oscillator.type = 'sine'
    oscillator.frequency.setValueAtTime(frequency, now + start)

    gain.gain.setValueAtTime(0.0001, now + start)
    gain.gain.exponentialRampToValueAtTime(0.18, now + start + 0.015)
    gain.gain.exponentialRampToValueAtTime(0.0001, now + start + 0.14)

    oscillator.connect(gain)
    gain.connect(audioContext.destination)

    oscillator.start(now + start)
    oscillator.stop(now + start + 0.16)
  })
}


const showProgress = ref(false)
const calendarDate = ref(new Date())
const focusSessions = ref([])
const selectedCalendarDay = ref(null)

const clearProgressStats = async () => {
  if (props.isAuth) {
    const token = localStorage.getItem('token')

    const response = await fetch(`${import.meta.env.VITE_API_URL}/focus-sessions`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    })

    if (!response.ok) {
      statusMessage.value = 'Не удалось очистить статистику'
      return
    }
  }

  focusSessions.value = []
  completedFocusSessions.value = 0
  totalFocusSeconds.value = 0
  todayFocusSessions.value = 0
  todayFocusSeconds.value = 0
  selectedCalendarDay.value = null
  localStorage.removeItem('completedFocusSessions')
}


const pageBackgroundClass = computed(() => {
  if (!isRunning.value) {
    return 'bg-gradient-to-br from-violet-900 via-purple-950 to-slate-950'
  }
  if (currentMode.value === 'focus') {
    return 'bg-gradient-to-r from-violet-500 via-emerald-200 to-violet-500'
  }
  return 'bg-gradient-to-r from-emerald-400 via-violet-200 to-violet-500'

})

const selectedCalendarMonthLabel = computed(() => {
  if (!selectedCalendarDay.value?.date) {
    return ''
  }

  return selectedCalendarDay.value.date.toLocaleDateString('ru-RU', {
    month: 'long',
  })
})

const selectCalendarDay = (day) => {
  if (!day.isCurrentMonth || !day.hasFocus) {
    return
  }

  selectedCalendarDay.value = day
}

const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const calendarMonthLabel = computed(() => {
  return calendarDate.value.toLocaleDateString('ru-RU', {
    month: 'long',
  })
})

const todayFocusSeconds = ref(0)
const todayFocusSessions = ref(0)

const todayFocusMinutes = computed(() => {
  return Math.floor(todayFocusSeconds.value / 60)
})


const props = defineProps({
  isAuth: {
    type: Boolean,
    default: false,
  },
})

const modes = {
  focus: {
    label: 'Фокус',
    seconds: 25 * 60,
  },
  shortBreak: {
    label: 'Короткий перерыв',
    seconds: 5 * 60,
  },
  longBreak: {
    label: 'Длинный перерыв',
    seconds: 15 * 60,
  },
}
const formattedTime = computed(() => {
  const minutes = Math.floor(totalSeconds.value / 60)
  const seconds = totalSeconds.value % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})
const totalFocusMinutes = computed(() => {
  return Math.floor(totalFocusSeconds.value / 60)
})
const focusSessionsByDate = computed(() => {
  return focusSessions.value.reduce((days, session) => {
    const date = session.completed_at?.slice(0, 10)

    if (!date) {
      return days
    }

    if (!days[date]) {
      days[date] = {
        sessions: 0,
        seconds: 0,
      }
    }

    days[date].sessions++
    days[date].seconds += session.duration_seconds

    return days
  }, {})
})

const startTimer = () => {
  if (isRunning.value) {
    return
  }
  void unlockCompletionSound()

  isRunning.value = true
  endTime.value = Date.now() + totalSeconds.value * 1000

  intervalId.value = setInterval(() => {
    const remaining = Math.ceil((endTime.value - Date.now()) / 1000)

    if (remaining <= 0) {
      totalSeconds.value = 0
      completeTimer()
      return
    }

    totalSeconds.value = remaining
  }, 1000)
}


const pauseTimer = () => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false
  endTime.value = null
}

const toggleTimer = () => {
  if (isRunning.value) {
    pauseTimer()
    return
  }

  startTimer()
}
const selectMode = (mode) => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false
  currentMode.value = mode
  totalSeconds.value = modes[mode].seconds
  statusMessage.value = ''
  endTime.value = null
}
const completeTimer = async () => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false
  endTime.value = null
  void playCompletionSound()

  if (currentMode.value === 'focus') {
    completedFocusSessions.value++
    await saveFocusSession()

    if (completedFocusSessions.value % 4 === 0) {
      selectMode('longBreak')
      statusMessage.value = 'Фокус завершён. Время длинного перерыва.'
      return
    }

    selectMode('shortBreak')
    statusMessage.value = 'Фокус завершён. Время короткого перерыва.'
    return;
  }
  selectMode('focus')
  statusMessage.value = 'Перерыв завершён. Время фокуса.'

}

const saveFocusSession = async () => {
  if (!props.isAuth) {
    localStorage.setItem('completedFocusSessions', completedFocusSessions.value)
    return
  }
  const token = localStorage.getItem('token')

  const response = await fetch(`${import.meta.env.VITE_API_URL}/focus-sessions`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
    body: JSON.stringify({
      mode: 'focus',
      duration_seconds: modes.focus.seconds,
    }),
  })

  if (!response.ok) {
    const data = await response.json()
    statusMessage.value = data.message || 'Не удалось сохранить статистику'
    return
  }
  totalFocusSeconds.value += modes.focus.seconds
  todayFocusSessions.value++
  todayFocusSeconds.value += modes.focus.seconds
  localStorage.setItem('completedFocusSessions', completedFocusSessions.value)
}
const loadFocusSessions = async () => {
  if (!props.isAuth) {
    return
  }

  const token = localStorage.getItem('token')

  const response = await fetch(`${import.meta.env.VITE_API_URL}/focus-sessions`, {
    headers: {
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
    },
  })

  if (!response.ok) {
    return
  }

  const data = await response.json()
  focusSessions.value = data.statistics
  completedFocusSessions.value = data.statistics.length
  totalFocusSeconds.value = data.statistics.reduce((sum, session) => {
    return sum + session.duration_seconds
  }, 0)
  const today = new Date().toISOString().slice(0, 10)

  const todayStatistics = data.statistics.filter((session) => {
    return session.completed_at?.slice(0, 10) === today
  })

  todayFocusSessions.value = todayStatistics.length
  todayFocusSeconds.value = todayStatistics.reduce((sum, session) => {
    return sum + session.duration_seconds
  }, 0)

}
onMounted(() => {
  loadFocusSessions()
})
const previousCalendarMonth = () => {
  calendarDate.value = new Date(
      calendarDate.value.getFullYear(),
      calendarDate.value.getMonth() - 1,
      1
  )
}

const nextCalendarMonth = () => {
  calendarDate.value = new Date(
      calendarDate.value.getFullYear(),
      calendarDate.value.getMonth() + 1,
      1
  )
}

const calendarDays = computed(() => {
  const year = calendarDate.value.getFullYear()
  const month = calendarDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)

  const days = []

  const firstWeekDay = (firstDay.getDay() + 6) % 7

  for (let i = 0; i < firstWeekDay; i++) {
    days.push({
      date: null,
      day: '',
      isCurrentMonth: false,
    })
  }

  for (let day = 1; day <= lastDay.getDate(); day++) {
    const date = new Date(year, month, day)
    const dateKey = date.toISOString().slice(0, 10)
    const focusStats = focusSessionsByDate.value[dateKey]

    days.push({
      date,
      day,
      isCurrentMonth: true,
      sessions: focusStats?.sessions || 0,
      minutes: Math.floor((focusStats?.seconds || 0) / 60),
      hasFocus: Boolean(focusStats),
    })
  }

  return days
})


const resetTimer = () => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false
  endTime.value = null
  totalSeconds.value = modes[currentMode.value].seconds

}
const resetLocalStats = () => {
  completedFocusSessions.value = 0
  localStorage.removeItem('completedFocusSessions')
}

</script>

<template>
  <main
      class="flex min-h-screen w-full flex-col items-center justify-center gap-6 p-6 transition-colors duration-500 lg:flex-row"
      :class="pageBackgroundClass"
  >
    <section class="relative w-full max-w-[520px] rounded-2xl bg-white p-8 text-center shadow-xl">
      <button
          v-if="isAuth"
          type="button"
          class="absolute right-6 top-6 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:border-black hover:text-black"
          @click="emit('logout')"
      >
        Выйти
      </button>

      <h1 class="mb-4 text-3xl font-bold !text-violet-900">
        Pomodoro Timer
      </h1>

      <button
          v-if="!isAuth"
          type="button"
          class="absolute right-6 top-6 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-
  semibold text-slate-700 transition hover:border-black hover:text-black"
          @click="emit('show-login')"
      >
        Войти
      </button>

      <div class="mb-6 flex items-center justify-center gap-3 text-sm font-semibold text-slate-700">
        <template v-if="isAuth">
          <span>
            Сегодня: {{ todayFocusSessions }} сессий / {{ todayFocusMinutes }} мин
          </span>

          <button
              type="button"
              class="rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 transition hover:border-black hover:text-black"
              @click="showProgress = true"
          >
            Прогресс
          </button>
        </template>

        <template v-else>
          <span>Завершено фокус-сессий: {{ completedFocusSessions }}</span>

          <button
              type="button"
              class="rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 transition hover:border-black hover:text-black"
              @click="resetLocalStats"
          >
            Сбросить
          </button>
        </template>
      </div>

      <div class="mb-6 flex flex-wrap justify-center gap-2">
        <button
            v-for="(mode, key) in modes"
            :key="key"
            type="button"
            class="rounded-lg border px-3 py-2 text-sm font-semibold transition"
            :class="currentMode === key
              ? 'border-violet-600 bg-violet-600 text-white'
              : 'border-slate-300 text-slate-700 hover:border-black hover:text-black'"
            @click="selectMode(key)"
        >
          {{ mode.label }}
        </button>
      </div>

      <div class="mb-8 text-7xl font-bold text-slate-950">
        {{ formattedTime }}
      </div>

      <div
          v-if="statusMessage"
          class="mb-4 mt-2 flex justify-center"
      >
        <p class="text-center text-base font-semibold text-purple-700">
          {{ statusMessage }}
        </p>
      </div>


      <div class="flex justify-center gap-3">
        <button
            type="button"
            class="rounded-lg bg-violet-500 px-5 py-3 font-semibold text-white transition hover:bg-violet-600"
            @click="toggleTimer"
        >
          {{ isRunning ? 'Пауза' : 'Старт' }}
        </button>

        <button
            type="button"
            class="rounded-lg border border-slate-300 px-5 py-3 font-semibold text-slate-900 transition hover:border-black"
            @click="resetTimer"
        >
          Сброс
        </button>
      </div>
    </section>

    <aside
        v-if="showProgress"
        class="w-full max-w-[460px] rounded-2xl border border-slate-200 bg-white/95 p-4 text-left shadow-xl"
    >
      <div class="mb-3 flex items-center justify-between gap-3">
        <h2 class="text-lg font-bold !text-violet-900">
          Прогресс
        </h2>

        <div class="flex items-center gap-2">


          <button
              type="button"
              class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-300 text-xs font-bold text-violet-700 transition hover:border-violet-600 hover:bg-violet-50"
              title="Закрыть"
              @click="showProgress = false"
          >×
          </button>
        </div>
      </div>

      <div class="grid gap-2 text-sm text-slate-700">
        <p>Всего сессий: {{ completedFocusSessions }}</p>
        <p>Всего минут: {{ totalFocusMinutes }}</p>
        <p>Сегодня: {{ todayFocusSessions }} сессий / {{ todayFocusMinutes }} мин</p>

        <p v-if="selectedCalendarDay">
          Выбрано: {{ selectedCalendarDay.day }} {{ selectedCalendarMonthLabel }} -
          {{ selectedCalendarDay.sessions }} сессий /
          {{ selectedCalendarDay.minutes }} мин
        </p>

        <div class="mt-5">
          <div class="mb-3 flex items-center justify-between">
            <button
                type="button"
                class="rounded-md px-2 py-1 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                @click="previousCalendarMonth"
            >
              ‹
            </button>

            <h3 class="font-semibold capitalize text-slate-900">
              {{ calendarMonthLabel }}
            </h3>

            <button
                type="button"
                class="rounded-md px-2 py-1 text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                @click="nextCalendarMonth"
            >
              ›
            </button>
          </div>

          <div class="mb-2 grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500">
            <span
                v-for="day in weekDays"
                :key="day"
            >
              {{ day }}
            </span>
          </div>

          <div class="grid grid-cols-7 gap-1 text-center text-sm">
            <div
                v-for="(day, index) in calendarDays"
                :key="index"
                class="flex aspect-square flex-col items-center justify-center rounded-full text-xs transition"
                :class="[
                  !day.isCurrentMonth
                    ? 'text-transparent'
                    : selectedCalendarDay?.date?.toDateString() === day.date?.toDateString()
                      ? 'bg-slate-700 font-bold text-white ring-4 ring-violet-200 cursor-pointer'
                      : day.hasFocus
                        ? 'bg-violet-600 font-bold text-white cursor-pointer'
                        : 'text-slate-600 hover:bg-slate-200'
                ]"
                :title="day.hasFocus ? `${day.sessions} сессий / ${day.minutes} мин` : ''"
                @click="selectCalendarDay(day)"
            >
              <span>{{ day.day }}</span>
            </div>
          </div>
        </div>
      </div>
    </aside>
    <div class="absolute bottom-5 left-5">
      <div class="rounded-full border border-white/80 px-4 py-1 text-xs font-medium text-
    white/90">
        by Кузнецова Анна
      </div>
    </div>
  </main>
</template>
