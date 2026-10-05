<script setup>
import { ref } from "vue"
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { ledgerMock } from "../../mocks/rewards.js"
const search = ref("")
const filtered = ref(ledgerMock)
function applySearch() {
  const q = search.value.toLowerCase()
  filtered.value = q ? ledgerMock.filter(l => l.student.toLowerCase().includes(q) || l.reference.toLowerCase().includes(q)) : ledgerMock
}
function mvtLabel(t){ return {earn:"Ganancia",redeem:"Canje",reverse:"Reverso"}[t] || t }
function ptsColor(m){ return {earn:"#16a34a",redeem:"#dc2626",reverse:"#d97706"}[m] || "#2878ED" }
function ptsSign(l){ return {earn:"+",redeem:"-",reverse:"~"}[l.movement] || "" }
</script>
<template>
  <div class="page">
    <div class="phrow">
      <div class="ph"><h1 class="page-title">7.1 Ledger global de puntos</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Registro completo de todos los movimientos de puntos de la plataforma.</p></div>
    </div>
    <div class="filters-bar">
      <input class="cd-input" v-model="search" @input="applySearch" placeholder="Buscar por estudiante o referencia…" style="max-width:360px"/>
      <select class="cd-input" style="max-width:160px"><option>Todos los tipos</option><option>Ganancia</option><option>Canje</option><option>Reverso</option></select>
      <select class="cd-input" style="max-width:160px"><option>Todos los estados</option><option>Completado</option><option>Entregado</option><option>Revertido</option></select>
    </div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Estudiante</th><th>Matrícula</th><th>Movimiento</th><th>Puntos</th><th>Referencia</th><th>Fecha</th><th>Estado</th></tr></thead>
          <tbody>
            <tr v-for="l in filtered" :key="l.id">
              <td style="font-weight:600">{{l.student}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{l.sid}}</td>
              <td><span class="mvt-badge" :class="l.movement">{{ mvtLabel(l.movement) }}</span></td>
              <td><span :style="{color:ptsColor(l.movement),fontWeight:600}">{{ ptsSign(l) }}{{ l.points }} pts</span></td>
              <td><code style="font-size:11px;background:#f0f3f9;padding:2px 6px;border-radius:4px">{{l.reference}}</code></td>
              <td style="color:var(--cd-text-muted);font-size:12px;white-space:nowrap">{{l.date}}</td>
              <td><StatusBadge :status="l.status" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.phrow{display:flex;align-items:flex-end;justify-content:space-between;}
.ph{display:flex;flex-direction:column;gap:2px;}
.filters-bar{display:flex;gap:10px;flex-wrap:wrap;}
.mvt-badge{padding:2px 10px;border-radius:12px;font-size:12px;font-weight:500;}
.earn{background:#f0fdf4;color:#16a34a;}
.redeem{background:#fef2f2;color:#dc2626;}
.reverse{background:#fffbeb;color:#d97706;}
</style>
