<script setup>
import { ref } from "vue"
import StatusBadge from "../../components/ui/StatusBadge.vue"
import ConfirmModal from "../../components/ui/ConfirmModal.vue"
import { useToast } from "../../composables/useToast.js"
import { redemptionsMock } from "../../mocks/rewards.js"
const { success } = useToast()
const redemptions = ref([...redemptionsMock])
const selected = ref(null)
const showModal = ref(false)
function openValidate(r) { selected.value = r; showModal.value = true }
function confirmValidate() {
  redemptions.value = redemptions.value.map(r => r.id === selected.value.id ? {...r, status:"Validado"} : r)
  showModal.value = false
  success(`Canje de ${selected.value.student} validado correctamente.`)
}
const colors = ["#2878ED","#16a34a","#d97706","#7c3aed"]
function ac(idx) { return colors[idx % colors.length] }
</script>
<template>
  <div class="page">
    <div class="page-header">
      <h1 class="page-title">Validar canjes</h1>
      <div class="page-subtitle">Panel del profesor</div>
      <p class="page-desc">Revisa y confirma los canjes pendientes de tus estudiantes.</p>
    </div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Alumno</th><th>Recompensa</th><th>Código</th><th>Puntos</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr></thead>
          <tbody>
            <tr v-for="(r,i) in redemptions" :key="r.id">
              <td><div class="sc"><div class="av" :style="{background:ac(i)}">{{r.initials}}</div>{{r.student}}</div></td>
              <td>{{r.reward}}</td>
              <td><code class="ct">{{r.code}}</code></td>
              <td style="color:var(--cd-primary);font-weight:600">{{r.points.toLocaleString()}} pts</td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{r.date}}</td>
              <td><StatusBadge :status="r.status" /></td>
              <td><button class="btn btn-primary btn-sm" :disabled="r.status==='Validado'||r.status==='Entregado'||r.status==='Cancelado'" @click="openValidate(r)">Validar</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <ConfirmModal :show="showModal" title="Validar canje" :message="`¿Confirmas la validación del canje de ${selected?.student}?`" confirm-label="Validar" @confirm="confirmValidate" @cancel="showModal=false" />
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.sc{display:flex;align-items:center;gap:8px;}
.av{width:28px;height:28px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;}
.ct{font-size:11px;background:#f0f3f9;padding:2px 6px;border-radius:4px;}
</style>
