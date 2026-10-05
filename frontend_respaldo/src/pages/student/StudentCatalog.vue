<script setup>
import { ref } from 'vue'
import ConfirmModal from '../../components/ui/ConfirmModal.vue'
import { useToast } from '../../composables/useToast.js'
import { rewardsMock } from '../../mocks/rewards.js'

const { success } = useToast()
const selected = ref(null)
const showModal = ref(false)

function openRedeem(r) {
  selected.value = r
  showModal.value = true
}
function confirmRedeem() {
  showModal.value = false
  success(`¡Canje de "${selected.value.name}" realizado con éxito!`)
  selected.value = null
}

const balance = 3420
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Catálogo de recompensas</h1>
      <div class="page-subtitle">Panel del alumno</div>
      <p class="page-desc">Explora y canjea recompensas disponibles con tus puntos.</p>
    </div>

    <div class="balance-banner card">
      <span class="bb-label">Tu saldo disponible</span>
      <span class="bb-value">⭐ {{ balance.toLocaleString() }} pts</span>
    </div>

    <div class="catalog-grid">
      <div v-for="r in rewardsMock.filter(r => r.status === 'Activo')" :key="r.id" class="reward-card card">
        <div class="rc-image">{{ r.image }}</div>
        <div class="rc-info">
          <div class="rc-name">{{ r.name }}</div>
          <div class="rc-business">{{ r.business }}</div>
          <div class="rc-points">{{ r.points.toLocaleString() }} puntos</div>
          <div class="rc-meta">
            <span>📦 Stock: {{ r.stock }}</span>
            <span>📅 {{ r.validUntil }}</span>
          </div>
        </div>
        <button
          class="btn btn-primary btn-sm"
          :disabled="r.points > balance"
          @click="openRedeem(r)"
        >
          {{ r.points > balance ? 'Puntos insuficientes' : 'Canjear' }}
        </button>
      </div>
    </div>

    <ConfirmModal
      :show="showModal"
      title="Confirmar canje"
      :message="`¿Deseas canjear '${selected?.name}' por ${selected?.points?.toLocaleString()} puntos?`"
      confirm-label="Sí, canjear"
      @confirm="confirmRedeem"
      @cancel="showModal = false"
    />
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 20px; }
.balance-banner {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px; background: var(--cd-primary-light);
  border-color: #c3ddf9;
}
.bb-label { font-size: 13px; color: var(--cd-text-secondary); font-weight: 500; }
.bb-value { font-size: 18px; font-weight: 700; color: var(--cd-primary); }

.catalog-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
}
.reward-card {
  display: flex; flex-direction: column; align-items: center;
  padding: 20px 16px; text-align: center; gap: 8px;
}
.rc-image { font-size: 42px; }
.rc-name { font-size: 14px; font-weight: 700; color: var(--cd-text-primary); }
.rc-business { font-size: 12px; color: var(--cd-text-muted); }
.rc-points { font-size: 15px; font-weight: 700; color: var(--cd-primary); }
.rc-meta { display: flex; flex-direction: column; gap: 2px; }
.rc-meta span { font-size: 11px; color: var(--cd-text-muted); }
.rc-info { display: flex; flex-direction: column; gap: 4px; align-items: center; width: 100%; }
</style>
