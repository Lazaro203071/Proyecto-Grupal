<script setup>
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { auditMock } from "../../mocks/rewards.js"
</script>
<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Historial y reportes</h1>
      <div class="page-subtitle">Panel del profesor</div>
      <p class="page-desc">Consulta el historial de tus acciones y reportes docentes.</p>
    </div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Usuario</th><th>Acción</th><th>Entidad</th><th>Fecha</th></tr></thead>
          <tbody>
            <tr v-for="a in auditMock" :key="a.id">
              <td><div style="display:flex;align-items:center;gap:8px"><div class="av" :style="{background:`#2878ED`}">{{a.initials}}</div>{{a.user}}</div></td>
              <td>{{a.action}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{a.entity}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px;white-space:nowrap">{{a.date}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.av{width:28px;height:28px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;}
</style>
