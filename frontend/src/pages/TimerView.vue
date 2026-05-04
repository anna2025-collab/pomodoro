<script setup>
import {computed, ref} from 'vue'

const emit = defineEmits(['logout','show-login'])
const totalSeconds = ref(25 * 60)
const isRunning = ref(false)
const intervalId = ref(null)
const currentMode = ref('focus')
const completedFocusSessions = ref(Number(localStorage.getItem('completedFocusSessions') ||
    0))
const statusMessage = ref('')
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
const startTimer = () => {
  if (isRunning.value) {
    return
  }

  isRunning.value = true

  intervalId.value = setInterval(() => {
    if (totalSeconds.value <= 1) {
      totalSeconds.value = 0
      completeTimer()
      return
    }

    totalSeconds.value--
  }, 1000)
}

const pauseTimer = () => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false
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
}
const completeTimer = async () => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false

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

  // Позже здесь будет запрос в Laravel для сохранения статистики.
  localStorage.setItem('completedFocusSessions', completedFocusSessions.value)
}

const resetTimer = () => {
  clearInterval(intervalId.value)
  intervalId.value = null
  isRunning.value = false
  totalSeconds.value = modes[currentMode.value].seconds

}
const resetLocalStats = () => {
  completedFocusSessions.value = 0
  localStorage.removeItem('completedFocusSessions')
}

</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-gradient-to-br from-violet-
  900 via-purple-950 to-slate-950">
    <section class="w-full max-w-[520px] rounded-2xl bg-white p-8 text-center shadow-xl">
      <h1 class="mb-4 text-3xl font-bold !text-violet-900">
        Pomodoro Timer
      </h1>

      <button
          v-if="isAuth"
          type="button"
          class="mb-6 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-semibold text-
  slate-700 transition hover:border-black hover:text-black"
          @click="emit('logout')"
      >
        Выйти
      </button>

      <button
          v-else
          type="button"
          class="mb-6 rounded-md border border-slate-300 px-3 py-1.5 text-sm font-semibold text-
  slate-700 transition hover:border-black hover:text-black"
          @click="emit('show-login')"
      >
        Войти
      </button>

      <div class="mb-6 flex items-center justify-center gap-3 text-sm font-semibold text-slate-
  700">
        <span>Завершено фокус-сессий: {{ completedFocusSessions }}</span>

        <button
            type="button"
            class="rounded-md border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700
  transition hover:border-black hover:text-black"
            @click="resetLocalStats"
        >
          Сбросить
        </button>
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
      <p v-if="statusMessage" class="mb-6 text-sm font-semibold text-violet-700">
        {{ statusMessage }}
      </p>

      <div class="flex justify-center gap-3">
        <button
            type="button"
            class="rounded-lg bg-violet-500 px-5 py-3 font-semibold text-white transition
  hover:bg-violet-600"
            @click="toggleTimer"
        >
          {{ isRunning ? 'Пауза' : 'Старт' }}
        </button>

        <button
            type="button"
            class="rounded-lg border border-slate-300 px-5 py-3 font-semibold text-slate-900
  transition hover:border-black"
            @click="resetTimer"

        >
          Сброс
        </button>
      </div>
    </section>
  </main>
</template>
