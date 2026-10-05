<script setup>
import { ref } from 'vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import EmptyState from '../../components/ui/EmptyState.vue'
import { activityMock } from '../../mocks/rewards.js'

const filter = ref('all')
const filtered = ref(activityMock)

function applyFilter(type) {
  filter.value = type
  if (type === 'all') filtered.value = activityMock
  else filtered.value = activityMock.filter(a => a.type === type)
}

function formatPoints(pts) {
  return (pts > 0 ? '+' : '') + pts.toLocaleString()
}
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mi actividad</h1>
        <div class="page-subtitle">Panel del alumno</div>
        <p class="page-desc">Historial completo de tus movimientos de puntos.</p>
      </div>
    </div>

    <!-- Balance -->
    <div class="balance-strip card">
      <div class="bs-item">
        <div class="bs-label">Saldo actual</div>
        <div class="bs-value blue">3,420 pts</div>
      </div>
      <div class="bs-divider"></div>
      <div class="bs-item">
        <div class="bs-label">Total ganado</div>
        <div class="bs-value green">+4,020 pts</div>
      </div>
      <div class="bs-divider"></div>
      <div class="bs-item">
        <div class="bs-label">Total canjeado</div>
        <div class="bs-value red">-600 pts</div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filter-row">
      <button v-for="f in [{k:'all',l:'Todos'},{k:'earn',l:'Ganancias'},{k:'redeem',l:'Canjes'}]"
        :key="f.k" class="filter-btn" :class="{active: filter===f.k}" @click="applyFilter(f.k)">
        {{ f.l }}
      </button>
    </div>

    <!-- Tabla -->
    <div class="card">
      <EmptyState v-if="!filtered.length" title="Sin movimientos" message="No hay movimientos para el filtro seleccionado." icon="🔍" />
      <div v-else class="table-wrapper">
        <table class="cd-table">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Concepto</th>
              <th>Fuente</th>
              <th>Puntos</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filtered" :key="item.date + item.concept">
              <td style="color:var(--cd-text-muted);font-size:12px;white-space:nowrap">{{ item.date }}</td>
              <td>{{ item.concept }}</td>
              <td><span class="source-tag">{{ item.source }}</span></td>
              <td>
                <span :class="item.points > 0 ? 'pts-pos' : 'pts-neg'">{{ formatPoints(item.points) }}</span>
              </td>
              <td><StatusBadge :status="item.status" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 20px; }
.page-header { margin-bottom: 4px; }

.balance-strip { display: flex; padding: 20px 24px; gap: 0; }
.bs-item { flex: 1; text-align: center; }
.bs-divider { width: 1px; background: var(--cd-border); }
.bs-label { font-size: 12px; color: var(--cd-text-secondary); margin-bottom: 6px; font-weight: 500; }
.bs-value { font-size: 20px; font-weight: 700; }
.blue { color: var(--cd-primary); }
.green { color: #16a34a; }
.red { color: #dc2626; }

.filter-row { display: flex; gap: 8px; }
.filter-btn {
  padding: 6px 16px; border-radius: 20px; border: 1px solid var(--cd-border);
  background: #fff; color: var(--cd-text-secondary); font-size: 13px;
  font-family: var(--cd-font); cursor: pointer; transition: all 0.15s;
}
.filter-btn.active { background: var(--cd-primary); color: #fff; border-color: var(--cd-primary); }
.filter-btn:hover:not(.active) { background: #f0f3f9; }

.source-tag {
  padding: 2px 8px; background: #f0f3f9; border-radius: 12px;
  font-size: 11px; color: var(--cd-text-secondary);
}
.pts-pos { color: #16a34a; font-weight: 600; }
.pts-neg { color: #dc2626; font-weight: 600; }
</style>
