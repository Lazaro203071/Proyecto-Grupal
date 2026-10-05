<script setup>
import { ref } from 'vue'
import StatusBadge from '../../components/ui/StatusBadge.vue'
import EmptyState from '../../components/ui/EmptyState.vue'
import { campaignsMock } from '../../mocks/rewards.js'
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Campañas activas</h1>
      <div class="page-subtitle">Panel del alumno</div>
      <p class="page-desc">Participa y gana puntos extra en campañas universitarias.</p>
    </div>

    <div class="campaigns-list">
      <div v-for="c in campaignsMock" :key="c.id" class="campaign-card card">
        <div class="camp-badge">
          <span class="multiplier-chip">{{ c.multiplier }}</span>
        </div>
        <div class="camp-info">
          <div class="camp-header">
            <h3 class="camp-title">{{ c.title }}</h3>
            <StatusBadge :status="c.status" />
          </div>
          <p class="camp-desc">{{ c.description }}</p>
          <div class="camp-meta">
            <span class="meta-item">📅 {{ c.period }}</span>
            <span class="meta-item">🌐 {{ c.scope }}</span>
          </div>
        </div>
        <button class="btn btn-primary btn-sm">Ver detalle</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { display: flex; flex-direction: column; gap: 20px; }
.campaigns-list { display: flex; flex-direction: column; gap: 12px; }

.campaign-card {
  display: flex; align-items: center; gap: 20px; padding: 18px 20px;
}
.multiplier-chip {
  display: inline-flex; align-items: center; justify-content: center;
  width: 56px; height: 56px; border-radius: 50%;
  background: var(--cd-primary-light); color: var(--cd-primary);
  font-size: 16px; font-weight: 700; flex-shrink: 0;
}
.camp-info { flex: 1; }
.camp-header { display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
.camp-title { font-size: 15px; font-weight: 700; color: var(--cd-text-primary); margin: 0; }
.camp-desc { font-size: 13px; color: var(--cd-text-secondary); margin-bottom: 8px; }
.camp-meta { display: flex; gap: 16px; }
.meta-item { font-size: 12px; color: var(--cd-text-muted); }
</style>
