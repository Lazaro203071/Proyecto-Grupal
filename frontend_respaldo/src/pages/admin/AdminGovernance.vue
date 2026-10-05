<script setup>
import { adminDashboardMock } from "../../mocks/rewards.js"
const data = adminDashboardMock
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">Gobierno de puntos</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Gestiona límites, expiraciones, antifraude y conciliaciones.</p></div>
    <div class="grid-2">
      <div class="card gov-section"><div class="gs-title">📋 Límites de emisión</div><div class="gs-list"><div class="gs-item"><span>Tope diario por estudiante</span><span class="gsv">500 pts</span></div><div class="gs-item"><span>Tope mensual</span><span class="gsv">5,000 pts</span></div><div class="gs-item"><span>Reglas con límite activo</span><span class="gsv" style="color:var(--cd-primary)">{{ data.governance.emissionLimits }}</span></div></div></div>
      <div class="card gov-section"><div class="gs-title">⏰ Puntos por expirar</div><div class="gs-list"><div class="gs-item"><span>Expiración en 30 días</span><span class="gsv" style="color:#d97706">{{ data.governance.expiringPoints }}</span></div><div class="gs-item"><span>Política: sin actividad 12 meses</span><span class="gsv">Activa</span></div></div></div>
      <div class="card gov-section"><div class="gs-title">⚠ Antifraude</div><div class="gs-list"><div class="gs-item"><span>Fraudes detectados</span><span class="gsv" style="color:#dc2626">{{ data.governance.detectedFrauds }}</span></div><div class="gs-item"><span>Umbral: 3 tx/hora</span><span class="gsv">Activo</span></div><div class="gs-item"><span>Flags abiertos</span><span class="gsv">12</span></div></div></div>
      <div class="card gov-section"><div class="gs-title">🔄 Reversos y conciliación</div><div class="gs-list"><div class="gs-item"><span>Reversos este mes</span><span class="gsv">8</span></div><div class="gs-item"><span>Última conciliación</span><span class="gsv">02/10/2026</span></div><div class="gs-item"><span>Estado</span><span class="gsv" style="color:#16a34a">OK</span></div></div></div>
    </div>
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.ph{display:flex;flex-direction:column;gap:2px;}
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
.gov-section{padding:18px 20px;}
.gs-title{font-size:14px;font-weight:700;color:var(--cd-text-primary);margin-bottom:14px;}
.gs-list{display:flex;flex-direction:column;gap:10px;}
.gs-item{display:flex;justify-content:space-between;align-items:center;font-size:13px;color:var(--cd-text-secondary);border-bottom:1px solid var(--cd-border-light);padding-bottom:8px;}
.gs-item:last-child{border-bottom:none;}
.gsv{font-weight:700;color:var(--cd-text-primary);}
@media(max-width:900px){.grid-2{grid-template-columns:1fr;}}
</style>
