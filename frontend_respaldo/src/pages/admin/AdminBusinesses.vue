<script setup>
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { businessesMock } from "../../mocks/rewards.js"
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">Adhesión de negocios</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Gestiona los negocios adheridos al programa de recompensas.</p></div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Negocio</th><th>Responsable</th><th>Fecha adhesión</th><th>Participación</th><th>Estado</th></tr></thead>
          <tbody>
            <tr v-for="b in businessesMock" :key="b.id">
              <td style="font-weight:600">{{b.name}}</td>
              <td>{{b.responsible}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{b.joinDate}}</td>
              <td><span class="part-badge" :class="b.participation.toLowerCase()">{{b.participation}}</span></td>
              <td><StatusBadge :status="b.status" /></td>
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
.part-badge{padding:2px 10px;border-radius:12px;font-size:12px;font-weight:500;}
.alta{background:#f0fdf4;color:#16a34a;}
.media{background:#fffbeb;color:#d97706;}
.baja{background:#fef2f2;color:#dc2626;}
.—{background:#f0f3f9;color:#6b7a99;}
</style>
