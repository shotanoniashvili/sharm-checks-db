import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  organizations: []
}

// getters
export const getters = {
  organizations: state => state.organizations
}

// mutations
export const mutations = {
  [types.FETCH_ORGANIZATION_SUCCESS] (state, { data }) {
    state.organizations = data
  },

  [types.FETCH_ORGANIZATION_FAILURE] (state) {
    state.organizations = []
  }
}

// actions
export const actions = {
  async getAll ({ commit }) {
    try {
      const { data } = await axios.get('/api/organizations')

      commit(types.FETCH_ORGANIZATION_SUCCESS, { data: data.data })
    } catch (e) {
      commit(types.FETCH_ORGANIZATION_FAILURE)
    }
  }
}
