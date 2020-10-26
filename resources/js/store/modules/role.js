import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  roles: []
}

// getters
export const getters = {
  roles: state => state.roles
}

// mutations
export const mutations = {
  [types.FETCH_ROLE_SUCCESS] (state, { data }) {
    state.roles = data
  },

  [types.FETCH_ROLE_FAILURE] (state) {
    state.roles = []
  }
}

// actions
export const actions = {
  async getAll ({ commit }) {
    try {
      const { data } = await axios.get('/api/roles')

      commit(types.FETCH_ROLE_SUCCESS, { data: data.data })
    } catch (e) {
      commit(types.FETCH_ROLE_FAILURE)
    }
  }
}
