/**
 * API Service — comunica el frontend Vue con el backend Laravel/MongoDB
 * Base URL: /api/v1 (proxy en vite.config.js hacia http://localhost:8000)
 */

const BASE_URL = '/api/v1'

async function request(method, endpoint, data = null) {
  const config = {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
  }

  if (data) {
    config.body = JSON.stringify(data)
  }

  const response = await fetch(`${BASE_URL}${endpoint}`, config)

  if (!response.ok) {
    const error = await response.json().catch(() => ({ error: response.statusText }))
    throw new Error(error.error || `HTTP ${response.status}`)
  }

  return response.json()
}

export const api = {
  // Dashboard
  getDashboardStats: () => request('GET', '/dashboard/stats'),

  // Points
  earnPoints: (payload) => request('POST', '/points/earn', payload),
  redeemPoints: (payload) => request('POST', '/points/redeem', payload),
  reversePoints: (payload) => request('POST', '/points/reverse', payload),
  getBalance: (studentId) => request('GET', `/points/balance/${studentId}`),
  getHistory: (studentId, page = 1) => request('GET', `/points/history/${studentId}?page=${page}`),
  reconcileAccount: (studentId) => request('POST', `/points/reconcile/${studentId}`),

  // Rewards
  getRewards: (page = 1) => request('GET', `/rewards?page=${page}`),
  getReward: (id) => request('GET', `/rewards/${id}`),
  createReward: (payload) => request('POST', '/rewards', payload),
  updateReward: (id, payload) => request('PUT', `/rewards/${id}`, payload),
  deleteReward: (id) => request('DELETE', `/rewards/${id}`),

  // Redemptions
  getRedemptions: (studentId = null, page = 1) => {
    const params = new URLSearchParams({ page })
    if (studentId) params.set('student_id', studentId)
    return request('GET', `/redemptions?${params}`)
  },
  getRedemption: (id) => request('GET', `/redemptions/${id}`),

  // Audit
  getAuditLogs: (filters = {}, page = 1) => {
    const params = new URLSearchParams({ page, ...filters })
    return request('GET', `/audit/logs?${params}`)
  },
}
