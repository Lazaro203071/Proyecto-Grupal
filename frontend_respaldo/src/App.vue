<script setup>
import { useRoute } from 'vue-router'
import { useRole } from './composables/useRole.js'
import AppLayout from './components/layout/AppLayout.vue'

const { currentRole } = useRole()
</script>

<template>
  <AppLayout>
    <RouterView />
  </AppLayout>
</template>
