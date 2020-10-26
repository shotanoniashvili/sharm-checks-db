import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  activeChecks: [],
  archiveChecks: [],
  archiveChecksTotal: 0
}

// getters
export const getters = {
  activeChecks: state => state.activeChecks,
  archiveChecks: state => state.archiveChecks,
  archiveChecksTotal: state => state.archiveChecksTotal
}

// mutations
export const mutations = {
  [types.FETCH_ACTIVE_CHECK_SUCCESS] (state, { data }) {
    state.activeChecks = data
  },

  [types.FETCH_ACTIVE_CHECK_FAILURE] (state) {
    state.activeChecks = []
  },

  [types.FETCH_ARCHIVE_CHECK_SUCCESS] (state, { data }) {
    state.archiveChecks = data.data
    state.archiveChecksTotal = data.total
  },

  [types.FETCH_ARCHIVE_CHECK_FAILURE] (state) {
    state.archiveChecks = []
  }
}

// actions
export const actions = {
  async getActiveChecks ({ commit }) {
    try {
      const { data } = await axios.get('/api/active-checks')

      commit(types.FETCH_ACTIVE_CHECK_SUCCESS, { data: data.data })
    } catch (e) {
      commit(types.FETCH_ACTIVE_CHECK_FAILURE)
    }
  },

  async getArchiveChecks ({ commit }, filter) {
    try {
      let operatorsParam = ''
      for (let operator of filter.filter.operators) {
        operatorsParam += '&operators[]=' + operator
      }

      const { data } = await axios.get('/api/archive-checks?page=' + filter.page + '&date-from=' + filter.filter.dateFrom + '&date-to=' + filter.filter.dateTo + operatorsParam)

      commit(types.FETCH_ARCHIVE_CHECK_SUCCESS, { data: data })
    } catch (e) {
      commit(types.FETCH_ARCHIVE_CHECK_FAILURE)
    }
  }
}
