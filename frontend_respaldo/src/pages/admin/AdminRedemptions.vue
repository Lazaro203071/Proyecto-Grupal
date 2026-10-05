<script setup>
import StatusBadge from "../../components/ui/StatusBadge.vue"
import { redemptionsMock } from "../../mocks/rewards.js"
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">Canje y entrega</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Monitorea y gestiona todos los canjes de la plataforma.</p></div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Usuario</th><th>Recompensa</th><th>Código</th><th>Puntos</th><th>Fecha</th><th>Estado</th></tr></thead>
          <tbody>
            <tr v-for="r in redemptionsMock" :key="r.id">
              <td><div style="display:flex;align-items:center;gap:8px"><div class="av" style="background:#2878ED">{{r.initials}}</div>{{r.student}}</div></td>
              <td>{{r.reward}}</td>
              <td><code style="font-size:11px;background:#f0f3f9;padding:2px 6px;border-radius:4px">{{r.code}}</code></td>
              <td style="color:var(--cd-primary);font-weight:600">{{r.points.toLocaleString()}} pts</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{r.date}}</td>
              <td><StatusBadge :status="r.status" /></td>
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
.av{width:28px;height:28px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;}
</style>
