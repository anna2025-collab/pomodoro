<script setup>
import { reactive, ref } from 'vue'
const emit = defineEmits(['show-login'])

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const message = ref('')
const loading = ref(false)

const submit = async () => {
  loading.value = true
  errors.value = {}
  message.value = ''

  try {
    const response = await fetch('http://localhost:8080/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(form),
    })

    const data = await response.json()

    if (!response.ok) {
      if (response.status === 422) {
        errors.value = data.errors || {}
      } else {
        message.value = data.message || 'Ошибка регистрации'
      }

      return
    }

    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    message.value = 'Регистрация прошла успешно'

    console.log('Ответ от Laravel:', data)
  } catch (error) {
    message.value = 'Не удалось подключиться к серверу'
    console.error(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-gradient-to-br from-violet-
  50 via-purple-100 to-fuchsia-50">
    <section class="w-full max-w-[420px] rounded-2xl bg-white p-8 shadow-xl">
      <h1 class="mb-2 text-center text-3xl font-bold !text-violet-900">
        Регистрация
      </h1>

      <p class="mb-6 text-center leading-relaxed text-violet-600
">
        Создай аккаунт, чтобы сохранять статистику помидорок.
      </p>

      <form @submit.prevent="submit">
        <div class="mb-4">
          <label for="name" class="mb-1.5 block font-semibold text-slate-900">
            Имя
          </label>

          <input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="Введите имя"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-black outline-none
  transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
          >

          <p v-if="errors.name" class="mt-1.5 text-sm text-red-600">
            {{ errors.name[0] }}
          </p>
        </div>

        <div class="mb-4">
          <label for="email" class="mb-1.5 block font-semibold text-slate-900">
            Email
          </label>

          <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="Введите email"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-black
  transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
          >

          <p v-if="errors.email" class="mt-1.5 text-sm text-red-600">
            {{ errors.email[0] }}
          </p>
        </div>

        <div class="mb-4">
          <label for="password" class="mb-1.5 block font-semibold text-slate-900">
            Пароль
          </label>

          <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="Минимум 8 символов"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5  text-black
  transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
          >

          <p v-if="errors.password" class="mt-1.5 text-sm text-red-600">
            {{ errors.password[0] }}
          </p>
        </div>

        <div class="mb-4">
          <label for="password_confirmation" class="mb-1.5 block font-semibold text-slate-
  900">
            Повторите пароль
          </label>

          <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              placeholder="Повторите пароль"
              class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-black outline-none
  transition focus:border-violet-500 focus:ring-2 focus:ring-violet-200"
          >
        </div>

        <button
            type="submit"
            :disabled="loading"
            class="w-full rounded-lg bg-violet-500 px-3 py-3 font-semibold text-white
  transition hover:bg-violet-600 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
        </button>

        <p v-if="message" class="mt-3 text-sm text-slate-900">
          {{ message }}
        </p>
        <div class="mt-7 flex items-center justify-center gap-4 text-sm text-slate-600">
          <span>Уже есть аккаунт?</span>

          <button
              type="button"
              class="rounded-md border border-transparent px-2 py-1 font-semibold text-slate-900
  transition hover:border-black hover:text-black hover:shadow-[0_0_12px_rgba(0,0,0,0.45)]
  focus:outline-none focus:ring-2 focus:ring-black/40"
              @click="emit('show-login')"
          >
            Войти
          </button>
        </div>

      </form>
    </section>
  </main>
</template>


