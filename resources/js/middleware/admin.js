import store from '~/store'

export default (to, from, next) => {
  if (store.getters['auth/isAdmin'] === true) {
    next({ name: 'home' })
  } else {
    next()
  }
}
