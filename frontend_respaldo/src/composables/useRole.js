// composable: useRole.js
// Gestiona el rol activo del usuario. Fácilmente sustituible por autenticación real.
import { ref, computed } from 'vue'
import { mockUsers } from '../mocks/rewards.js'

// Rol activo — cambiar aquí para dev: 'student' | 'professor' | 'admin'
const currentRole = ref('student')

export function useRole() {
  const user = computed(() => mockUsers[currentRole.value])

  const isStudent   = computed(() => currentRole.value === 'student')
  const isProfessor = computed(() => currentRole.value === 'professor')
  const isAdmin     = computed(() => currentRole.value === 'admin')

  function setRole(role) {
    if (['student', 'professor', 'admin'].includes(role)) {
      currentRole.value = role
    }
  }

  return {
    currentRole,
    user,
    isStudent,
    isProfessor,
    isAdmin,
    setRole,
  }
}
