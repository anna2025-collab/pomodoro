<script setup>
import {ref} from 'vue'
import LoginView from './pages/auth/LoginView.vue'
import RegisterView from './pages/auth/RegisterView.vue'
import TimerView from './pages/TimerView.vue'


const currentView = ref('timer')
const isAuth = ref(!!localStorage.getItem('token'))
const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  isAuth.value = false
  authView.value = 'login'
}

</script>
<template>
  <TimerView
      v-if="currentView === 'timer'"
      :is-auth="isAuth"
      @show-login="currentView = 'login'"
      @logout="logout"
  />

  <LoginView
      v-else-if="currentView === 'login'"
      @show-register="currentView = 'register'"
      @login-success="isAuth = true; currentView = 'timer'"
  />

  <RegisterView
      v-else
      @show-login="currentView = 'login'"
  />
</template>

