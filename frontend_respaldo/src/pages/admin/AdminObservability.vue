<script setup>
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { integrationMock } from "../../mocks/rewards.js"
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">7.10 Integración y observabilidad</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Estado de APIs, webhooks, jobs y colas del sistema de recompensas.</p></div>
    <div class="summary-row">
      <div class="card sm-card ok"><div class="sml">OK</div><div class="smv">{{ integrationMock.filter(i=>i.status==="OK").length }}</div></div>
      <div class="card sm-card warn"><div class="sml">Advertencias</div><div class="smv">{{ integrationMock.filter(i=>i.status==="Advertencia").length }}</div></div>
      <div class="card sm-card err"><div class="sml">Errores</div><div class="smv">{{ integrationMock.filter(i=>i.status==="Error").length }}</div></div>
    </div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Servicio</th><th>Tipo</th><th>Endpoint</th><th>Estado</th><th>Última revisión</th><th>Uptime</th></tr></thead>
          <tbody>
            <tr v-for="i in integrationMock" :key="i.id">
              <td style="font-weight:600">{{i.name}}</td>
              <td><span class="type-badge">{{i.type}}</span></td>
              <td><code style="font-size:11px;background:#f0f3f9;padding:2px 6px;border-radius:4px">{{i.endpoint}}</code></td>
              <td><StatusBadge :status="i.status" /></td>
              <td style="color:var(--cd-text-muted);font-size:12px;white-space:nowrap">{{i.lastCheck}}</td>
              <td><span :style="{color: parseFloat(i.uptime)>95 ? '#16a34a' : parseFloat(i.uptime)>90 ? '#d97706' : '#dc2626', fontWeight:600}">{{i.uptime}}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.ph{display:flex;flex-direction:column;gap:2px;}
.summary-row{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.sm-card{padding:16px 20px;text-align:center;border-top:3px solid;}
.ok{border-color:#16a34a;} .warn{border-color:#d97706;} .err{border-color:#dc2626;}
.sml{font-size:12px;color:var(--cd-text-secondary);margin-bottom:4px;}
.smv{font-size:24px;font-weight:700;color:var(--cd-text-primary);}
.type-badge{padding:2px 8px;background:#f0f3f9;border-radius:12px;font-size:11px;color:var(--cd-text-secondary);}
</style>
