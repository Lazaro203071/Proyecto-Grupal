<script setup>
import StatusBadge from '../../components/ui/StatusBadge.vue'
import EmptyState from '../../components/ui/EmptyState.vue'
import { redemptionsMock } from '../../mocks/rewards.js'

const myRedemptions = redemptionsMock.filter(r => r.student === 'María López')
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Mis canjes</h1>
      <div class="page-subtitle">Panel del alumno</div>
      <p class="page-desc">Consulta el estado de tus solicitudes de canje.</p>
    </div>

    <div class="card">
      <EmptyState v-if="!myRedemptions.length" title="Sin canjes" message="Aún no has realizado ningún canje. ¡Explora el catálogo!" icon="🎟" />
      <div v-else class="table-wrapper">
        <table class="cd-table">
          <thead>
            <tr>
              <th>Recompensa</th>
              <th>Código</th>
              <th>Puntos</th>
              <th>Fecha</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in myRedemptions" :key="r.id">
              <td style="font-weight:600">{{ r.reward }}</td>
              <td><code style="font-size:12px;background:#f0f3f9;padding:2px 6px;border-radius:4px">{{ r.code }}</code></td>
              <td style="color:var(--cd-primary);font-weight:600">{{ r.points.toLocaleString() }} pts</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{ r.date }}</td>
              <td><StatusBadge :status="r.status" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 20px; }
</style>
