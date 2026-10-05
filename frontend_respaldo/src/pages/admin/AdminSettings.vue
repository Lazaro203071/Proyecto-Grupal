<script setup>
import { ref } from "vue"
import StatusBadge from "../../components/ui/StatusBadge.vue"
import ConfirmModal from "../../components/ui/ConfirmModal.vue"
import { useToast } from "../../composables/useToast.js"
import { settingsMock } from "../../mocks/rewards.js"
const { success } = useToast()
const showModal = ref(false)
const selected = ref(null)
function openEdit(s){ selected.value = s; showModal.value = true }
function confirmEdit(){ showModal.value = false; success(`Configuración "${selected.value.config}" actualizada correctamente.`) }
</script>
<template>
  <div class="page">
    <div class="ph"><h1 class="page-title">7.11 Configuraciones sensibles</h1><div class="page-subtitle">Panel de administración</div><p class="page-desc">Parámetros críticos del sistema de recompensas.</p></div>
    <div class="warn-banner">⚠ Los cambios en esta sección afectan el comportamiento global de la plataforma. Procede con precaución.</div>
    <div class="card">
      <div class="table-wrapper">
        <table class="cd-table">
          <thead><tr><th>Configuración</th><th>Valor actual</th><th>Versión</th><th>Última modificación</th><th>Estado</th><th>Acción</th></tr></thead>
          <tbody>
            <tr v-for="s in settingsMock" :key="s.id">
              <td style="font-weight:600">{{s.config}}</td>
              <td><code style="font-size:12px;background:#f0f3f9;padding:3px 8px;border-radius:4px">{{s.value}}</code></td>
              <td style="color:var(--cd-text-muted);font-size:12px">{{s.version}}</td>
              <td style="color:var(--cd-text-muted);font-size:12px;white-space:nowrap">{{s.lastModified}}</td>
              <td><StatusBadge :status="s.status" /></td>
              <td><button class="btn btn-ghost btn-sm" @click="openEdit(s)">Editar</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <ConfirmModal :show="showModal" :danger="true" title="Editar configuración sensible" :message="`¿Estás seguro de que deseas modificar '${selected?.config}'? Esta acción puede afectar el programa completo.`" confirm-label="Confirmar edición" @confirm="confirmEdit" @cancel="showModal=false" />
  </div>
</template>
<style scoped>
.page{display:flex;flex-direction:column;gap:20px;}
.ph{display:flex;flex-direction:column;gap:2px;}
.warn-banner{padding:12px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:13px;color:#92400e;font-weight:500;}
</style>
