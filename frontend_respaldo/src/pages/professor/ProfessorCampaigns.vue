<script setup>
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { campaignsMock } from "../../mocks/rewards.js"
</script>
<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Campañas académicas</h1>
      <div class="page-subtitle">Panel del profesor</div>
      <p class="page-desc">Campañas activas para reconocer el esfuerzo de tus estudiantes.</p>
    </div>
    <div class="campaigns-list">
      <div v-for="c in campaignsMock" :key="c.id" class="campaign-card card">
        <div class="mult-chip">{{ c.multiplier }}</div>
        <div class="camp-info">
          <div class="camp-header">
            <h3 class="camp-title">{{ c.title }}</h3>
            <StatusBadge :status="c.status" />
          </div>
          <p class="camp-desc">{{ c.description }}</p>
          <div class="camp-meta">
            <span>📅 {{ c.period }}</span>
            <span>🌐 {{ c.scope }}</span>
          </div>
        </div>
        <button class="btn btn-secondary btn-sm">Ver detalle</button>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page { display:flex; flex-direction:column; gap:20px; }
.campaigns-list { display:flex; flex-direction:column; gap:12px; }
.campaign-card { display:flex; align-items:center; gap:20px; padding:18px 20px; }
.mult-chip { min-width:56px; height:56px; border-radius:50%; background:var(--cd-primary-light); color:var(--cd-primary); display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; }
.camp-info { flex:1; }
.camp-header { display:flex; align-items:center; gap:10px; margin-bottom:4px; }
.camp-title { font-size:15px; font-weight:700; color:var(--cd-text-primary); margin:0; }
.camp-desc { font-size:13px; color:var(--cd-text-secondary); margin-bottom:8px; }
.camp-meta { display:flex; gap:16px; font-size:12px; color:var(--cd-text-muted); }
</style>
