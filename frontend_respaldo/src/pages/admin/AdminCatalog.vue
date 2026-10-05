<script setup>
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { rewardsMock } from "../../mocks/rewards.js"
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">Catálogo de recompensas</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Administra las recompensas disponibles en la plataforma.</p></div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Recompensa</th><th>Negocio</th><th>Puntos</th><th>Stock</th><th>Vigencia</th><th>Estado</th><th></th></tr></thead>
          <tbody>
            <tr v-for="r in rewardsMock" :key="r.id">
              <td><div style="display:flex;align-items:center;gap:10px"><span style="font-size:22px">{{r.image}}</span><span style="font-weight:600">{{r.name}}</span></div></td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{r.business}}</td>
              <td style="color:var(--cd-primary);font-weight:600">{{r.points.toLocaleString()}} pts</td>
              <td>{{r.stock}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{r.validUntil}}</td>
              <td><StatusBadge :status="r.status" /></td>
              <td><button class="btn btn-ghost btn-sm">Editar</button></td>
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
</style>
