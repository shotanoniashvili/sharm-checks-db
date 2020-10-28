<template>
  <div>
    <b-modal
      id="user-modal"
      ref="modal"
      :title="modalTitle"
      :ok-title="modalTitle"
      cancel-title="დახურვა"
      @shown="showModal"
      @hidden="hideModal"
      @ok="handleOk"
    >
      <form ref="form" @submit.stop.prevent="handleSubmit">
        <b-form-group
          label="სახელი"
          label-for="name-input"
        >
          <b-form-input
            id="name-input"
            v-model="tmpItem.name"
            required
          />
        </b-form-group>
        <b-form-group
          label="ელ-ფოსტა"
          label-for="email-input"
        >
          <b-form-input
            id="email-input"
            v-model="tmpItem.email"
            required
            type="email"
          />
        </b-form-group>
        <b-form-group
          label="ორგანიზაცია"
          label-for="organization-input"
        >
          <v-select
            v-model="tmpItem.organization"
            :reduce="organization => organization.id"
            :options="organizations"
            label="name"
          />
        </b-form-group>
        <b-form-group
          label="მომხმარებლის როლი"
          label-for="roles-input"
        >
          <v-select
            v-model="tmpItem.roles"
            :reduce="role => role.id"
            label="name"
            multiple
            :options="roles"
          />
        </b-form-group>
        <b-form-group v-if="tmpItem && tmpItem.roles && (tmpItem.roles.indexOf(3) !== -1 || tmpItem.roles.map(o => o.id).indexOf(3) !== -1)"
                      label="მენეჯერები"
                      label-for="managers-input"
        >
          <v-select
            v-model="tmpItem.managers"
            :reduce="manager => manager.id"
            label="name"
            multiple
            :options="managers"
          />
        </b-form-group>
        <hr>
        <span v-if="tmpItem.id" class="text-muted">* დატოვეთ ცარიელი თუ არ გსურთ შეცვლა</span>
        <b-form-group
          label="პაროლი"
          label-for="password-input"
        >
          <b-form-input
            id="password-input"
            v-model="tmpItem.password"
            type="password"
          />
        </b-form-group>
      </form>
    </b-modal>
  </div>
</template>

<script>
import axios from 'axios'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import { mapGetters } from 'vuex'
import store from '../store'

export default {
  name: 'UserModal',

  components: {
    vSelect
  },

  props: ['item', 'title'],

  data () {
    return {
      tmpItem: {}
    }
  },

  computed: {
    ...mapGetters({
      organizations: 'organization/organizations',
      roles: 'role/roles',
      managers: 'user/users'
    }),

    modalTitle: function () {
      return this.tmpItem.id ? 'რედაქტირება' : 'დამატება'
    }
  },

  methods: {
    getEmptyItem () {
      return {
        id: null,
        name: '',
        email: '',
        organization_id: '',
        password: ''
      }
    },

    showModal () {
      store.dispatch('organization/getAll')
      store.dispatch('role/getAll')
      store.dispatch('user/getAll')

      if (this.item) { this.tmpItem = Object.assign({}, this.item) }

      this.$emit('show-modal')
    },

    hideModal () {
      this.$emit('hide-modal')
    },

    handleOk (event) {
      event.preventDefault()

      this.handleSubmit()
    },

    handleSubmit () {
      if (this.tmpItem.id) {
        this.update()
      } else {
        this.create()
      }
    },

    getParams () {
      let data = Object.assign({}, this.tmpItem)

      data.roles = !Number.isInteger(data.roles[0]) ? data.roles.map(o => o.id) : data.roles

      if (data.organization) data.organization_id = data.organization.id
      if (data.managers) { data.managers = !Number.isInteger(data.managers[0]) ? data.managers.map(o => o.id) : data.managers }

      return data
    },

    update () {
      axios.patch('/api/users/' + this.tmpItem.id, this.getParams())
        .then(response => {
          this.$emit('user-updated', response.data.data)

          this.$bvModal.hide('user-modal')
        })
    },

    create () {
      axios.post('/api/users', this.getParams())
        .then(response => {
          this.$emit('user-created', response.data.data)

          this.$bvModal.hide('user-modal')
        })
    }
  }
}
</script>
