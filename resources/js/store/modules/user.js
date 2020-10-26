import axios from 'axios'
import * as types from '../mutation-types'

// state
export const state = {
  users: []
}

// getters
export const getters = {
  users: state => state.users
}

// mutations
export const mutations = {
  [types.FETCH_USERS_SUCCESS] (state, { data }) {
    state.users = data
  },

  [types.FETCH_USERS_FAILURE] (state) {
    state.users = []
  }
}

// actions
export const actions = {
  async getAll ({ commit }) {
    try {
      const { data } = await axios.get('/api/users')

      commit(types.FETCH_USERS_SUCCESS, { data: data.data })
    } catch (e) {
      commit(types.FETCH_USERS_FAILURE)
    }
  }
}
